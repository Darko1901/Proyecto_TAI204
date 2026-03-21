from fastapi import FastAPI
from app.data.database import engine, Base
import app.models  # Registra todos los modelos con Base para crear las tablas
from app.routers import auth, autopartes, pedidos, usuarios, reportes

app = FastAPI(
    title="MACUIN API",
    description="API central de MACUIN para la gestión de autopartes, inventarios y pedidos",
    version="1.0"
)

# Crea todas las tablas en PostgreSQL si no existen
Base.metadata.create_all(bind=engine)

# Registro de routers
app.include_router(auth.router)
app.include_router(autopartes.router)
app.include_router(pedidos.router)
app.include_router(usuarios.router)
app.include_router(reportes.router)
