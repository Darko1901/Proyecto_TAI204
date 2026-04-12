from flask import Blueprint, render_template, request, redirect, url_for, flash, session
import json
import base64
from security.api_client import login_api

auth_bp = Blueprint('auth', __name__)

@auth_bp.route('/')
def index():
    return redirect(url_for('auth.login_personal_interno'))

@auth_bp.route('/login_personal_interno', methods=['GET', 'POST'])
def login_personal_interno():
    if 'usuario' in session:
        return redirect(url_for('views.dashboard'))

    if request.method == 'POST':
        usuario = request.form.get('usuario', '')
        contrasena = request.form.get('contrasena', '')
        
        if '@' not in usuario:
            flash('Formato inválido: El usuario debe contener un "@".', 'error')
            return render_template('login_personal.html')
            
        if usuario and contrasena:
            response = login_api(usuario, contrasena)
            if response and response.status_code == 200:
                token_data = response.json()
                access_token = token_data.get('access_token')
                
                # Decodificar el JWT (base64) para obtener el rol sin dependencias extras
                try:
                    payload_b64 = access_token.split('.')[1]
                    # Corregir padding para base64
                    payload_json = base64.b64decode(payload_b64 + '=' * (-len(payload_b64) % 4)).decode('utf-8')
                    payload = json.loads(payload_json)
                    rol = payload.get('rol')
                    nombre = payload.get('nombre')
                    id_usuario = payload.get('id_usuario')
                    session['rol'] = rol
                    session['nombre'] = nombre
                    session['id_usuario'] = id_usuario
                except Exception:
                    rol = None

                session['usuario'] = usuario
                session['token'] = access_token
                
                if rol == 1:
                    return redirect(url_for('views.superadmin'))
                return redirect(url_for('views.dashboard'))
            else:
                flash('Credenciales incorrectas o error en el servidor.', 'error')
            
    return render_template('login_personal.html')

@auth_bp.route('/logout')
def logout():
    session.clear() 
    return redirect(url_for('auth.login_personal_interno'))

@auth_bp.route('/recuperar')
def recuperar():
    return render_template('recuperar.html')
