from sqlalchemy import Column, Integer, String
from sqlalchemy.orm import relationship
from pydantic import BaseModel
from app.data.database import Base


# SQLAlchemy ORM
class EstadoPedido(Base):
    __tablename__ = "estados_pedido"

    id_estado = Column(Integer, primary_key=True, index=True)
    nombre_estado = Column(String(50), nullable=False, unique=True)

    pedidos = relationship("Pedido", back_populates="estado")


# Pydantic Schema
class EstadoPedidoResponse(BaseModel):
    id_estado: int
    nombre_estado: str

    model_config = {"from_attributes": True}
