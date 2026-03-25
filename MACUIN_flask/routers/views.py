from flask import Blueprint, render_template, redirect, url_for, session, request, flash
from security.api_client import fetch_data, post_data, patch_data

views_bp = Blueprint('views', __name__)

@views_bp.route('/dashboard')
def dashboard():
    if 'usuario' not in session:
        return redirect(url_for('auth.login_personal_interno'))
    return render_template('dashboard.html')

@views_bp.route('/superadmin')
def superadmin():
    if 'usuario' not in session:
        return redirect(url_for('auth.login_personal_interno'))
    
    # Seguridad extra: Solo si es rol 1 (Superadmin)
    if session.get('rol') != 1:
        flash("Acceso denegado: Se requieren permisos de Superadmin.", "error")
        return redirect(url_for('views.dashboard'))

    usuarios_data = fetch_data("/v1/usuarios/", session.get('token'))
    usuarios_list = []
    
    for u in usuarios_data:
        rol_nombre = "Usuario"
        if u.get('id_rol') == 1: rol_nombre = "Superadmin"
        elif u.get('id_rol') == 2: rol_nombre = "Trabajador"
        elif u.get('id_rol') == 3: rol_nombre = "Empresa"
        elif u.get('id_rol') == 4: rol_nombre = "Ventas"

        usuarios_list.append({
            "id": f"USR-{u.get('id_usuario', '')}",
            "nombre": f"{u.get('nombre', '')} {u.get('apellido_paterno', '')}",
            "origen": "MACUIN Central" if u.get('id_rol') != 3 else "Empresa Asociada",
            "rol": rol_nombre,
            "estado": "Activo" if u.get('activo') else "Suspendido"
        })

    return render_template('superadmin.html', usuariosGlobales=usuarios_list)

@views_bp.route('/ventas')
def ventas():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    # 1. Obtener KPIs para el dashboard
    kpis = fetch_data("/v1/reportes/dashboard", session.get('token'))
    
    # 2. Obtener lista de ventas (pedidos)
    pedidos_data = fetch_data("/v1/pedidos/", session.get('token'))
    ventas_list = []
    
    for p in pedidos_data:
        # Mapeo de estados
        estados_map = {1: "Recibido", 2: "Surtido", 3: "Enviado", 4: "Completado", 5: "Cancelado"}
        estatus = estados_map.get(p.get("id_estado"), "Procesando")
        
        ventas_list.append({
            "id": f"V-{p.get('id_pedido', '')}",
            "id_puro": p.get('id_pedido'),
            "cliente": f"Cliente #{p.get('id_usuario', 'N/A')}",
            "piezas": "Piezas del Pedido",
            "fecha": p.get('fecha_pedido', '').split('T')[0],
            "total": float(p.get('total', 0.0)),
            "estatus": estatus
        })
    # 3. Obtener inventario para el select del modal
    productos_all = fetch_data("/v1/autopartes/", session.get('token'))
    inventario_global = []
    for p in productos_all:
        inventario_global.append({
            "id_puro": p.get('id_producto'),
            "id": f"SKU-{p.get('id_producto')}",
            "pieza": p.get('nombre_producto')
        })

    return render_template('ventas.html', ventasData=ventas_list, kpis=kpis, inventarioGlobal=inventario_global)

@views_bp.route('/ventas/nueva', methods=['POST'])
def nueva_venta():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    cliente = request.form.get('cliente', 'anonimo')
    id_producto = request.form.get('id_producto')
    cantidad = request.form.get('cantidad', '1')
    total = request.form.get('total', '0') # Total manual o calculado?
    
    if not id_producto:
        flash("Error: Debe seleccionar un producto.", "error")
        return redirect(url_for('views.ventas'))

    pedido_data = {
        "direccion": "Mostrador",
        "ciudad": "Local",
        "codigo_postal": "00000",
        "telefono_contacto": "0000000000",
        "notas": f"Cliente: {cliente}",
        "detalles": [
            {
                "id_producto": int(id_producto),
                "cantidad": int(cantidad),
                "precio_unitario": float(total) / int(cantidad) if int(cantidad) > 0 else 0.0
            }
        ]
    }
    
    response = post_data("/v1/pedidos/", pedido_data, session.get('token'))
    if response and response.status_code == 201:
        flash("Venta registrada exitosamente. El stock ha sido actualizado.", "success")
    else:
        error_detail = response.json().get('detail', 'Error desconocido') if response else "Sin respuesta del servidor"
        flash(f"Error al registrar la venta: {error_detail}", "error")
        
    return redirect(url_for('views.ventas'))

