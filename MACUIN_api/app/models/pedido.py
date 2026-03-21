from sqlalchemy import Column, Integer, Numeric, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from pydantic import BaseModel, Field
from decimal import Decimal
from datetime import datetime
from typing import List, Optional
from app.data.database import Base
from app.models.detalle_pedido import DetallePedidoCreate, DetallePedidoResponse


# SQLAlchemy ORM
class Pedido(Base):
    __tablename__ = "pedidos"

    id_pedido = Column(Integer, primary_key=True, index=True)
    fecha_pedido = Column(DateTime, default=datetime.utcnow)
    total = Column(Numeric(10, 2), nullable=False)
    id_usuario = Column(Integer, ForeignKey("usuarios.id_usuario"), nullable=False)
    id_estado = Column(Integer, ForeignKey("estados_pedido.id_estado"), nullable=False, default=1)

    usuario = relationship("Usuario", back_populates="pedidos")
    estado = relationship("EstadoPedido", back_populates="pedidos")
    detalles = relationship("DetallePedido", back_populates="pedido")
    envio = relationship("Envio", back_populates="pedido", uselist=False)


# Pydantic Schemas
class PedidoCreate(BaseModel):
    detalles: List[DetallePedidoCreate]
    direccion: str
    ciudad: str
    codigo_postal: str = Field(..., max_length=10)
    telefono_contacto: str = Field(..., max_length=20)
    notas: Optional[str] = None

class PedidoEstadoUpdate(BaseModel):
    id_estado: int

class PedidoResponse(BaseModel):
    id_pedido: int
    fecha_pedido: datetime
    total: Decimal
    id_usuario: int
    id_estado: int

    model_config = {"from_attributes": True}

class PedidoDetalladoResponse(PedidoResponse):
    detalles: List[DetallePedidoResponse] = []
