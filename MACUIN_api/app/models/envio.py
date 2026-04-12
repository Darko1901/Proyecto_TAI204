from sqlalchemy import Column, Integer, String, DateTime, Text, ForeignKey
from sqlalchemy.orm import relationship
from pydantic import BaseModel
from datetime import datetime
from typing import Optional
from app.data.database import Base


# SQLAlchemy ORM
class Envio(Base):
    __tablename__ = "envios"

    id_envio = Column(Integer, primary_key=True, index=True)
    fecha_envio = Column(DateTime, nullable=True)
    estado_envio = Column(String(50), default="pendiente")
    id_pedido = Column(Integer, ForeignKey("pedidos.id_pedido"), nullable=False, unique=True)
    direccion = Column(String(255), nullable=False)
    ciudad = Column(String(100), nullable=False)
    codigo_postal = Column(String(10), nullable=False)
    telefono_contacto = Column(String(20), nullable=False)
    notas = Column(Text, nullable=True)
    paqueteria = Column(String(100), nullable=True, default="MACUIN Fleet Management")

    pedido = relationship("Pedido", back_populates="envio")


# Pydantic Schema
class EnvioResponse(BaseModel):
    id_envio: int
    fecha_envio: Optional[datetime]
    estado_envio: str
    id_pedido: int
    direccion: str
    ciudad: str
    codigo_postal: str
    telefono_contacto: str
    notas: Optional[str]
    paqueteria: Optional[str] = "MACUIN Fleet Management"

    model_config = {"from_attributes": True}
