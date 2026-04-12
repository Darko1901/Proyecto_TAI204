from sqlalchemy import Column, Integer, String
from sqlalchemy.orm import relationship
from pydantic import BaseModel
from app.data.database import Base


# SQLAlchemy ORM
class Rol(Base):
    __tablename__ = "roles"

    id_rol = Column(Integer, primary_key=True, index=True)
    nombre_rol = Column(String(50), nullable=False, unique=True)

    usuarios = relationship("Usuario", back_populates="rol")


# Pydantic Schemas
class RolCreate(BaseModel):
    nombre_rol: str

class RolResponse(BaseModel):
    id_rol: int
    nombre_rol: str

    model_config = {"from_attributes": True}
