from flask import Blueprint, render_template, redirect, url_for, session, request, flash, jsonify, Response
import json
import os
from security.api_client import fetch_data, post_data, patch_data, delete_data, fetch_raw, post_multipart, patch_multipart

views_bp = Blueprint('views', __name__)

# --- Auxiliares para Paqueterías (Phase 13 Persistence) ---
PAQUETERIAS_FILE = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'data', 'paqueterias.json')

def load_paqueterias():
    if not os.path.exists(PAQUETERIAS_FILE):
        return [{"nombre": "MACUIN Fleet Management"}]
    with open(PAQUETERIAS_FILE, 'r') as f:
        return json.load(f)

def save_paqueterias(data):
    os.makedirs(os.path.dirname(PAQUETERIAS_FILE), exist_ok=True)
    with open(PAQUETERIAS_FILE, 'w') as f:
        json.dump(data, f, indent=4)

@views_bp.route('/reporte/excel')
def reporte_excel_proxy():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    token = session.get('token')
    api_response = fetch_raw("/v1/reportes/ventas/xlsx", token)
    
    if api_response and api_response.status_code == 200:
        return Response(
            api_response.content,
            headers={
                "Content-Type": api_response.headers.get("Content-Type"),
                "Content-Disposition": "attachment; filename=Reporte_Ventas_MACUIN.xlsx"
            }
        )
    flash("Error al generar el reporte Excel desde el servidor.", "error")
    return redirect(url_for('views.ventas'))

@views_bp.route('/dashboard')
def dashboard():
    if 'usuario' not in session:
        return redirect(url_for('auth.login_personal_interno'))
    
    token = session.get('token')
    inventario_raw = fetch_data("/v1/autopartes/", token)
    if inventario_raw == "__TOKEN_INVALIDO__":
        session.clear()
        return redirect(url_for('auth.login_personal_interno'))
    inventario = inventario_raw.get('items', []) if isinstance(inventario_raw, dict) else []

    pedidos_raw = fetch_data("/v1/pedidos/", token)
    pedidos = pedidos_raw.get('items', []) if isinstance(pedidos_raw, dict) else (pedidos_raw if isinstance(pedidos_raw, list) else [])
    
    try:
        inv_data = fetch_data("/v1/autopartes/?limit=500", session.get('token'))
        inventario_global = inv_data.get('items', []) if isinstance(inv_data, dict) else []
    except:
        inventario_global = []

    return render_template('dashboard.html', 
                          inventarioData=inventario, 
                          logisticaData=pedidos,
                          inventarioGlobal=inventario_global)

