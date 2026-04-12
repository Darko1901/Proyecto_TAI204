from sqlalchemy import Column, Integer, ForeignKey
from sqlalchemy.orm import relationship
from pydantic import BaseModel, Field
from app.data.database import Base


# SQLAlchemy ORM
class Inventario(Base):
    __tablename__ = "inventario"

    id_inventario = Column(Integer, primary_key=True, index=True)
    id_producto = Column(Integer, ForeignKey("productos.id_producto"), nullable=False)
    id_almacen = Column(Integer, ForeignKey("almacenes.id_almacen"), nullable=False)
    cantidad = Column(Integer, default=0)
    stock_minimo = Column(Integer, default=0)

    producto = relationship("Producto", back_populates="inventarios")
    almacen = relationship("Almacen", back_populates="inventarios")


# Pydantic Schemas
class InventarioCreate(BaseModel):
    id_producto: int
    id_almacen: int
    cantidad: int = Field(..., ge=0)
    stock_minimo: int = Field(..., ge=0)

class InventarioUpdate(BaseModel):
    cantidad: int = Field(..., ge=0)
    stock_minimo: int = Field(..., ge=0)

class InventarioResponse(BaseModel):
    id_inventario: int
    id_producto: int
    id_almacen: int
    cantidad: int
    stock_minimo: int

    model_config = {"from_attributes": True}
