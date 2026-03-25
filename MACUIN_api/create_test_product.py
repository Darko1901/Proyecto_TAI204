import sys
import os
sys.path.append('/app')
sys.path.append('/')

from app.data.database import SessionLocal
from app.models.producto import Producto
from app.models.inventario import Inventario
from app.models.categoria import Categoria
from app.models.almacen import Almacen
from sqlalchemy.orm import Session
from sqlalchemy import text

def setup_test_db():
    db: Session = SessionLocal()
    try:
        # 1. Create Category
        cat = db.query(Categoria).filter(Categoria.id_categoria == 1).first()
        if not cat:
            cat = Categoria(id_categoria=1, nombre_categoria="Motor / Mecánica")
            db.add(cat)
            db.commit()
            print("Created category.")

        # 2. Create Almacen
        alm = db.query(Almacen).filter(Almacen.id_almacen == 1).first()
        if not alm:
            alm = Almacen(id_almacen=1, nombre_almacen="Almacén Principal", ubicacion="Norte")
            db.add(alm)
            db.commit()
            print("Created warehouse.")
            
        # 3. Create Product
        p = Producto(
            nombre_producto="Filtro de Aceite Premium",
            descripcion="Filtro de alto rendimiento para motores sintéticos",
            precio=450.00,
            id_categoria=1,
            marca="Bosch",
            modelo="X-200",
            garantia="1 año"
        )
        db.add(p)
        db.flush()
        
        # 4. Create initial inventory
        inv = Inventario(
            id_producto=p.id_producto,
            id_almacen=1,
            cantidad=50,
            stock_minimo=10
        )
        db.add(inv)
        db.commit()
        print(f"Created product ID: {p.id_producto} with 50 units.")
    except Exception as e:
        print("Error:", e)
    finally:
        db.close()

if __name__ == "__main__":
    setup_test_db()
