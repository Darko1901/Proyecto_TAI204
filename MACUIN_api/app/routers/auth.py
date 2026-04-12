from fastapi import APIRouter, HTTPException, status, Depends
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy.orm import Session
from app.data.database import get_db
from app.models.usuario import Usuario, UsuarioCreate, UsuarioResponse
from app.security.auth import verificar_password, crear_token, hashear_password

router = APIRouter(tags=["Autenticación"])


@router.post("/token")
async def login(form_data: OAuth2PasswordRequestForm = Depends(), db: Session = Depends(get_db)):
    """Recibe correo y contraseña, retorna JWT si las credenciales son válidas."""
    usuario = db.query(Usuario).filter(Usuario.correo == form_data.username).first()
    if not usuario or not verificar_password(form_data.password, usuario.password):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales incorrectas"
        )
    token = crear_token({"sub": usuario.correo, "rol": usuario.id_rol, "nombre": usuario.nombre, "id_usuario": usuario.id_usuario})
    return {"access_token": token, "token_type": "bearer"}


@router.post("/registro", response_model=UsuarioResponse, status_code=status.HTTP_201_CREATED)
async def registro(datos: UsuarioCreate, db: Session = Depends(get_db)):
    """Registro de clientes externos (id_rol = 1)."""
    if db.query(Usuario).filter(Usuario.correo == datos.correo).first():
        raise HTTPException(status_code=400, detail="El correo ya está registrado")
    nuevo = Usuario(
        nombre=datos.nombre,
        apellido_paterno=datos.apellido_paterno,
        apellido_materno=datos.apellido_materno,
        correo=datos.correo,
        telefono=datos.telefono,
        password=hashear_password(datos.password),
        id_rol=datos.id_rol
    )
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
    return nuevo
