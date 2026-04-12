from sqlalchemy import Column, Integer, String, Numeric, Text, ForeignKey
from sqlalchemy.orm import relationship
from pydantic import BaseModel, Field
from decimal import Decimal
from typing import Optional
from app.data.database import Base


# SQLAlchemy ORM
class Producto(Base):
    __tablename__ = "productos"

    id_producto = Column(Integer, primary_key=True, index=True)
    nombre_producto = Column(String(100), nullable=False)
    descripcion = Column(String(255), nullable=True)
    precio = Column(Numeric(10, 2), nullable=False)
    imagen = Column(String(255), nullable=True)
    marca = Column(String(100), nullable=True)
    modelo = Column(String(100), nullable=True)
    compatibilidad = Column(Text, nullable=True)
    garantia = Column(String(100), nullable=True)
    id_categoria = Column(Integer, ForeignKey("categorias.id_categoria"), nullable=False)
    activo = Column(Integer, default=1) # 1 for True, 0 for False (standard for some DBs)

    categoria = relationship("Categoria", back_populates="productos")
    inventarios = relationship("Inventario", back_populates="producto")
    detalles = relationship("DetallePedido", back_populates="producto")


# Pydantic Schemas
class ProductoCreate(BaseModel):
    nombre_producto: str = Field(..., min_length=2, max_length=100)
    descripcion: Optional[str] = Field(None, max_length=255)
    precio: Decimal = Field(..., gt=0)
    imagen: Optional[str] = None
    marca: Optional[str] = Field(None, max_length=100)
    modelo: Optional[str] = Field(None, max_length=100)
    compatibilidad: Optional[str] = None
    garantia: Optional[str] = Field(None, max_length=100)
    id_categoria: int
    activo: Optional[bool] = True

class ProductoUpdate(BaseModel):
    nombre_producto: Optional[str] = Field(None, min_length=2, max_length=100)
    descripcion: Optional[str] = Field(None, max_length=255)
    precio: Optional[Decimal] = Field(None, gt=0)
    imagen: Optional[str] = None
    marca: Optional[str] = None
    modelo: Optional[str] = None
    compatibilidad: Optional[str] = None
    garantia: Optional[str] = None
    id_categoria: Optional[int] = None
    activo: Optional[bool] = None

class ProductoResponse(BaseModel):
    id_producto: int
    nombre_producto: str
    descripcion: Optional[str]
    precio: Decimal
    imagen: Optional[str]
    marca: Optional[str]
    modelo: Optional[str]
    compatibilidad: Optional[str]
    garantia: Optional[str]
    id_categoria: int
    activo: bool = True
    stock: int = 0
    stock_minimo: int = 0

    model_config = {"from_attributes": True}
