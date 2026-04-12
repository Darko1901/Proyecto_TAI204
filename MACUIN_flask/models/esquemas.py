"""
Este archivo contiene modelos Pydantic o representaciones de datos
para tipado en el frontend si en un futuro se usan Formularios de Validación.
En Flask, generalmente usamos Flask-WTF, pero para mantener la misma
estructura vista en FastAPI, aquí reservamos el espacio para BaseModels.
"""

from typing import Optional
from pydantic import BaseModel

class LoginFrontModel(BaseModel):
    usuario: str
    contrasena: str

class PedidoFrontModel(BaseModel):
    id: str
    cliente: str
    piezas: str
    fecha: str
    total: float
    estatus: str
