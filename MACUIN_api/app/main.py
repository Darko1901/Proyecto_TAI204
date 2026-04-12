from fastapi import FastAPI
from fastapi.staticfiles import StaticFiles
from app.data.database import engine, Base
from fastapi.middleware.cors import CORSMiddleware
import app.models  # Registra todos los modelos con Base para crear las tablas
from app.routers import auth, autopartes, pedidos, usuarios, reportes, resenas

app = FastAPI(
    title="MACUIN API",
    description="API central de MACUIN para la gestión de autopartes, inventarios y pedidos",
    version="1.0"
)

# Configuración de CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # En producción, restringir a los dominios de Flask y Laravel
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Servir archivos estáticos (imágenes de productos)
app.mount("/static", StaticFiles(directory="app/static"), name="static")

# Crea todas las tablas en PostgreSQL si no existen
Base.metadata.create_all(bind=engine)

# Registro de routers
app.include_router(auth.router)
app.include_router(autopartes.router)
app.include_router(pedidos.router)
app.include_router(usuarios.router)
app.include_router(reportes.router)
app.include_router(resenas.router)
