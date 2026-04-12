from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, declarative_base
import os

# URL de conexión — se lee desde variable de entorno (Docker) o usa valor por defecto
DATABASE_URL = os.getenv(
    "DATABASE_URL",
    "postgresql://admin:macuin123@postgres:5432/macuin_db"
)

# Motor de conexión
engine = create_engine(DATABASE_URL)

# Gestor de sesiones
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

# Base para los modelos SQLAlchemy
Base = declarative_base()


# Dependencia para obtener la sesión de BD en cada request
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
