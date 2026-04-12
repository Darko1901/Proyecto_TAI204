from app.data.database import SessionLocal
from app.models.categoria import Categoria
from sqlalchemy.orm import Session

def seed_categories():
    db: Session = SessionLocal()
    try:
        categories = [
            (1, 'Frenos'),
            (2, 'Suspensión y Dirección'),
            (3, 'Iluminación'),
            (4, 'Carrocería y Colisión'),
            (5, 'Llantas'),
            (6, 'Kits de Afinación'),
            (7, 'Sensores y Eléctrico'),
            (8, 'Aceites y Aditivos')
        ]
        
        for id_cat, name in categories:
            existing = db.query(Categoria).filter(Categoria.id_categoria == id_cat).first()
            if not existing:
                cat = Categoria(id_categoria=id_cat, nombre_categoria=name)
                db.add(cat)
        
        db.commit()
        print("Categories seeded successfully.")
    except Exception as e:
        print("Error:", e)
        db.rollback()
    finally:
        db.close()

if __name__ == "__main__":
    seed_categories()
