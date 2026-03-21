from sqlalchemy import Column, Integer, String
from sqlalchemy.orm import relationship
from pydantic import BaseModel
from app.data.database import Base


# SQLAlchemy ORM
class Categoria(Base):
    __tablename__ = "categorias"

    id_categoria = Column(Integer, primary_key=True, index=True)
    nombre_categoria = Column(String(50), nullable=False, unique=True)

    productos = relationship("Producto", back_populates="categoria")


# Pydantic Schemas
class CategoriaCreate(BaseModel):
    nombre_categoria: str

class CategoriaResponse(BaseModel):
    id_categoria: int
    nombre_categoria: str

    model_config = {"from_attributes": True}
