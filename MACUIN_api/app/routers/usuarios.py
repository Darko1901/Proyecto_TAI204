from fastapi import APIRouter, HTTPException, status, Depends
from sqlalchemy.orm import Session
from typing import List
from app.data.database import get_db
from app.models.usuario import Usuario, UsuarioCreate, UsuarioUpdate, UsuarioResponse
from app.security.auth import verificar_token, hashear_password

router = APIRouter(prefix="/v1/usuarios", tags=["Usuarios Internos"])


@router.get("/me", response_model=UsuarioResponse)
async def obtener_perfil_actual(usuario_actual: Usuario = Depends(verificar_token)):
    return usuario_actual


@router.get("/", response_model=List[UsuarioResponse])
async def obtener_usuarios(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    return db.query(Usuario).all()


@router.get("/{id_usuario}", response_model=UsuarioResponse)
async def obtener_usuario(id_usuario: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    usuario = db.query(Usuario).filter(Usuario.id_usuario == id_usuario).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    return usuario


@router.post("/", response_model=UsuarioResponse, status_code=status.HTTP_201_CREATED)
async def crear_usuario(datos: UsuarioCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
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


@router.patch("/{id_usuario}", response_model=UsuarioResponse)
async def actualizar_usuario(id_usuario: int, datos: UsuarioUpdate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    usuario = db.query(Usuario).filter(Usuario.id_usuario == id_usuario).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    for campo, valor in datos.model_dump(exclude_none=True).items():
        setattr(usuario, campo, valor)
    db.commit()
    db.refresh(usuario)
    return usuario


@router.delete("/{id_usuario}", status_code=status.HTTP_204_NO_CONTENT)
async def eliminar_usuario(id_usuario: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    usuario = db.query(Usuario).filter(Usuario.id_usuario == id_usuario).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    db.delete(usuario)
    db.commit()


@router.post("/{id_usuario}/toggle-active", response_model=UsuarioResponse)
async def toggle_usuario_activo(id_usuario: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    """Invierten el estado 'activo' del usuario."""
    if usuario_actual.id_rol != 1:
        raise HTTPException(status_code=403, detail="No tienes permisos para realizar esta acción")
    
    usuario = db.query(Usuario).filter(Usuario.id_usuario == id_usuario).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
        
    usuario.activo = not usuario.activo
    db.commit()
    db.refresh(usuario)
    return usuario
