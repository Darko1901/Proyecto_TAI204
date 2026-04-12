from fastapi import APIRouter, HTTPException, status, Depends
from sqlalchemy.orm import Session
from typing import List
from app.data.database import get_db
from app.models.resena import Resena, ResenaCreate, ResenaResponse
from app.security.auth import verificar_token
from app.models.usuario import Usuario

router = APIRouter(prefix="/v1/resenas", tags=["Reseñas"])


@router.get("/producto/{id_producto}", response_model=List[ResenaResponse])
async def obtener_resenas_producto(id_producto: int, db: Session = Depends(get_db)):
    resenas = db.query(Resena).filter(Resena.id_producto == id_producto).all()
    
    # Inyectar nombre de usuario para la respuesta
    resultado = []
    for r in resenas:
        item = ResenaResponse.model_validate(r)
        item.nombre_usuario = f"{r.usuario.nombre} {r.usuario.apellido_paterno}"
        resultado.append(item)
        
    return resultado


@router.post("/", response_model=ResenaResponse, status_code=status.HTTP_201_CREATED)
async def crear_resena(datos: ResenaCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    nueva = Resena(
        id_usuario=usuario_actual.id_usuario,
        id_producto=datos.id_producto,
        calificacion=datos.calificacion,
        comentario=datos.comentario
    )
    db.add(nueva)
    db.commit()
    db.refresh(nueva)
    
    # Response model verification
    res = ResenaResponse.model_validate(nueva)
    res.nombre_usuario = f"{usuario_actual.nombre} {usuario_actual.apellido_paterno}"
    return res


@router.delete("/{id_resena}", status_code=status.HTTP_204_NO_CONTENT)
async def eliminar_resena(id_resena: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    resena = db.query(Resena).filter(Resena.id_resena == id_resena).first()
    if not resena:
        raise HTTPException(status_code=404, detail="Reseña no encontrada")
    
    # Solo el autor o un superadmin (rol 1) puede borrar
    if resena.id_usuario != usuario_actual.id_usuario and usuario_actual.id_rol != 1:
        raise HTTPException(status_code=403, detail="No tienes permisos para eliminar esta reseña")
    
    db.delete(resena)
    db.commit()
