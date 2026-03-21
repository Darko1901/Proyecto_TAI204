from sqlalchemy import Column, Integer, String
from sqlalchemy.orm import relationship
from pydantic import BaseModel
from typing import Optional
from app.data.database import Base


# SQLAlchemy ORM
class Almacen(Base):
    __tablename__ = "almacenes"

    id_almacen = Column(Integer, primary_key=True, index=True)
    nombre_almacen = Column(String(50), nullable=False)
    ubicacion = Column(String(50), nullable=True)

    inventarios = relationship("Inventario", back_populates="almacen")


# Pydantic Schemas
class AlmacenCreate(BaseModel):
    nombre_almacen: str
    ubicacion: Optional[str] = None

class AlmacenResponse(BaseModel):
    id_almacen: int
    nombre_almacen: str
    ubicacion: Optional[str]

    model_config = {"from_attributes": True}
