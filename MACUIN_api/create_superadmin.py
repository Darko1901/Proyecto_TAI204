import sys
import os
sys.path.append('/app')
sys.path.append('/')

from app.data.database import SessionLocal
from app.models.usuario import Usuario
from app.security.auth import hashear_password
from sqlalchemy.orm import Session
from sqlalchemy import text

def create_superadmin():
    db: Session = SessionLocal()
    try:
        # Check if role 1 exists (usually Superadmin/Admin)
        res = db.execute(text("SELECT id_rol, nombre_rol FROM roles")).fetchall()
        print("Roles in DB:", res)
        
        superadmin_role_id = 1 # We'll assume 1 is admin, or we can find it
        if res:
            for r in res:
                if 'admin' in str(r[1]).lower() or 'super' in str(r[1]).lower():
                    superadmin_role_id = r[0]
                    break
        else:
            print("No roles found, might need to insert a role first.")
            db.execute(text("INSERT INTO roles (id_rol, nombre_rol) VALUES (1, 'Superadmin') ON CONFLICT DO NOTHING"))
            db.commit()
            superadmin_role_id = 1
            
        print(f"Using role ID: {superadmin_role_id}")
            
        email = "albertolunaaa@gmail.com"
        password_plano = "Luna0203fe@"
        hashed_pw = hashear_password(password_plano)
        
        existing = db.query(Usuario).filter(Usuario.correo == email).first()
        if existing:
            print(f"Updating password for existing user {email}")
            existing.password = hashed_pw
            existing.id_rol = superadmin_role_id
            existing.activo = True
            db.commit()
            print("Successfully updated user.")
        else:
            print(f"Creating new user {email}")
            new_user = Usuario(
                nombre="Alberto",
                apellido_paterno="Luna",
                apellido_materno="",
                correo=email,
                telefono="5555555555",
                password=hashed_pw,
                id_rol=superadmin_role_id,
                activo=True
            )
            db.add(new_user)
            db.commit()
            print("Successfully created superadmin user.")
    except Exception as e:
        print("Error:", e)
    finally:
        db.close()

if __name__ == "__main__":
    create_superadmin()