@views_bp.route('/almacen')
def almacen():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    productos_data = fetch_data("/v1/autopartes/", session.get('token'))
    almacen_list = []
    
    # Mapeo de categorías para mostrar nombres legibles
    categorias_map = {
        1: "Motor / Mecánica",
        2: "Sistema Eléctrico",
        3: "Frenado y Suspensión",
        4: "Limpieza y Fluidos"
    }
    
    for p in productos_data:
        id_cat = p.get('id_categoria', 1)
        almacen_list.append({
            "id": f"SKU-{p.get('id_producto', '')}",
            "id_puro": p.get('id_producto'),
            "pieza": p.get('nombre_producto', 'Pieza desconocida'),
            "categoria": categorias_map.get(id_cat, f"Cat-{id_cat}"),
            "pasillo": "A-1", # Ubicación simulada o fija por ahora
            "stock": p.get('stock', 0),
            "min": p.get('stock_minimo', 5),
            "precio": float(p.get('precio', 0.0))
        })
    # KPI Dashboard Inventario
    kpis = fetch_data("/v1/reportes/dashboard", session.get('token'))
    
    return render_template('almacen.html', inventarioData=almacen_list, kpis=kpis)

@views_bp.route('/almacen/ajustar/<int:id_producto>/<int:cantidad>', methods=['POST'])
def ajustar_stock(id_producto, cantidad):
    if 'usuario' not in session: return "No autorizado", 401
    
    # Llamar a la API para ajustar el stock
    response = post_data(f"/v1/autopartes/{id_producto}/ajustar_stock?cantidad={cantidad}", {}, session.get('token'))
    if response and response.status_code == 200:
        return {"status": "success", "data": response.json()}
    return {"status": "error"}, 500

@views_bp.route('/almacen/editar/<int:id_producto>', methods=['POST'])
def editar_pieza_api(id_producto):
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    nuevo_precio = request.form.get('precio')
    
    update_data = {
        "precio": float(nuevo_precio) if nuevo_precio else 0.0
    }
    
    response = patch_data(f"/v1/autopartes/{id_producto}", update_data, session.get('token'))
    if response and response.status_code == 200:
        flash("Producto actualizado exitosamente.", "success")
    else:
        flash("Error al actualizar el producto.", "error")
        
    return redirect(url_for('views.almacen'))

@views_bp.route('/almacen/nueva', methods=['POST'])
def nueva_pieza():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    # 1. Obtener datos del formulario
    form_data = {
        "nombre_producto": request.form.get('nombre'),
        "precio": request.form.get('precio', '0.0'),
        "id_categoria": request.form.get('categoria', '1'),
        "descripcion": request.form.get('descripcion', ''),
        "marca": request.form.get('marca', ''),
        "modelo": request.form.get('modelo', ''),
        "cantidad_inicial": request.form.get('stock', '0'),
        "compatibilidad": request.form.get('descripcion', ''), # Reusar descripcion si no hay mas
        "garantia": "1 año" # Valor base
    }
    
    # 2. Manejar archivo de imagen
    files = {}
    if 'imagen' in request.files:
        file = request.files['imagen']
        if file.filename != '':
            files['imagen'] = (file.filename, file.read(), file.content_type)
    
    # 3. Llamada Multipart a la API
    from security.api_client import post_multipart
    response = post_multipart("/v1/autopartes/", form_data, files, session.get('token'))
    
    if response and response.status_code == 201:
        flash("Pieza y stock registrados exitosamente.", "success")
    else:
        flash("Error al registrar la pieza completa en la API.", "error")
        
    return redirect(url_for('views.almacen'))

