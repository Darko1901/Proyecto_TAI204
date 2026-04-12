from fastapi.security import OAuth2PasswordBearer
from fastapi import HTTPException, status, Depends
from jose import JWTError, jwt
import hashlib
import os
import binascii
from datetime import datetime, timedelta
from sqlalchemy.orm import Session
from app.data.database import get_db
from app.models.usuario import Usuario

# Configuración JWT
SECRET_KEY = os.getenv("SECRET_KEY", "macuin_clave_secreta_204")
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 1440 # 24 horas

# Esquema OAuth2
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token")

def hashear_password(password: str) -> str:
    # Usar PBKDF2 con SHA256 manualmente para evitar bugs de passlib en este entorno
    salt = hashlib.sha256(os.urandom(60)).hexdigest().encode('ascii')
    pwdhash = hashlib.pbkdf2_hmac('sha256', password.encode('utf-8'), salt, 100000)
    pwdhash = binascii.hexlify(pwdhash)
    return (salt + pwdhash).decode('ascii')

def verificar_password(password_plano: str, password_hash: str) -> bool:
    # Verificar el hash generado por el método anterior
    salt = password_hash[:64].encode('ascii')
    stored_hash = password_hash[64:].encode('ascii')
    pwdhash = hashlib.pbkdf2_hmac('sha256', password_plano.encode('utf-8'), salt, 100000)
    return binascii.hexlify(pwdhash) == stored_hash

def crear_token(datos: dict) -> str:
    datos_token = datos.copy()
    expiracion = datetime.utcnow() + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    datos_token["exp"] = expiracion
    return jwt.encode(datos_token, SECRET_KEY, algorithm=ALGORITHM)

def verificar_token(token: str = Depends(oauth2_scheme), db: Session = Depends(get_db)):
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        correo: str = payload.get("sub")
        if correo is None:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Token inválido"
            )
        usuario = db.query(Usuario).filter(Usuario.correo == correo).first()
        if usuario is None or not usuario.activo:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Usuario no encontrado o inactivo"
            )
        return usuario
    except JWTError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Token inválido o expirado"
        )
