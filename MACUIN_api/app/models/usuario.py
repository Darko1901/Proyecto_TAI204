from sqlalchemy import Column, Integer, String, Boolean, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from pydantic import BaseModel, Field
from datetime import datetime
from typing import Optional
from app.data.database import Base


# SQLAlchemy ORM
class Usuario(Base):
    __tablename__ = "usuarios"

    id_usuario = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(50), nullable=False)
    apellido_paterno = Column(String(50), nullable=False)
    apellido_materno = Column(String(50), nullable=False)
    correo = Column(String(100), unique=True, nullable=False, index=True)
    telefono = Column(String(20), nullable=True)
    password = Column(String(255), nullable=False)
    activo = Column(Boolean, default=True)
    fecha_registro = Column(DateTime, default=datetime.utcnow)
    id_rol = Column(Integer, ForeignKey("roles.id_rol"), nullable=False)

    rol = relationship("Rol", back_populates="usuarios")
    pedidos = relationship("Pedido", back_populates="usuario")


# Pydantic Schemas
class UsuarioCreate(BaseModel):
    nombre: str = Field(..., min_length=2, max_length=50)
    apellido_paterno: str = Field(..., min_length=2, max_length=50)
    apellido_materno: str = Field(..., min_length=2, max_length=50)
    correo: str = Field(..., max_length=100)
    telefono: Optional[str] = Field(None, max_length=20)
    password: str = Field(..., min_length=6)
    id_rol: int

class UsuarioUpdate(BaseModel):
    nombre: Optional[str] = Field(None, min_length=2, max_length=50)
    apellido_paterno: Optional[str] = Field(None, min_length=2, max_length=50)
    apellido_materno: Optional[str] = Field(None, min_length=2, max_length=50)
    telefono: Optional[str] = Field(None, max_length=20)
    activo: Optional[bool] = None

class UsuarioResponse(BaseModel):
    id_usuario: int
    nombre: str
    apellido_paterno: str
    apellido_materno: str
    correo: str
    telefono: Optional[str]
    activo: bool
    fecha_registro: datetime
    id_rol: int

    model_config = {"from_attributes": True}
