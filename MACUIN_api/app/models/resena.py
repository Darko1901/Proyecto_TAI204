from sqlalchemy import Column, Integer, String, Text, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from pydantic import BaseModel, Field
from datetime import datetime
from typing import Optional
from app.data.database import Base


# SQLAlchemy ORM
class Resena(Base):
    __tablename__ = "resenas"

    id_resena = Column(Integer, primary_key=True, index=True)
    id_usuario = Column(Integer, ForeignKey("usuarios.id_usuario"), nullable=False)
    id_producto = Column(Integer, ForeignKey("productos.id_producto"), nullable=False)
    calificacion = Column(Integer, nullable=False) # 1-5
    comentario = Column(Text, nullable=True)
    fecha = Column(DateTime, default=datetime.utcnow)

    usuario = relationship("Usuario")
    producto = relationship("Producto")


# Pydantic Schemas
class ResenaCreate(BaseModel):
    id_producto: int
    calificacion: int = Field(..., ge=1, le=5)
    comentario: Optional[str] = None

class ResenaResponse(BaseModel):
    id_resena: int
    id_usuario: int
    id_producto: int
    calificacion: int
    comentario: Optional[str]
    fecha: datetime
    nombre_usuario: Optional[str] = None

    model_config = {"from_attributes": True}
