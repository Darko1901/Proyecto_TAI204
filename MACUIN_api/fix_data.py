from app.data.database import SessionLocal
from app.models.producto import Producto
from app.models.categoria import Categoria
from sqlalchemy import or_

def fix_data():
    db = SessionLocal()
    try:
        # 1. Renombrar Categoría 4 si es necesario
        cat4 = db.query(Categoria).filter(Categoria.id_categoria == 4).first()
        if cat4:
            cat4.nombre_categoria = "Motor y Transmisión"
            db.add(cat4)
            print("Categoría 4 renombrada a 'Motor y Transmisión'")

        # 2. Definir Reglas de Re-categorización (Keywords)
        rules = [
            (3, ["foco", "led", "faro", "calavera", "iluminación", "iluminacion", "cuarto"]),
            (1, ["freno", "balata", "disco", "tambor", "booster", "caliper", "manguera de frenos"]),
            (2, ["amortiguador", "suspensión", "suspension", "dirección", "direccion", "terminal", "horquilla", "cremallera", "maza", "rotula", "resorte", "bieleta"]),
            (8, ["aceite", "anticongelante", "refrigerante", "líquido", "liquido", "aditivo"]),
            (5, ["llanta"]),
            (6, ["afinación", "afinacion", "filtro", "bujía", "bujia"]),
            (7, ["sensor", "maf", "ckp", "alternador", "marcha", "bobina", "eléctrico", "electrico"]),
            (4, ["motor", "transmisión", "transmision", "defensa", "espejo", "cofre", "parrilla", "tolva", "salpicadera", "manija", "limpiaparabrisas", "spoiler"])
        ]

        # Nota: He incluido elementos de carrocería en la 4 temporalmente para que no queden huérfanos si el usuario prefiere Motor en esa posición.
        
        updated_count = 0
        for cat_id, keywords in rules:
            query_filters = [Producto.nombre_producto.ilike(f"%{kw}%") for kw in keywords]
            products = db.query(Producto).filter(or_(*query_filters)).all()
            for p in products:
                if p.id_categoria != cat_id:
                    print(f"Moviendo '{p.nombre_producto}' (ID:{p.id_producto}) de Cat {p.id_categoria} a Cat {cat_id}")
                    p.id_categoria = cat_id
                    db.add(p)
                    updated_count += 1
        
        db.commit()
        print(f"Limpieza completada. {updated_count} productos actualizados.")
        
    except Exception as e:
        print(f"Error: {e}")
        db.rollback()
    finally:
        db.close()

if __name__ == "__main__":
    fix_data()
