import sys
import os
from sqlalchemy import text
from sqlalchemy.orm import Session

# Añadir el directorio actual al path para importar app
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.data.database import SessionLocal, Base, engine
from app.models.rol import Rol
from app.models.categoria import Categoria
from app.models.usuario import Usuario
from app.models.producto import Producto
from app.models.inventario import Inventario
from app.models.almacen import Almacen
from app.models.estado_pedido import EstadoPedido
from app.security.auth import hashear_password

def seed_database():
    db: Session = SessionLocal()
    try:
        print("Iniciando sembrado de base de datos...")
        
        # 1. Crear Roles si no existen
        roles = [
            (1, "Superadmin"),
            (2, "Trabajador"),
            (3, "Empresa"),
            (4, "Ventas"),
            (5, "Cliente")
        ]
        for id_rol, nombre in roles:
            db.execute(text(f"INSERT INTO roles (id_rol, nombre_rol) VALUES ({id_rol}, '{nombre}') ON CONFLICT (id_rol) DO NOTHING"))
        db.commit()
        print("Roles verificados.")

        # 2. Crear Estados de Pedido
        estados = [
            (1, "Recibido"),
            (2, "Surtido"),
            (3, "Enviado"),
            (4, "Completado"),
            (5, "Cancelado")
        ]
        for id_est, nombre in estados:
            db.execute(text(f"INSERT INTO estados_pedido (id_estado, nombre_estado) VALUES ({id_est}, '{nombre}') ON CONFLICT (id_estado) DO NOTHING"))
        db.commit()
        print("Estados de pedido verificados.")

        # 3. Crear Categorías
        categorias_map = {
            "Frenos": 1,
            "Suspension y Direccion": 2,
            "Iluminacion": 3,
            "Carroceria y Colision": 4,
            "Llantas": 5,
            "Kits de Afinacion": 6,
            "Sensores y Electrico": 7,
            "Aceites y Aditivos": 8
        }
        for nombre, id_cat in categorias_map.items():
            db.execute(text(f"INSERT INTO categorias (id_categoria, nombre_categoria) VALUES ({id_cat}, '{nombre}') ON CONFLICT (id_categoria) DO NOTHING"))
        db.commit()
        print("Categorías verificadas.")

        # 4. Crear Almacén por defecto
        db.execute(text("INSERT INTO almacenes (id_almacen, nombre_almacen, ubicacion) VALUES (1, 'Almacén Central', 'Planta Baja') ON CONFLICT (id_almacen) DO NOTHING"))
        db.commit()
        print("Almacén verificado.")

        # 5. Crear Usuarios Maestro (Superadmin y Cliente Interno)
        usuarios_iniciales = [
            {
                "nombre": "Alberto",
                "apellido_paterno": "Luna",
                "apellido_materno": "Master",
                "correo": "albertolunaaa@gmail.com",
                "password": hashear_password("Luna0203fe@"),
                "id_rol": 1, # Superadmin
                "activo": True
            },
            {
                "nombre": "Jochua",
                "apellido_paterno": "V",
                "apellido_materno": "Interno",
                "correo": "jochua@gmail.com",
                "password": hashear_password("Leon123@"),
                "id_rol": 2, # Trabajador / Cliente Interno
                "activo": True
            }
        ]
        
        for user_data in usuarios_iniciales:
            usr = db.query(Usuario).filter(Usuario.correo == user_data['correo']).first()
            if not usr:
                nuevo_usr = Usuario(**user_data)
                db.add(nuevo_usr)
                print(f"Usuario creado: {user_data['correo']}")
            else:
                usr.password = user_data['password']
                usr.id_rol = user_data['id_rol']
                usr.activo = user_data['activo']
                db.add(usr)
                print(f"Usuario actualizado: {user_data['correo']}")
        db.commit()

        # 6. Importar Productos desde products.json (Catálogo anterior)
        import json
        products_file = os.path.join(os.path.dirname(__file__), "products.json")
        if os.path.exists(products_file):
            with open(products_file, 'r', encoding='utf-8') as f:
                prods_data = json.load(f)
            
            for pid, pdata in prods_data.items():
                existing = db.query(Producto).filter(Producto.id_producto == int(pid)).first()
                if not existing:
                    # Limpiar precio: "$1,200.00 MXN" -> 1200.00
                    precio_str = pdata.get('precio', '0').replace('$', '').replace('MXN', '').replace(',', '').strip()
                    try:
                        precio_val = float(precio_str)
                    except:
                        precio_val = 0.0

                    id_cat = categorias_map.get(pdata.get('categoria'), 8) # Default Aceites
                    
                    # Imagen path: "img/productos/..." -> "/static/uploads/..."
                    img_path = pdata.get('imagen', '').split('/')[-1]
                    final_img = f"/static/uploads/{img_path}"

                    nuevo_prod = Producto(
                        id_producto=int(pid),
                        nombre_producto=pdata.get('nombre'),
                        descripcion=pdata.get('descripcion', ''),
                        precio=precio_val,
                        marca=pdata.get('marca', 'Genérica'),
                        modelo=pdata.get('modelo', 'Universal'),
                        id_categoria=id_cat,
                        imagen=final_img
                    )
                    db.add(nuevo_prod)
                    db.flush() # Para obtener el ID si fuera necesario

                    # Stock inicial
                    nuevo_inv = Inventario(
                        id_producto=int(pid),
                        id_almacen=1,
                        cantidad=50 if pdata.get('disponible') else 0,
                        stock_minimo=5
                    )
                    db.add(nuevo_inv)

            db.commit()
            print(f"Importados {len(prods_data)} productos del catálogo original.")
        else:
            print("No se encontró products.json, saltando importación masiva.")
        
        print("Sembrado completado exitosamente.")
        
    except Exception as e:
        print(f"Error durante el sembrado: {e}")
        db.rollback()
    finally:
        db.close()

if __name__ == "__main__":
    seed_database()
