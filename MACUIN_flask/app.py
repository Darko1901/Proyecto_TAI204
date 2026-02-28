from flask import Flask, render_template
from flask import request, redirect, flash,url_for, session
import os

app = Flask(__name__)

app.secret_key = "macuin_secret"

productos = [
    {
        "id": 1,
        "nombre": "Frenos",
        "precio": 1200,
        "descripcion": "Sistema de frenos de alta calidad para mayor seguridad.",
        "imagen": "frenos.png"
    },
    {
        "id": 2,
        "nombre": "Amortiguadores",
        "precio": 2300,
        "descripcion": "Amortiguadores resistentes para todo tipo de terreno.",
        "imagen": "amortiguador.png"
    }
]
    

@app.route('/')
def index():
    return render_template('login.html')

@app.route('/login')
def login():
    return render_template('login.html')

@app.route('/registro')
def registro():
    return render_template('registro.html')

@app.route('/login_personal_interno')
def login_personal_interno():
    return render_template('login_personal.html')

@app.route('/dashboard')
def dashboard():
    return render_template('dashboard.html')

#perfil de usuario con formulario para actualizar datos y cambiar contraseña

@app.route('/perfil', methods=['GET', 'POST'])
def perfil():

    usuario = {
        "nombre": "Alberto Luna",
        "correo": "alberto@gmail.com",
        "telefono": "4421234567"
    }

    if request.method == 'POST':

      
        if 'guardar_datos' in request.form:

            nombre = request.form['nombre']
            correo = request.form['correo']
            telefono = request.form['telefono']

            

            flash("Información actualizada correctamente", "success")


        
        elif 'cambiar_password' in request.form:

            actual = request.form['actual']
            nueva = request.form['nueva']
            confirmar = request.form['confirmar']

            if nueva != confirmar:
                flash("Las contraseñas no coinciden", "error")
            elif len(nueva) < 6:
                flash("La nueva contraseña debe tener al menos 6 caracteres", "error")
            else:
                flash("Contraseña actualizada correctamente", "success")

        return redirect(url_for('perfil'))

    return render_template('perfil.html', usuario=usuario)

#catalogo 

@app.route('/catalogo')
def catalogo():
    return render_template('catalogo.html', productos=productos)




# Diccionario para opiniones (temporal en memoria)
opiniones = {
    1: [{"usuario": "Juan Pérez", "texto": "Excelente calidad, lo recomiendo."}],
    2: [{"usuario": "María López", "texto": "Muy buen producto y llegó rápido."}]
}

@app.route('/producto/<int:id>', methods=['GET','POST'])
def detalle_producto(id):
    producto = next((p for p in productos if p["id"] == id), None)
    if producto is None:
        return redirect(url_for('catalogo'))

    # Inicializar lista de opiniones si no existe
    if id not in opiniones:
        opiniones[id] = []

    # Guardar opinión nueva
    if request.method == 'POST':
        opinion_texto = request.form.get('opinion')
        if opinion_texto:
            opiniones[id].append({"usuario": "Anonimo", "texto": opinion_texto})
            flash("Opinión publicada", "success")
        return redirect(url_for('detalle_producto', id=id))

    # Pasar productos para recomendaciones y opiniones
    return render_template('detalle_producto.html', 
                           producto=producto, 
                           productos=productos,
                           opiniones=opiniones[id])



@app.route('/agregar_carrito/<int:id>')
def agregar_carrito(id):

    if 'carrito' not in session:
        session['carrito'] = []

    session['carrito'].append(id)
    session.modified = True

    flash("Producto agregado al carrito", "success")
    return redirect(url_for('catalogo'))




@app.route('/carrito')
def ver_carrito():
    carrito_ids = session.get('carrito', [])
    carrito_productos = []
    for p in productos:
        cantidad = carrito_ids.count(p["id"])
        if cantidad > 0:
            carrito_productos.append({**p, "cantidad": cantidad})
    total = sum(p["precio"] * p["cantidad"] for p in carrito_productos)
    return render_template('carrito.html', productos=carrito_productos, total=total)




@app.route('/eliminar_carrito/<int:id>')
def eliminar_carrito(id):

    if 'carrito' in session:
        session['carrito'] = [p for p in session['carrito'] if p != id]
        session.modified = True

    return redirect(url_for('ver_carrito'))


if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5001)
    
 