@views_bp.route('/superadmin')
def superadmin():
    if 'usuario' not in session:
        return redirect(url_for('auth.login_personal_interno'))
    
    if session.get('rol') != 1:
        flash("Acceso denegado: Se requieren permisos de Superadmin.", "error")
        return redirect(url_for('views.dashboard'))

    usuarios_data = fetch_data("/v1/usuarios/", session.get('token'))
    usuarios_list = []
    
    usuarios_raw = []
    if isinstance(usuarios_data, list):
        usuarios_raw = usuarios_data
    elif isinstance(usuarios_data, dict):
        usuarios_raw = usuarios_data.get('items', [])
        
    for u in usuarios_raw:
        rol_nombre = "Usuario"
        if u.get('id_rol') == 1: rol_nombre = "Superadmin"
        elif u.get('id_rol') == 2: rol_nombre = "Trabajador"
        elif u.get('id_rol') == 3: rol_nombre = "Empresa"
        elif u.get('id_rol') == 4: rol_nombre = "Ventas"
        elif u.get('id_rol') == 5: rol_nombre = "Cliente"

        usuarios_list.append({
            "id_puro": u.get('id_usuario'),
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
    
    kpis = fetch_data("/v1/reportes/dashboard", session.get('token'))
    pedidos_data = fetch_data("/v1/pedidos/", session.get('token'))
    if pedidos_data == "__TOKEN_INVALIDO__":
        session.clear()
        return redirect(url_for('auth.login_personal_interno'))
    ventas_list = []
    
    pedidos_raw = []
    if isinstance(pedidos_data, list):
        pedidos_raw = pedidos_data
    elif isinstance(pedidos_data, dict):
        pedidos_raw = pedidos_data.get('items', [])
        
    categorias_map = {
        1: "Frenado",
        2: "Suspensión y Dirección",
        3: "Iluminación",
        4: "Motor y Transmisión",
        5: "Llantas",
        6: "Kits de Afinación",
        7: "Sensores y Eléctrico",
        8: "Aceites y Aditivos"
    }

    for p in pedidos_raw:
        estados_map = {1: "Recibido", 2: "Surtido", 3: "Enviado", 4: "Completado", 5: "Cancelado"}
        estatus = estados_map.get(p.get("id_estado"), "Procesando")
        
        u = p.get("usuario")
        nombre_cliente = f"{u.get('nombre')} {u.get('apellido_paterno')}" if u else f"Cliente #{p.get('id_usuario', 'N/A')}"
        
        # Procesar piezas reales (Phase 16 Fix)
        detalles = p.get("detalles", [])
        piezas_str = "Sin productos"
        if detalles:
            items_text = []
            for d in detalles:
                # El detalle puede traer nombre_producto o debemos buscarlo
                p_name = d.get('nombre_producto') or d.get('producto', {}).get('nombre_producto')
                if not p_name:
                    p_name = f"Pieza #{d.get('id_producto', 'SQL')}"
                # Obtener categoría
                p_cat_id = d.get('producto', {}).get('id_categoria', 1) if d.get('producto') else 1
                cat_name = categorias_map.get(p_cat_id, "General")
                
                items_text.append(f"#{d.get('id_producto') or '??'}")
            piezas_str = ", ".join(items_text)

        ventas_list.append({
            "id": f"V-{p.get('id_pedido', '')}",
            "id_puro": p.get('id_pedido'),
            "cliente": nombre_cliente,
            "piezas": piezas_str,
            "fecha": p.get('fecha_pedido', '').split('T')[0] if p.get('fecha_pedido') else 'N/A',
            "total": float(p.get('total', 0.0)),
            "estatus": estatus
        })

    response_autopartes = fetch_data("/v1/autopartes/?limit=2000", session.get('token'))
    inventario_global = []
    
    if isinstance(response_autopartes, dict):
        productos_all = response_autopartes.get('items', [])
    else:
        productos_all = response_autopartes

    for p in productos_all:
        id_cat = p.get('id_categoria', 1)
        cat_name = categorias_map.get(id_cat, "General")
        inventario_global.append({
            "id_puro": p.get('id_producto'),
            "id": f"SKU-{p.get('id_producto')}",
            "pieza": f"[{cat_name}] {p.get('nombre_producto')}",
            "precio": float(p.get('precio', 0.0))
        })

    status_counts = {1: 0, 2: 0, 3: 0, 4: 0, 5: 0}
    total_acumulado = 0.0
    for p in pedidos_raw:
        id_est = p.get("id_estado", 1)
        status_counts[id_est] = status_counts.get(id_est, 0) + 1
        total_acumulado += float(p.get("total", 0.0))

    top_v = fetch_data("/v1/reportes/top-productos", session.get('token'))
    tendencia = fetch_data("/v1/reportes/ventas/tendencia", session.get('token'))

    chart_data = {
        "status_labels": ["Recibido", "Surtido", "Enviado", "Completado", "Cancelado"],
        "status_values": [status_counts[1], status_counts[2], status_counts[3], status_counts[4], status_counts[5]],
        "total_revenue": total_acumulado,
        "top_labels": [item.get('producto') for item in top_v] if isinstance(top_v, list) else [],
        "top_values": [item.get('cantidad') for item in top_v] if isinstance(top_v, list) else [],
        "trend_labels": [item.get('fecha') for item in tendencia] if isinstance(tendencia, list) else [],
        "trend_revenue": [item.get('ingreso') for item in tendencia] if isinstance(tendencia, list) else [],
        "trend_orders": [item.get('cantidad') for item in tendencia] if isinstance(tendencia, list) else []
    }

    return render_template('ventas.html', ventasData=ventas_list, chartData=chart_data, kpis=kpis, inventarioGlobal=inventario_global, paqueterias=load_paqueterias())

@views_bp.route('/ventas/detalle/<int:id_pedido>')
def obtener_detalle_venta(id_pedido):
    if 'usuario' not in session: return jsonify({"error": "No autenticado"}), 401
    detalle = fetch_data(f"/v1/pedidos/{id_pedido}", session.get('token'))
    if detalle:
        return jsonify(detalle)
    return jsonify({"error": "No se encontró el detalle"}), 404

@views_bp.route('/ventas/confirmar/<int:id_pedido>', methods=['POST'])
def confirmar_pedido(id_pedido):
    if 'usuario' not in session: return jsonify({"error": "No autenticado"}), 401
    response = patch_data(f"/v1/pedidos/{id_pedido}/estado", {"id_estado": 2}, session.get('token'))
    if response and response.status_code == 200:
        return jsonify({"status": "success", "message": "Pedido confirmado. Notificación enviada al cliente."})
    return jsonify({"status": "error", "message": "Error al confirmar en la API"}), 400

@views_bp.route('/ventas/eliminar/<int:id_pedido>', methods=['POST'])
def eliminar_venta(id_pedido):
    if 'usuario' not in session: return jsonify({"error": "No autenticado"}), 401
    response = delete_data(f"/v1/pedidos/{id_pedido}", session.get('token'))
    if response and response.status_code in [200, 204]:
        return jsonify({"status": "success"})
    return jsonify({"status": "error", "message": "Error al eliminar en la API"}), 400

@views_bp.route('/ventas/editar/<int:id_pedido>', methods=['POST'])
def editar_pedido_completo(id_pedido):
    if 'usuario' not in session: return jsonify({"error": "No autenticado"}), 401
    
    datos_envio = {}
    mapping = {
        "direccion": "direccion",
        "ciudad": "ciudad",
        "codigo_postal": "codigo_postal",
        "telefono_contacto": "telefono_contacto",
        "notas": "notas",
        "paqueteria": "paqueteria"
    }
    
    for form_key, api_key in mapping.items():
        val = request.form.get(form_key)
        if val:
            datos_envio[api_key] = val
            
    if datos_envio:
        res_envio = patch_data(f"/v1/pedidos/{id_pedido}/envio", datos_envio, session.get('token'))
        if not res_envio or res_envio.status_code != 200:
            return jsonify({"status": "error", "message": "No se pudo actualizar la información de envío en el servidor."}), 400
    
    id_estado_str = request.form.get('id_estado')
    if id_estado_str:
        id_estado = int(id_estado_str)
        response = patch_data(f"/v1/pedidos/{id_pedido}/estado", {"id_estado": id_estado}, session.get('token'))
        if not response or response.status_code != 200:
            return jsonify({"status": "error", "message": "Error al actualizar el estado del pedido."}), 400
    
    return jsonify({"status": "success", "message": "Pedido actualizado correctamente."})
    
@views_bp.route('/logistica/asignar_paqueteria/<int:id_pedido>', methods=['POST'])
def asignar_paqueteria_fast(id_pedido):
    if 'usuario' not in session: return jsonify({"status": "error", "message": "No autenticado"}), 401
    
    data = request.get_json()
    paqueteria = data.get('paqueteria') if data else None
    
    if not paqueteria:
        return jsonify({"status": "error", "message": "Paquetería no proporcionada"}), 400
        
    res = patch_data(f"/v1/pedidos/{id_pedido}/envio", {"paqueteria": paqueteria}, session.get('token'))
    
    if res and res.status_code == 200:
        return jsonify({"status": "success", "message": "Paquetería asignada correctamente."})
        
    return jsonify({"status": "error", "message": "Error al sincronizar con el servidor central."}), 400

@views_bp.route('/logistica/paqueteria', methods=['POST'])
def registrar_paqueteria():
    if 'usuario' not in session: return jsonify({"status": "error", "message": "No autorizado"}), 401
    data = request.get_json()
    nombre = data.get('nombre')
    if not nombre: return jsonify({"status": "error", "message": "Nombre requerido"}), 400
    paqs = load_paqueterias()
    if any(p['nombre'].lower() == nombre.lower() for p in paqs):
        return jsonify({"status": "error", "message": "Esta paquetería ya existe"}), 400
    paqs.append({"nombre": nombre, "web": data.get('web', ''), "tel": data.get('tel', '')})
    save_paqueterias(paqs)
    return jsonify({"status": "success", "message": "Paquetería registrada correctamente"})

@views_bp.route('/logistica/paqueterias')
def obtener_paqueterias():
    return jsonify(load_paqueterias())

@views_bp.route('/almacen')
def almacen():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    page = request.args.get('page', 1, type=int)
    limit = 200
    
    response_data = fetch_data(f"/v1/autopartes/?page={page}&limit={limit}", session.get('token'))
    
    if not isinstance(response_data, dict) or 'items' not in response_data:
        flash("Error al obtener datos del inventario.", "error")
        return render_template('almacen.html', inventarioData=[], kpis={})

    productos_data = response_data['items']
    total = response_data['total']
    total_pages = response_data['total_pages']
    
    almacen_list = []
    categorias_map = {
        1: "Frenado",
        2: "Suspensión y Dirección",
        3: "Iluminación",
        4: "Motor y Transmisión",
        5: "Llantas",
        6: "Kits de Afinación",
        7: "Sensores y Eléctrico",
        8: "Aceites y Aditivos"
    }
    
    for p in productos_data:
        id_cat = p.get('id_categoria', 1)
        almacen_list.append({
            "id": f"SKU-{p.get('id_producto', '')}",
            "id_puro": p.get('id_producto'),
            "pieza": p.get('nombre_producto', 'Pieza desconocida'),
            "categoria": categorias_map.get(id_cat, f"Cat-{id_cat}"),
            "pasillo": "A-1", 
            "stock": p.get('stock', 0),
            "min": p.get('stock_minimo', 5),
            "precio": float(p.get('precio', 0.0)),
            "marca": p.get('marca', 'N/A'),
            "modelo": p.get('modelo', 'N/A'),
            "descripcion": p.get('descripcion', ''),
            "imagen_url": p.get('imagen_url') if p.get('imagen_url') else None
        })
        
    try:
        kpis = fetch_data("/v1/reportes/dashboard", session.get('token'))
    except:
        kpis = {}

    start_page = max(1, page - 2)
    end_page = min(total_pages, page + 2)
    page_range = range(start_page, end_page + 1)
    
    return render_template('almacen.html', 
                          inventarioData=almacen_list, 
                          kpis=kpis, 
                          total=total, 
                          currentPage=page, 
                          totalPages=total_pages,
                          pageRange=page_range)

@views_bp.route('/almacen/ajustar/<int:id_producto>', methods=['POST'])
@views_bp.route('/almacen/ajustar/<int:id_producto>/<string:cantidad>', methods=['POST'])
def ajustar_stock(id_producto, cantidad=None):
    if 'usuario' not in session: return {"status": "error", "message": "No autorizado"}, 401
    if cantidad is None:
        cantidad = request.form.get('stock')
    try:
        cant_int = int(cantidad)
    except:
        return {"status": "error", "message": "Cantidad inválida"}, 400
        
    response = post_data(f"/v1/autopartes/{id_producto}/ajustar_stock?cantidad={cant_int}", {}, session.get('token'))
    if response and response.status_code == 200:
        return {"status": "success", "data": response.json(), "message": "Stock actualizado"}
    return {"status": "error", "message": "Error al comunicar con la API"}, 500

@views_bp.route('/almacen/editar/<int:id_producto>', methods=['POST'])
def editar_pieza_api(id_producto):
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    form_data = {
        "nombre_producto": request.form.get('nombre_producto'),
        "precio": request.form.get('precio', '0.0'),
        "id_categoria": request.form.get('id_categoria', '1'),
        "descripcion": request.form.get('descripcion', ''),
        "marca": request.form.get('marca', ''),
        "modelo": request.form.get('modelo', ''),
        "compatibilidad": request.form.get('descripcion', ''),
        "garantia": "1 año"
    }
    
    files = {}
    if 'imagen' in request.files:
        file = request.files['imagen']
        if file.filename != '':
            files['imagen'] = (file.filename, file.read(), file.content_type)
    
    response = patch_multipart(f"/v1/autopartes/{id_producto}", form_data, files, session.get('token'))
    
    if response and response.status_code in [200, 201]:
        return {"status": "success"}, 200
    return {"status": "error"}, 500

@views_bp.route('/almacen/eliminar/<int:id_producto>', methods=['POST'])
def eliminar_pieza_api(id_producto):
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    response = delete_data(f"/v1/autopartes/{id_producto}", session.get('token'))
    if response and response.status_code in [200, 204]:
        return {"status": "success"}, 200
    return {"status": "error"}, 500

@views_bp.route('/almacen/nuevo', methods=['POST'])
def nueva_pieza():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    
    form_data = {
        "nombre_producto": request.form.get('nombre_producto'),
        "precio": request.form.get('precio', '0.0'),
        "id_categoria": request.form.get('id_categoria', '1'),
        "descripcion": request.form.get('descripcion', ''),
        "marca": request.form.get('marca', ''),
        "modelo": request.form.get('modelo', ''),
        "cantidad_inicial": request.form.get('stock', '0'),
        "compatibilidad": request.form.get('descripcion', ''),
        "garantia": "1 año"
    }
    files = {}
    if 'imagen' in request.files:
        file = request.files['imagen']
        if file.filename != '':
            files['imagen'] = (file.filename, file.read(), file.content_type)
    
    response = post_multipart("/v1/autopartes/", form_data, files, session.get('token'))
    
    if response and response.status_code == 201:
        return {"status": "success", "message": "Producto registrado correctamente"}, 201
    elif response:
        try:
            detail = response.json().get('detail', 'Error desconocido en el servidor')
        except:
            detail = f"Error del servidor (Status {response.status_code})"
        return {"status": "error", "message": detail}, response.status_code
    return {"status": "error", "message": "No se pudo conectar con el API"}, 500

@views_bp.route('/logistica')
def logistica():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    kpis = fetch_data("/v1/reportes/dashboard", session.get('token'))
    pedidos_data = fetch_data("/v1/pedidos/", session.get('token'))
    logistica_list = []
    
    pedidos_raw = []
    if isinstance(pedidos_data, list):
        pedidos_raw = pedidos_data
    elif isinstance(pedidos_data, dict):
        pedidos_raw = pedidos_data.get('items', [])

    for p in pedidos_raw:
        estados_map = {1: "Recibido", 2: "Surtido", 3: "Enviado", 4: "Completado", 5: "Cancelado"}
        u = p.get("usuario")
        nombre_cliente = f"{u.get('nombre')} {u.get('apellido_paterno')}" if u else f"Cliente #{p.get('id_usuario', 'N/A')}"
        env = p.get("envio")
        paqueteria_txt = env.get("paqueteria") if env else "MACUIN Fleet Management"

        logistica_list.append({
            "guia": f"TRK-{p.get('id_pedido', '')}",
            "id_puro": p.get('id_pedido'), 
            "id_pedido": p.get('id_pedido'),
            "id_estado": p.get('id_estado'),
            "destino": nombre_cliente,
            "courier": paqueteria_txt,
            "estado": estados_map.get(p.get("id_estado"), "Pendiente"),
            "fecha": p.get('fecha_pedido', '').split('T')[0] if p.get('fecha_pedido') else "Pendiente"
        })
    op_kpis = {
        "recibido": 0,
        "surtido": 0,
        "transito": 0,
        "completos": 0,
        "cancelados": 0,
        "total_entregados": 0,
        "total_cancelados": 0
    }
    
    for p in pedidos_raw:
        st = p.get("id_estado")
        if st == 1: op_kpis["recibido"] += 1
        elif st == 2: op_kpis["surtido"] += 1
        elif st == 3: op_kpis["transito"] += 1
        elif st == 4: 
            op_kpis["completos"] += 1
            op_kpis["total_entregados"] += 1
        elif st == 5: 
            op_kpis["cancelados"] += 1
            op_kpis["total_cancelados"] += 1

    paqueterias = load_paqueterias()
    
    # Carga de Inventario Global para los selectores (Phase 13 Sync - Fixed Mapping)
    try:
        response_autopartes = fetch_data("/v1/autopartes/?limit=2000", session.get('token'))
        inventario_global = []
        if isinstance(response_autopartes, dict):
            productos_all = response_autopartes.get('items', [])
            for p in productos_all:
                inventario_global.append({
                    "id_puro": p.get('id_producto'),
                    "id": f"SKU-{p.get('id_producto')}",
                    "pieza": p.get('nombre_producto'),
                    "precio": float(p.get('precio', 0.0))
                })
    except Exception as e:
        print(f"Error cargando inventario en logistica: {e}")
        inventario_global = []

    return render_template('logistica.html', 
                          logisticaData=logistica_list, 
                          kpis=op_kpis, 
                          paqueterias=paqueterias, 
                          inventarioGlobal=inventario_global)

@views_bp.route('/ventas/nueva', methods=['POST'])
def nueva_venta():
    if 'usuario' not in session: return {"status": "error", "message": "No autorizado"}, 401

    try:
        data = request.get_json()
        items = data.get('items', [])

        if not items:
            return {"status": "error", "message": "No hay productos en la venta."}, 400

        detalles = []
        for item in items:
            precio = float(item.get('precio', 0))
            if precio <= 0:
                return {"status": "error", "message": f"Precio inválido para {item.get('nombre', 'producto')}."}, 400
            detalles.append({
                "id_producto": int(item['id_producto']),
                "cantidad": int(item.get('cantidad', 1)),
                "precio_unitario": precio
            })

        telefono = str(data.get('telefono', '0000000000'))[:20]
        cliente_nombre = data.get('cliente_nombre', 'Cliente de Mostrador')
        original_notas = data.get('notas', '')
        notas_finales = f"CLIENTE: {cliente_nombre} | {original_notas or 'Venta de Mostrador'}"

        payload = {
            "detalles": detalles,
            "direccion": data.get('direccion', 'Venta de Mostrador'),
            "ciudad": data.get('ciudad', 'Local'),
            "codigo_postal": data.get('codigo_postal', '00000'),
            "telefono_contacto": telefono,
            "notas": notas_finales,
            "paqueteria": data.get('paqueteria', 'MACUIN Fleet Management')
        }

        response = post_data("/v1/pedidos/", payload, session.get('token'))
        if response and response.status_code == 201:
            return {"status": "success", "data": response.json()}

        msg = "Error al crear pedido en API"
        if response:
            try: msg = response.json().get('detail', msg)
            except: pass
        return {"status": "error", "message": msg}, 500

    except Exception as e:
        return {"status": "error", "message": str(e)}, 500

@views_bp.route('/usuarios')
def usuarios():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    usuarios_data = fetch_data("/v1/usuarios/", session.get('token'))
    usuarios_list = []
    
    usuarios_raw = []
    if isinstance(usuarios_data, list):
        usuarios_raw = usuarios_data
    elif isinstance(usuarios_data, dict):
        usuarios_raw = usuarios_data.get('items', [])
        
    for u in usuarios_raw:
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
    
    # Recolectar datos del formulario
    nombre = request.form.get('nombre')
    apellido_paterno = request.form.get('apellido_paterno')
    apellido_materno = request.form.get('apellido_materno')
    telefono = request.form.get('telefono')
    correo = request.form.get('correo')
    password = request.form.get('password')
    confirm_password = request.form.get('confirm_password')
    id_rol = request.form.get('rol')

    # Validaciones básicas
    if password != confirm_password:
        flash("Las contraseñas no coinciden.", "error")
        return redirect(url_for('views.superadmin'))

    usuario_data = {
        "nombre": nombre,
        "apellido_paterno": apellido_paterno,
        "apellido_materno": apellido_materno,
        "correo": correo,
        "password": password,
        "telefono": telefono,
        "id_rol": int(id_rol)
    }

    # Llamada al API
    response = post_data("/v1/usuarios/", usuario_data, session.get('token'))
    
    if response and response.status_code == 201:
        flash(f"Usuario {nombre} registrado exitosamente.", "success")
    else:
        error_msg = "Error al crear usuario."
        if response and response.status_code == 400:
            error_msg = "El correo ya está registrado o los datos son inválidos."
        flash(error_msg, "error")
        
    return redirect(url_for('views.superadmin'))

@views_bp.route('/superadmin/toggle/<int:id_usuario>', methods=['POST'])
def toggle_usuario(id_usuario):
    if 'usuario' not in session:
        return redirect(url_for('auth.login_personal_interno'))
    if session.get('rol') != 1:
        flash("Sin permisos de Superadmin.", "error")
        return redirect(url_for('views.superadmin'))
    response = post_data(f"/v1/usuarios/{id_usuario}/toggle-active", {}, session.get('token'))
    if response and response.status_code == 200:
        flash("Estado del usuario actualizado correctamente.", "success")
    else:
        flash("Error al cambiar el estado del usuario.", "error")
    return redirect(url_for('views.superadmin'))

@views_bp.route('/superadmin/eliminar/<int:id_usuario>', methods=['POST'])
def eliminar_usuario(id_usuario):
    if 'usuario' not in session:
        return jsonify({"status": "error", "message": "No autenticado"}), 401
    if session.get('rol') != 1:
        return jsonify({"status": "error", "message": "Sin permisos"}), 403
    response = delete_data(f"/v1/usuarios/{id_usuario}", session.get('token'))
    if response and response.status_code in [200, 204]:
        return jsonify({"status": "success"})
    return jsonify({"status": "error", "message": "Error al eliminar usuario"}), 400

@views_bp.route('/superadmin/editar/<int:id_usuario>', methods=['POST'])
def editar_usuario(id_usuario):
    if 'usuario' not in session:
        return jsonify({"status": "error", "message": "No autenticado"}), 401
    usuario_data = {}
    nombre = request.form.get('nombre', '').strip()
    apellido_paterno = request.form.get('apellido_paterno', '').strip()
    id_rol = request.form.get('id_rol')
    if nombre:
        usuario_data['nombre'] = nombre
    if apellido_paterno:
        usuario_data['apellido_paterno'] = apellido_paterno
    if id_rol:
        usuario_data['id_rol'] = int(id_rol)
    response = patch_data(f"/v1/usuarios/{id_usuario}", usuario_data, session.get('token'))
    if response and response.status_code == 200:
        return jsonify({"status": "success", "data": response.json()})
    return jsonify({"status": "error", "message": "Error al actualizar usuario"}), 400

@views_bp.route('/superadmin/empresa', methods=['POST'])
def nueva_empresa():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    usuario_data = {
        "nombre": request.form.get('nombre'),
        "apellido_paterno": "Empresa",
        "apellido_materno": "S.A.",
        "correo": request.form.get('correo'),
        "password": request.form.get('password'),
        "id_rol": 3, # Rol de Empresa
        "telefono": "0000000000"
    }
    response = post_data("/v1/usuarios/", usuario_data, session.get('token'))
    if response and response.status_code == 201:
        flash("Empresa registrada exitosamente.", "success")
    else:
        flash("Error al registrar empresa.", "error")
    return redirect(url_for('views.superadmin'))

@views_bp.route('/perfil/actualizar', methods=['POST'])
def perfil_actualizar():
    if 'usuario' not in session: return redirect(url_for('auth.login_personal_interno'))
    id_usuario = session.get('id_usuario')
    if not id_usuario:
        flash("Error: No se pudo identificar al usuario.", "error")
        return redirect(url_for('views.usuarios'))
    
    nombre_completo = request.form.get('nombre', '')
    nombres = nombre_completo.split()
    nombre = nombres[0] if len(nombres) > 0 else "N/A"
    apellido = nombres[1] if len(nombres) > 1 else "N/A"
    
    usuario_data = {
        "nombre": nombre,
        "apellido_paterno": apellido
    }
    
    response = patch_data(f"/v1/usuarios/{id_usuario}", usuario_data, session.get('token'))
    if response and response.status_code == 200:
        session['nombre'] = nombre
        flash("Perfil actualizado correctamente.", "success")
    else:
        flash("Error al actualizar el perfil.", "error")
    
    return redirect(url_for('views.usuarios'))
