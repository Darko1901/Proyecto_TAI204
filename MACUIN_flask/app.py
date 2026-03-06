from flask import Flask, render_template
from flask import request, redirect, flash,url_for, session
import os

app = Flask(__name__)
app.secret_key = 'macuin_secreto_123'

# ==========================================
# BASE DE DATOS SIMULADA (Roles y Permisos)
# ==========================================
USUARIOS_DB = {
    "master": {
        "contrasena": "admin123",
        "rol": "superadmin"
    },
    "empleado": {
        "contrasena": "ventas123",
        "rol": "operativo"
    }
}


@app.route('/')
def index():
    return redirect(url_for('login_personal_interno'))


@app.route('/login_personal_interno', methods=['GET', 'POST'])
def login_personal_interno():
    if 'usuario' in session:
        return redirect(url_for('dashboard'))

    if request.method == 'POST':
        usuario = request.form.get('usuario', '')
        contrasena = request.form.get('contrasena', '')
        
        if '@' not in usuario:
            flash('Formato inválido: El usuario debe contener un "@".', 'error')
            return render_template('login_personal.html')
            
        if usuario and contrasena:
            session['usuario'] = usuario
            return redirect(url_for('dashboard'))
            
    return render_template('login_personal.html')


@app.route('/superadmin')
def superadmin():
    if 'usuario' not in session:
        return redirect(url_for('login_personal_interno'))
    return render_template('superadmin.html')

@app.route('/dashboard')
def dashboard():
    if 'usuario' not in session:
        return redirect(url_for('login_personal_interno'))
    return render_template('dashboard.html')

@app.route('/logout')
def logout():
    session.clear() 
    return redirect(url_for('login_personal_interno'))

@app.route('/ventas')
def ventas():
    if 'usuario' not in session:
        return redirect(url_for('login_personal_interno'))
    return render_template('ventas.html')

@app.route('/logistica')
def logistica():
    if 'usuario' not in session:
        return redirect(url_for('login_personal_interno'))
    return render_template('logistica.html')

@app.route('/almacen')
def almacen():
    if 'usuario' not in session:
        return redirect(url_for('login_personal_interno'))
    return render_template('almacen.html')

@app.route('/usuarios')
def usuarios():
    if 'usuario' not in session:
        return redirect(url_for('login_personal_interno'))
    return render_template('usuarios.html')

@app.route('/recuperar')
def recuperar():
    return render_template('recuperar.html')


if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5001)