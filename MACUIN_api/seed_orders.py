import sys
import os
import random
from datetime import datetime, timedelta

# Añadir el directorio actual al path para importar app
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.data.database import SessionLocal
from app.models.usuario import Usuario
from app.models.producto import Producto
from app.models.pedido import Pedido
from app.models.detalle_pedido import DetallePedido
from app.models.envio import Envio
from app.models.inventario import Inventario
from app.security.auth import hashear_password

def seed_test_orders():
    db = SessionLocal()
    try:
        # 1. Asegurar que los 4 usuarios de prueba existan
        test_emails = [
            "client_test_1@example.com",
            "client_test_2@example.com",
            "client_test_3@example.com",
            "client_test_4@example.com"
        ]
        
        users = []
        for i, email in enumerate(test_emails):
            usr = db.query(Usuario).filter(Usuario.correo == email).first()
            if not usr:
                usr = Usuario(
                    nombre=f"Cliente Prueba {i+1}",
                    apellido_paterno="Test",
                    apellido_materno="MACUIN",
                    correo=email,
                    telefono=f"442100000{i+1}",
                    password=hashear_password("Test123456"),
                    id_rol=5, # Cliente
                    activo=True
                )
                db.add(usr)
                db.commit()
                db.refresh(usr)
            users.append(usr)
            
        print(f"Usuarios de prueba listos: {[u.correo for u in users]}")

        # 2. Obtener productos disponibles
        productos = db.query(Producto).all()
        if not productos:
            print("No hay productos en la base de datos. Abortando órdenes.")
            return

        # 3. Generar 10 órdenes por cada usuario
        status_pool = [1, 2, 3, 4] # Recibido, Surtido, Enviado, Entregado
        paqueterias = ["DHL", "FedEx", "Estafeta", "MACUIN Fleet"]
        ciudades = ["Querétaro", "CDMX", "Monterrey", "Guadalajara", "Puebla"]
        
        for user in users:
            print(f"Generando 10 órdenes para {user.correo}...")
            for j in range(10):
                # Seleccionar 1-3 productos aleatorios
                items_count = random.randint(1, 3)
                selected_prods = random.sample(productos, items_count)
                
                total = 0
                for p in selected_prods:
                    total += p.precio * 1 # Cantidad 1 para simplificar
                
                id_estado = random.choice(status_pool)
                
                nuevo_pedido = Pedido(
                    id_usuario=user.id_usuario,
                    id_estado=id_estado,
                    total=total,
                    fecha_pedido=datetime.utcnow() - timedelta(days=random.randint(0, 30))
                )
                db.add(nuevo_pedido)
                db.flush()
                
                for p in selected_prods:
                    db.add(DetallePedido(
                        id_pedido=nuevo_pedido.id_pedido,
                        id_producto=p.id_producto,
                        cantidad=1,
                        precio_unitario=p.precio
                    ))
                    # Ajustar stock
                    inv = db.query(Inventario).filter(Inventario.id_producto == p.id_producto).first()
                    if inv and inv.cantidad > 0:
                        inv.cantidad -= 1
                
                # Crear Envío
                db.add(Envio(
                    id_pedido=nuevo_pedido.id_pedido,
                    direccion=f"Calle de Prueba #{random.randint(1,999)}",
                    ciudad=random.choice(ciudades),
                    codigo_postal=str(random.randint(10000, 99999)),
                    telefono_contacto=user.telefono,
                    notas=f"Orden de prueba automática {j+1}",
                    paqueteria=random.choice(paqueterias),
                    fecha_envio=datetime.utcnow() if id_estado >= 3 else None,
                    estado_envio="enviado" if id_estado >= 3 else "pendiente"
                ))
                
            db.commit()
        
        print("Órdenes de prueba generadas exitosamente.")

    except Exception as e:
        print(f"Error sembrando órdenes: {e}")
        db.rollback()
    finally:
        db.close()

if __name__ == "__main__":
    seed_test_orders()
