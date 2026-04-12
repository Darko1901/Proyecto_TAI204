from sqlalchemy import Column, Integer, Numeric, ForeignKey
from sqlalchemy.orm import relationship
from pydantic import BaseModel, Field
from decimal import Decimal
from typing import Optional
from app.data.database import Base


# SQLAlchemy ORM
class DetallePedido(Base):
    __tablename__ = "detalle_pedido"

    id_detalle = Column(Integer, primary_key=True, index=True)
    id_pedido = Column(Integer, ForeignKey("pedidos.id_pedido"), nullable=False)
    id_producto = Column(Integer, ForeignKey("productos.id_producto"), nullable=False)
    cantidad = Column(Integer, nullable=False)
    precio_unitario = Column(Numeric(10, 2), nullable=False)

    pedido = relationship("Pedido", back_populates="detalles")
    producto = relationship("Producto", back_populates="detalles")


# Pydantic Schemas
class DetallePedidoCreate(BaseModel):
    id_producto: int
    cantidad: int = Field(..., gt=0)
    precio_unitario: Decimal = Field(..., gt=0)

class DetallePedidoResponse(BaseModel):
    id_detalle: int
    id_pedido: int
    id_producto: int
    cantidad: int
    precio_unitario: Decimal
    nombre_producto: Optional[str] = None
    imagen: Optional[str] = None

    @classmethod
    def model_validate(cls, obj, **kwargs):
        data = super().model_validate(obj, **kwargs)
        if hasattr(obj, 'producto') and obj.producto:
            data.nombre_producto = obj.producto.nombre_producto
            data.imagen = obj.producto.imagen
        return data

    model_config = {"from_attributes": True}