@views_bp.route('/logistica')
def logistica():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    # 1. KPIs
    kpis = fetch_data("/v1/reportes/dashboard", session.get('token'))
    
    # 2. Envíos
    envios_data = fetch_data("/v1/pedidos/envios/todos", session.get('token'))
    logistica_list = []
    
    for e in envios_data:
        logistica_list.append({
            "guia": f"TRK-{e.get('id_envio', '')}",
            "id_puro": e.get('id_envio'),
            "destino": e.get('ciudad', 'Desconocido'),
            "courier": "MACUIN Interno" if "static" in e.get('direccion', '').lower() else "Paquetería",
            "estado": e.get('estado_envio', 'pendiente'),
            "fecha": e.get('fecha_envio', 'Pendiente').split('T')[0] if e.get('fecha_envio') else "N/A"
        })
        
    return render_template('logistica.html', logisticaData=logistica_list, kpis=kpis)

@views_bp.route('/usuarios')
def usuarios():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    usuarios_data = fetch_data("/v1/usuarios/", session.get('token'))
    usuarios_list = []
    
    for u in usuarios_data:
        usuarios_list.append({
            "id": f"USR-{u.get('id_usuario', '')}",
            "nombre": f"{u.get('nombre', '')} {u.get('apellido_paterno', '')}",
            "origen": u.get('correo', 'N/A'),
            "rol": "Admin" if u.get('id_rol') == 2 else "Usuario",
            "estado": "Activo"
        })
    return render_template('usuarios.html', usuariosGlobales=usuarios_list)

@views_bp.route('/superadmin/usuario', methods=['POST'])
def nuevo_usuario():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    nombre_completo = request.form.get('nombre', '')
    nombres = nombre_completo.split()
    nombre = nombres[0] if len(nombres) > 0 else "Admin"
    apellido = nombres[1] if len(nombres) > 1 else "Macuin"
    
    correo = request.form.get('correo', 'admin@macuin.com')
    rol = request.form.get('rol', '2') # Por defecto Trabajador (2)
    
    # Seguridad: No permitir crear Superadmins (1) desde este formulario
    if str(rol) == '1':
        rol = '2'
    
    password = request.form.get('password', 'password123')
    
    usuario_data = {
        "nombre": nombre,
        "apellido_paterno": apellido,
        "apellido_materno": apellido,
        "correo": correo,
        "password": password,
        "id_rol": int(rol),
        "telefono": "5555555555"
    }
    
    response = post_data("/v1/usuarios/", usuario_data, session.get('token'))
    if response and response.status_code == 201:
        flash("Usuario global registrado con éxito.", "success")
    else:
        flash("Error al intentar registrar usuario global.", "error")
        
    return redirect(url_for('views.superadmin'))

@views_bp.route('/superadmin/empresa', methods=['POST'])
def nueva_empresa():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    nombre = request.form.get('nombre', 'Empresa Nueva')
    correo = request.form.get('correo', f"contacto@{nombre.lower().replace(' ', '')}.com")
    
    password = request.form.get('password', 'password123')
    
    usuario_data = {
        "nombre": nombre,
        "apellido_paterno": "Empresa",
        "apellido_materno": "S.A.",
        "correo": correo,
        "password": password,
        "id_rol": 3, # Rol Empresa
        "telefono": "0000000000"
    }
    
    response = post_data("/v1/usuarios/", usuario_data, session.get('token'))
    if response and response.status_code == 201:
        flash(f"Empresa '{nombre}' registrada exitosamente como usuario.", "success")
    else:
        flash("Error al registrar la empresa en la base de datos.", "error")
        
    return redirect(url_for('views.superadmin'))

@views_bp.route('/perfil/actualizar', methods=['POST'])
def perfil_actualizar():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    id_usuario = session.get('id_usuario')
    if not id_usuario:
        flash("Error: No se pudo identificar al usuario en la sesión.", "error")
        return redirect(url_for('views.usuarios'))
        
    nombre = request.form.get('nombre')
    apellido_p = request.form.get('apellido_paterno')
    apellido_m = request.form.get('apellido_materno')
    
    update_data = {}
    if nombre: update_data["nombre"] = nombre
    if apellido_p: update_data["apellido_paterno"] = apellido_p
    if apellido_m: update_data["apellido_materno"] = apellido_m
    
    # Llamada a la API (PATCH)
    response = patch_data(f"/v1/usuarios/{id_usuario}", update_data, session.get('token'))
    
    if response and response.status_code == 200:
        session['nombre'] = nombre # Actualizar nombre en sesión para el header
        flash("Perfil actualizado con éxito.", "success")
    else:
        flash("Error al actualizar el perfil en el servidor.", "error")
        
    return redirect(url_for('views.usuarios'))
