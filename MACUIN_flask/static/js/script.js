// ================================================================
// 1. UTILIDADES GENERALES
// ================================================================

// Control de visibilidad de contraseñas
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId || 'contrasena');
    const button = passwordInput.parentElement.querySelector('.toggle-password');
    const eyeIcon = button.querySelector('.eye-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    }
}

// Obtener fecha local (Evita que el dinero salga en $0.00 por desfase de horas UTC)
function getFechaLocal() {
    const ahora = new Date();
    const año = ahora.getFullYear();
    const mes = String(ahora.getMonth() + 1).padStart(2, '0');
    const dia = String(ahora.getDate()).padStart(2, '0');
    return `${año}-${mes}-${dia}`;
}

// ================================================================
// 2. MÓDULO DE VENTAS (Enterprise Dinámico)
// ================================================================

// Datos iniciales simulados
let modoEdicion = false;
let ventasData = window.ventasData || [];
let listaItemsVenta = [];

// Dibuja la tabla de ventas en el HTML
function renderTabla() {
    const isDark = document.body.classList.contains('theme-dark');
    const textColor = isDark ? '#ffffff' : '#000000';
    const borderColor = isDark ? '#333' : '#eee';
    const tbody = document.getElementById("tabla-ventas-body");
    if (!tbody) return;

    tbody.innerHTML = ""; 
    
    ventasData.forEach(venta => {
        let badgeClass = venta.estatus === "Completado" ? "badge-success" : 
                         venta.estatus === "Procesando" || venta.estatus === "Recibido" ? "badge-warning" : "badge-danger";
                         
        tbody.innerHTML += `
            <tr style="border-bottom: 1px solid ${borderColor};">
                <td style="color: ${textColor}; font-weight: 800;">#${venta.id}</td>
                <td style="color: ${textColor};">${venta.cliente}</td>
                <td style="color: ${textColor}; font-size: 0.85rem;">${venta.piezas}</td>
                <td style="color: ${textColor};">${venta.fecha}</td>
                <td style="color: ${textColor}; font-weight: 800; color: #1e293b;">$${parseFloat(venta.total).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td><span class="badge ${badgeClass}" style="border-radius: 4px; padding: 4px 10px; font-weight: 700;">${venta.estatus}</span></td>
                <td class="row-actions">
                    <button class="btn-action-sm btn-view-sm" onclick="verDetalleOrden('${venta.id_puro}')" title="Ver Detalle" style="color: #64748b; background: #f1f5f9; border: 1px solid #e2e8f0; padding: 8px; border-radius: 8px; cursor: pointer; transition: all 0.2s;"><i class="fas fa-eye"></i></button>
                    <button class="btn-action-sm btn-edit-sm" onclick="abrirEditarPedido('${venta.id_puro}')" title="Editar" style="color: #64748b; background: #f1f5f9; border: 1px solid #e2e8f0; padding: 8px; border-radius: 8px; cursor: pointer; margin-left:5px; transition: all 0.2s;"><i class="fas fa-edit"></i></button>
                    <button class="btn-action-sm btn-delete-sm" onclick="eliminarVenta('${venta.id_puro}')" title="Eliminar" style="color: #e57373; background: #fef2f2; border: 1px solid #fee2e2; padding: 8px; border-radius: 8px; cursor: pointer; margin-left:5px; transition: all 0.2s;"><i class="fas fa-trash-alt"></i></button>
                </td>
            </tr>
        `;
    });
    
    actualizarKPIs(); 
}

// Calcula los totales de las tarjetas superiores
function actualizarKPIs() {
    const kpiDia = document.getElementById("kpi-dia");
    if (!kpiDia) return;

    let totalDia = 0, totalMes = 0, totalHistorial = 0, cancelaciones = 0;
    const hoy = getFechaLocal();
    const mesActual = hoy.substring(0, 7); 

    ventasData.forEach(venta => {
        const totalVenta = parseFloat(venta.total) || 0;
        if (venta.estatus !== "Cancelado") {
            totalHistorial += totalVenta;
            if (venta.fecha === hoy) totalDia += totalVenta;
            if (venta.fecha.startsWith(mesActual)) totalMes += totalVenta;
        } else {
            cancelaciones++;
        }
    });

    kpiDia.innerText = `$${totalDia.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    document.getElementById("kpi-mes").innerText = `$${totalMes.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    document.getElementById("kpi-total").innerText = `$${totalHistorial.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    document.getElementById("kpi-cancelaciones").innerText = cancelaciones;
}

// ================================================================
// 3. ACCIONES CRUD Y MODALES
// ================================================================

function abrirModalVenta(creandoNuevo = true) {
    const modal = document.getElementById("modalVenta");
    if(!modal) return;
    modal.style.display = "flex";
    if (creandoNuevo) {
        modoEdicion = false;
        document.getElementById("formVenta").reset();
        document.getElementById("venta-id").value = "AUTOGENERADO";
        listaItemsVenta = [];
        renderListaItems();
        
        // Limpiar búsqueda de piezas
        const buscarInput = document.getElementById('buscar-pieza');
        const hiddenSelect = document.getElementById('select-pieza');
        if (buscarInput) buscarInput.value = '';
        if (hiddenSelect) { hiddenSelect.value = ''; hiddenSelect.dataset.precio = ''; hiddenSelect.dataset.nombre = ''; }
        ocultarDropdownPiezas();
    }
}

function filtrarPiezas(query) {
    const dropdown = document.getElementById('dropdown-piezas');
    if (!dropdown || !window.inventarioGlobal) return;

    const q = query.trim().toLowerCase();
    // Mostramos todos los productos que coincidan (o todo el catálogo si está vacío)
    const resultados = q.length === 0
        ? window.inventarioGlobal
        : window.inventarioGlobal.filter(p =>
            (p.pieza || '').toLowerCase().includes(q) ||
            (p.id || '').toString().toLowerCase().includes(q)
          );

    if (resultados.length === 0) {
        dropdown.innerHTML = '<div style="padding:15px; text-align:center; color:#94a3b8; font-size:0.85rem;">No se encontraron piezas con ese nombre o SKU.</div>';
    } else {
        dropdown.innerHTML = resultados.map(p => `
            <div onclick="seleccionarPieza('${p.id_puro}', '${(p.pieza||'').replace(/'/g,"\\'")}', ${p.precio})"
                style="padding:12px 15px; cursor:pointer; font-size:0.85rem; font-weight:600; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;"
                onmouseover="this.style.background='#f8fafc'; this.style.color='#e57373'" onmouseout="this.style.background='white'; this.style.color='#1e293b'">
                <div>
                    <span style="color:#94a3b8; font-size:0.7rem; font-family:monospace;">[${p.id}]</span> 
                    <span style="margin-left:5px;">${p.pieza}</span>
                </div>
                <span style="color:#10b981; font-weight:800;">$${parseFloat(p.precio).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
            </div>
        `).join('');
    }
    dropdown.style.display = 'block';
}

function toggleManualAjuste(isManual) {
    const inputTotal = document.getElementById('venta-total');
    const containerMotivo = document.getElementById('container-motivo');
    
    if (isManual) {
        inputTotal.readOnly = false;
        inputTotal.style.background = "white";
        inputTotal.focus();
        if(containerMotivo) containerMotivo.style.display = "block";
    } else {
        inputTotal.readOnly = true;
        inputTotal.style.background = "#f8fafc";
        if(containerMotivo) containerMotivo.style.display = "none";
        calcularTotalVenta(); // Volver al cálculo automático
    }
}

function calcularTotalVenta() {
    let granTotal = 0;
    listaItemsVenta.forEach(item => {
        granTotal += (item.subtotal || 0);
    });
    
    const inputTotal = document.getElementById('venta-total');
    if (inputTotal) {
        inputTotal.value = granTotal.toFixed(2);
    }
}

function seleccionarPieza(id, nombre, precio) {
    const buscarInput = document.getElementById('buscar-pieza');
    const hiddenSelect = document.getElementById('select-pieza');
    if (buscarInput) buscarInput.value = nombre;
    if (hiddenSelect) {
        hiddenSelect.value = id;
        hiddenSelect.dataset.precio = precio;
        hiddenSelect.dataset.nombre = nombre;
    }
    ocultarDropdownPiezas();
}

function ocultarDropdownPiezas() {
    const dropdown = document.getElementById('dropdown-piezas');
    if (dropdown) dropdown.style.display = 'none';
}

function agregarPiezaALista() {
    const select = document.getElementById('select-pieza');
    const inputCant = document.getElementById('input-cantidad');
    if (!select || !select.value) {
        alert("Selecciona una pieza primero.");
        return;
    }

    const id = select.value;
    const cant = parseInt(inputCant.value) || 0;
    const precio = parseFloat(select.dataset.precio);
    const nombre = select.dataset.nombre;

    if (cant <= 0) return;

    const itemExistente = listaItemsVenta.find(i => i.id_producto == id);
    if (itemExistente) {
        itemExistente.cantidad += cant;
        itemExistente.subtotal = itemExistente.cantidad * itemExistente.precio;
    } else {
        listaItemsVenta.push({
            id_producto: id,
            nombre: nombre,
            cantidad: cant,
            precio: precio,
            subtotal: cant * precio
        });
    }

    renderListaItems();
    inputCant.value = 1;
    // Reset search input
    const buscarInput = document.getElementById('buscar-pieza');
    if (buscarInput) buscarInput.value = '';
    select.value = '';
    select.dataset.precio = '';
    select.dataset.nombre = '';
}

function eliminarPieza(index) {
    listaItemsVenta.splice(index, 1);
    renderListaItems();
}

function renderListaItems() {
    const tbody = document.getElementById('lista-piezas-body');
    if (!tbody) return;

    if (listaItemsVenta.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 15px; color: #64748b;">No hay piezas añadidas</td></tr>';
        const inputTotal = document.getElementById('venta-total');
        if(inputTotal) inputTotal.value = "0.00";
        return;
    }

    tbody.innerHTML = '';
    listaItemsVenta.forEach((item, index) => {
        tbody.innerHTML += `
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;">${item.nombre}</td>
                <td style="padding: 10px; text-align: center;">
                    <input type="number" value="${item.cantidad}" min="1" 
                        onchange="actualizarCantidad(${index}, this.value)"
                        style="width: 60px; text-align: center; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px; font-weight: 700;">
                </td>
                <td style="padding: 10px; text-align: right; font-weight: 700;">$${item.subtotal.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td style="padding: 10px; text-align: center;">
                    <button type="button" onclick="eliminarPieza(${index})" style="background: none; border: none; color: #e57373; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    // Solo recalculamos si no está en modo manual
    const isManual = document.getElementById('ajuste-manual')?.checked;
    if (!isManual) {
        calcularTotalVenta();
    }
}

function actualizarCantidad(index, nuevaCantidad) {
    const cant = parseInt(nuevaCantidad) || 1;
    if (cant <= 0) {
        alert("La cantidad debe ser al menos 1");
        renderListaItems();
        return;
    }
    
    listaItemsVenta[index].cantidad = cant;
    listaItemsVenta[index].subtotal = cant * listaItemsVenta[index].precio;
    
    renderListaItems();
}

async function guardarVenta() {
    if (listaItemsVenta.length === 0) {
        alert("Falta añadir productos a la venta.");
        return;
    }

    const totalFinal = parseFloat(document.getElementById("venta-total").value) || 0;
    const isManual = document.getElementById('ajuste-manual')?.checked;
    const motivoManual = document.getElementById('motivo-manual')?.value.trim();

    if (isManual && !motivoManual) {
        alert("Atención: Es obligatorio ingresar el motivo del ajuste manual de precio.");
        document.getElementById('motivo-manual').focus();
        return;
    }

    let notasFinales = document.getElementById("venta-notes").value;
    if (isManual) {
        notasFinales += ` [AJUSTE MANUAL] Motivo: ${motivoManual}`;
    }

    const payload = {
        id_usuario: 1, 
        cliente_nombre: document.getElementById("venta-cliente").value,
        direccion: document.getElementById("venta-direccion").value,
        codigo_postal: document.getElementById("venta-cp").value,
        ciudad: document.getElementById("venta-ciudad").value,
        telefono: document.getElementById("venta-telefono").value,
        paqueteria: document.getElementById("venta-paqueteria").value,
        notas: notasFinales,
        items: listaItemsVenta,
        total_personalizado: totalFinal // Enviamos el total por si la API lo soporta o para tracking
    };

    try {
        const response = await fetch('/ventas/nueva', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.status === "success") {
            alert("¡Venta Registrada Exitosamente!");
            location.reload();
        } else {
            alert("Error: " + result.message);
        }
    } catch (e) {
        console.error(e);
        alert("Error de conexión al servidor.");
    }
}

function cerrarModal() {
    document.getElementById("modalVenta").style.display = "none";
}

async function verDetalleOrden(idOguia) {
    const modal = document.getElementById("modalDetalleVenta");
    if (!modal) return;
    
    // Mostramos cargando...
    modal.style.display = "flex";
    
    // Tratamos de encontrar el objeto en ventasData o enviosData
    let dataObj = (window.ventasData || []).find(v => v.id === idOguia || v.id_puro == idOguia);
    if (!dataObj && typeof window.enviosData !== 'undefined') {
        dataObj = window.enviosData.find(e => e.guia === idOguia || e.id_puro == idOguia);
    }
    
    const idPuro = dataObj ? (dataObj.id_puro || idOguia) : idOguia;

    try {
        const response = await fetch(`/ventas/detalle/${idPuro}`);
        const data = await response.json();
        
        // Sincronizar con los IDs de ventas.html (Phase 4 Fix)
        if(document.getElementById("det-folio")) document.getElementById("det-folio").innerText = data.id_pedido || idPuro;
        
        const u = data.usuario || {};
        let nombreMostrar = "Cliente Local";
        if (u && (u.nombre || u.apellido_paterno)) {
            nombreMostrar = `${u.nombre || ""} ${u.apellido_paterno || ""}`.trim();
        }

        const envio = data.envio || {};
        let notasMostrar = envio.notas || data.notas || "Sin notas";

        // Lógica de extracción del nombre si se guardó en notas (Hack de bajo impacto)
        if (notasMostrar.startsWith("CLIENTE: ")) {
            const parts = notasMostrar.split(" | ");
            nombreMostrar = parts[0].replace("CLIENTE: ", "").trim();
            notasMostrar = parts.length > 1 ? parts.slice(1).join(" | ") : "Sin notas adicionales";
        }

        if(document.getElementById("det-cliente-nombre")) document.getElementById("det-cliente-nombre").innerText = nombreMostrar;
        if(document.getElementById("det-direccion")) document.getElementById("det-direccion").innerText = envio.direccion || data.direccion || "Sucursal";
        if(document.getElementById("det-ciudad")) document.getElementById("det-ciudad").innerText = envio.ciudad || data.ciudad || "-";
        if(document.getElementById("det-cp")) document.getElementById("det-cp").innerText = envio.codigo_postal || data.codigo_postal || "-";
        if(document.getElementById("det-telefono")) document.getElementById("det-telefono").innerText = envio.telefono_contacto || data.telefono || "-";
        if(document.getElementById("det-notas")) document.getElementById("det-notas").innerText = notasMostrar;
        
        if(document.getElementById("det-fecha")) document.getElementById("det-fecha").innerText = data.fecha_pedido ? new Date(data.fecha_pedido).toLocaleDateString() : "N/A";
        
        const badgeEst = document.getElementById("det-estatus");
        if(badgeEst) {
            const estados = {1: "Recibido", 2: "Surtido", 3: "Enviado", 4: "Completado", 5: "Cancelado"};
            badgeEst.innerText = estados[data.id_estado] || "Procesando";
            badgeEst.className = `badge ${data.id_estado === 4 ? 'badge-success' : 'badge-warning'}`;
        }
        
        if(document.getElementById("det-total")) document.getElementById("det-total").innerText = `$${parseFloat(data.total || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}`;

        // Timeline Sync
        for(let i=1; i<=4; i++) {
            const step = document.getElementById(`step-${i}`);
            if(!step) continue;
            const circle = step.querySelector('.circle');
            if (i <= data.id_estado && data.id_estado < 5) {
                circle.style.background = "#e57373"; // Rojo Autopartes
            } else {
                circle.style.background = "#ddd";
            }
        }

        // Llenar productos
        const listContainer = document.getElementById("det-piezas-list");
        if (listContainer) {
            listContainer.innerHTML = "";
            const detalles = data.detalles || [];
            detalles.forEach(d => {
                listContainer.innerHTML += `
                    <li style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; color: #000;">
                        <span><strong>${d.producto ? d.producto.nombre_producto : 'Pieza #'+d.id_producto}</strong> x ${d.cantidad}</span>
                        <span style="font-weight: bold;">$${(d.cantidad * d.precio_unitario).toLocaleString()}</span>
                    </li>
                `;
            });
        }
        
    } catch (error) {
        console.error("Error al obtener detalle:", error);
    }
}

async function confirmarPedido(id) {
    const venta = ventasData.find(v => v.id === id);
    if (!venta) return;

    if (confirm(`¿Confirmar orden #${venta.id_puro}? Esto notificará al cliente y pasará el pedido a SURTIDO (Almacén).`)) {
        try {
            const response = await fetch(`/ventas/confirmar/${venta.id_puro}`, { method: 'POST' });
            const result = await response.json();
            if (result.status === "success") {
                alert("¡Orden Confirmada!\nEl equipo de almacén ha sido notificado para surtir las piezas.");
                location.reload();
            } else {
                alert("Error: " + result.message);
            }
        } catch (e) {
            alert("Error de conexión al intentar confirmar el pedido.");
        }
    }
}

async function eliminarVenta(id) {
    // Buscar en ventasData o usar id como id_puro directo (Fase 6 Sync)
    const venta = (window.ventasData || []).find(v => v.id === id || v.id_puro == id);
    const idPuro = venta ? venta.id_puro : id;

    if (!idPuro) return;

    if (confirm(`¿Estás seguro de eliminar permanentemente la orden #${idPuro}? El stock será devuelto al inventario.`)) {
        try {
            const response = await fetch(`/ventas/eliminar/${idPuro}`, { method: 'POST' });
            const result = await response.json();
            if (result.status === "success") {
                location.reload(); 
            } else {
                alert("Error: " + result.message);
            }
        } catch (e) {
            alert("Error de conexión al intentar eliminar la venta.");
        }
    }
}

async function editarVenta(id) {
    let idPuro = id;
    if (typeof id === 'string' && id.startsWith('V-')) {
        const v = (window.ventasData || []).find(venta => venta.id === id);
        if (v) idPuro = v.id_puro;
    }
    
    if (!idPuro) return;

    try {
        const response = await fetch(`/ventas/detalle/${idPuro}`);
        const data = await response.json();

        if (data.id_pedido) {
            document.getElementById('edit-folio-title').innerText = data.id_pedido;
            document.getElementById('edit-id-puro').value = data.id_pedido;
            
            // Llenar campos de envío (Phase 4 Fix: soporte para datos planos o anidados)
            const envio = data.envio || data;
            document.getElementById('edit-direccion').value = envio.direccion || "";
            document.getElementById('edit-ciudad').value = envio.ciudad || "";
            document.getElementById('edit-cp').value = envio.codigo_postal || "";
            document.getElementById('edit-telefono').value = envio.telefono_contacto || envio.telefono || "";
            document.getElementById('edit-notas').value = envio.notas || "";
            
            // Llenar estado
            document.getElementById('edit-estado').value = data.id_estado;

            document.getElementById('modalEditarPedido').style.display = "flex";
        }
    } catch (e) {
        alert("Error al cargar los datos del pedido para edición.");
    }
}

async function gestionarEnvioLogistica() {
    const id = document.getElementById('edit-id-puro').value;
    if (!id) return;
    
    if (confirm("¿Marcar este pedido como ENVIADO? Se registrará la fecha de salida y pasará al módulo de logística.")) {
        document.getElementById('edit-estado').value = "3"; // 3 = Enviado
        // Opcional: Podríamos hacer el submit automático aquí
        document.getElementById('formEditarPedido').requestSubmit();
    }
}

// Inicializar el formulario de edición avanzada
document.addEventListener('DOMContentLoaded', () => {
    const formEdit = document.getElementById('formEditarPedido');
    if (formEdit) {
        formEdit.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('edit-id-puro').value;
            const formData = new FormData(formEdit);
            
            try {
                const response = await fetch(`/ventas/editar/${id}`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.status === "success") {
                    alert("¡Cambios Guardados!\nEl pedido ha sido actualizado exitosamente.");
                    location.reload();
                } else {
                    alert("Error: " + result.message);
                }
            } catch (err) {
                alert("Error de conexión al intentar guardar los cambios.");
            }
        });
    }
});

// ================================================================
// 4. FILTROS Y EXPORTACIÓN
// ================================================================

function filtrarTabla() {
    const textFilter   = (document.getElementById("buscador")?.value || "").toLowerCase();
    const statusFilter = document.getElementById("filtro-estatus")?.value || "";
    const dateFilter   = document.getElementById("filtro-fecha")?.value || "";
    const catFilter    = (document.getElementById("filtro-categoria-v")?.value || "").toLowerCase();

    const tbody = document.getElementById("tabla-ventas-body");
    if (!tbody) return;
    const trs = tbody.getElementsByTagName("tr");

    for (let i = 0; i < trs.length; i++) {
        const tr = trs[i];
        const rowText    = tr.textContent.toLowerCase();
        const cellStatus = tr.cells[5]?.innerText.trim() || "";
        const cellDate   = tr.cells[3]?.innerText.trim() || "";
        const cellPiezas = (tr.cells[2]?.innerText || "").toLowerCase();

        const matchText   = rowText.includes(textFilter);
        const matchStatus = statusFilter === "" || cellStatus === statusFilter;
        const matchDate   = dateFilter   === "" || cellDate   === dateFilter;
        const matchCat    = catFilter    === "" || cellPiezas.includes(catFilter);

        tr.style.display = (matchText && matchStatus && matchDate && matchCat) ? "" : "none";
    }
}

function descargarPDF(contexto = 'ventas') {
    // 0. Validar librerías
    if (typeof jspdf === 'undefined' || typeof jspdf.jsPDF === 'undefined') {
        alert("Librería jsPDF no detectada. Verifique la conexión a internet.");
        return;
    }

    // Mostrar loader
    let loader = document.getElementById('pdf-loading-overlay');
    if (loader) loader.style.display = 'flex';

    // 1. Obtener Datos
    const sourceTableId = {
        'ventas': 'tabla-ventas-body',
        'logistica': 'tabla-logistica-body',
        'almacen': 'tabla-almacen-body'
    }[contexto];

    const mainTableBody = document.getElementById(sourceTableId);
    if (!mainTableBody) {
        alert("Error: No se encontró la tabla de origen.");
        if (loader) loader.style.display = 'none';
        return;
    }

    // 2. Inicializar Documento
    const { jsPDF } = jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    const pageWidth = doc.internal.pageSize.getWidth();

    // 3. Dibujar Branding Corporativo (Manual Draw)
    // Fondo de acento según módulo
    const themeColors = { 'ventas': [229, 115, 115], 'almacen': [46, 204, 113], 'logistica': [52, 152, 219] };
    const accent = themeColors[contexto] || [30, 41, 59];

    // Encabezado PURE TEXT (Garantiza visibilidad)
    doc.setFillColor(30, 41, 59);
    doc.rect(0, 0, pageWidth, 40, 'F');
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(22);
    doc.setFont("helvetica", "bold");
    doc.text("AUTOPARTES MACUIN", 15, 20);
    
    doc.setFontSize(9);
    doc.setFont("helvetica", "normal");
    doc.text("SOLUCIONES AUTOMOTRICES DE ALTA PRECISIÓN", 15, 28);
    doc.text("Av. Tecnológico #123, Querétaro, MX | contacto@macuin.com", 15, 33);

    // Folio y Título del Reporte
    doc.setFillColor(...accent);
    doc.rect(pageWidth - 75, 12, 60, 18, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(10);
    doc.text(`REPORTE DE ${contexto.toUpperCase()}`, pageWidth - 70, 19);
    doc.setFontSize(8);
    const folio = `#REP-${new Date().getTime().toString().slice(-4)}`;
    doc.text(`FOLIO: ${folio}`, pageWidth - 70, 25);

    // 4. Configurar Columnas y Filas para AutoTable
    let columns = [];
    let data = [];

    if (contexto === 'ventas') {
        columns = ["ID / FOLIO", "CLIENTE / TALLER", "PIEZAS", "FECHA", "TOTAL"];
        Array.from(mainTableBody.rows).forEach(row => {
            if (row.style.display !== 'none') {
                const c = row.cells;
                data.push([c[0].innerText, c[1].innerText, c[2].innerText, c[3].innerText, c[4].innerText]);
            }
        });
    } else if (contexto === 'logistica') {
        columns = ["GUÍA / ID", "DESTINATARIO", "COURIER", "ESTADO", "ENTREGA"];
        Array.from(mainTableBody.rows).forEach(row => {
            if (row.style.display !== 'none') {
                const c = row.cells;
                data.push([c[0].innerText, c[1].innerText, c[2].innerText, c[4].innerText, c[5].innerText]);
            }
        });
    } else {
        columns = ["SKU / ID", "PRODUCTO", "CATEGORÍA", "PRECIO", "STOCK"];
        Array.from(mainTableBody.rows).forEach(row => {
            if (row.style.display !== 'none') {
                const c = row.cells;
                data.push([c[0].innerText, c[1].innerText, c[2].innerText, c[5].innerText, c[4].innerText]);
            }
        });
    }

    // 5. Generar Tabla Programática
    doc.autoTable({
        head: [columns],
        body: data,
        startY: 50,
        theme: 'striped',
        headStyles: { fillColor: [30, 41, 59], textColor: 255, fontSize: 10, halign: 'center' },
        styles: { fontSize: 9, cellPadding: 4, halign: 'left' },
        columnStyles: { 0: { fontStyle: 'bold' }, 4: { halign: 'right' } },
        margin: { left: 15, right: 15 }
    });

    // 6. Pie de Página y Firmas
    const finalY = doc.lastAutoTable.finalY + 20;
    doc.setFontSize(8);
    doc.setTextColor(100);
    doc.text("ESTE DOCUMENTO ES UN COMPROBANTE OFICIAL DE CONTROL INTERNO.", 15, finalY);
    doc.text(`FECHA DE EMISIÓN: ${new Date().toLocaleString()}`, 15, finalY + 5);
    
    // Línea de firma
    doc.setDrawColor(0);
    doc.line(130, finalY + 15, 180, finalY + 15);
    doc.text("FIRMA AUTORIZADA", 140, finalY + 20);

    // Finalizar
    doc.save(`MACUIN_${contexto.toUpperCase()}_OFICIAL.pdf`);
    if (loader) loader.style.display = 'none';
    console.log("✅ Reporte Programático Generado Correctamente.");
}

function exportarExcel(contexto) {
    if (typeof XLSX === 'undefined') {
        alert("La librería para exportar a Excel no está cargada.");
        return;
    }

    // Auto-detect context if not provided
    if (!contexto) {
        if (document.getElementById('tabla-logistica-body')) contexto = 'logistica';
        else if (document.getElementById('tabla-almacen-body')) contexto = 'almacen';
        else contexto = 'ventas';
    }

    const tableIds = {
        ventas:    'tabla-ventas-body',
        almacen:   'tabla-almacen-body',
        logistica: 'tabla-logistica-body'
    };

    const headers = {
        ventas:    ["Folio", "Cliente / Taller", "Piezas", "Fecha", "Total ($)", "Estatus"],
        almacen:   ["SKU", "Pieza", "Categoría", "Ubicación", "Stock", "Precio ($)"],
        logistica: ["Guía / ID", "Destinatario", "Paquetería", "Estado", "Entrega Est."]
    };

    const colIndexes = {
        ventas:    [0, 1, 2, 3, 4, 5],
        almacen:   [0, 1, 2, 3, 4, 5],
        logistica: [0, 1, 2, 4, 5]
    };

    const fileName = contexto.toUpperCase();
    const ws_data  = [];

    // Metadata row with active filters
    ws_data.push([`REPORTE DE ${fileName} - MACUIN Enterprise`]);
    ws_data.push([`Generado: ${new Date().toLocaleString()}`]);
    ws_data.push([_getActiveFiltersText(contexto)]);
    ws_data.push([]); // blank row
    ws_data.push(headers[contexto]);

    // Read only visible rows (filters applied)
    const tbody = document.getElementById(tableIds[contexto]);
    if (tbody) {
        Array.from(tbody.rows).forEach(row => {
            if (row.style.display !== 'none') {
                ws_data.push(colIndexes[contexto].map(ci => (row.cells[ci]?.innerText || "").trim()));
            }
        });
    }

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(ws_data);
    // Bold the title row
    if (ws['A1']) ws['A1'].s = { font: { bold: true } };
    XLSX.utils.book_append_sheet(wb, ws, fileName);
    XLSX.writeFile(wb, `REPORTE_MACUIN_${fileName}_${new Date().toISOString().split('T')[0]}.xlsx`);
}

/**
 * Returns a human-readable summary of active filters for a given context.
 */
function _getActiveFiltersText(contexto) {
    const parts = ["Filtros activos:"];
    if (contexto === 'ventas') {
        const s = document.getElementById('filtro-estatus')?.value;
        const d = document.getElementById('filtro-fecha')?.value;
        const c = document.getElementById('filtro-categoria-v')?.value;
        if (s) parts.push(`Estatus=${s}`);
        if (d) parts.push(`Fecha=${d}`);
        if (c) parts.push(`Categoría=${c}`);
    } else if (contexto === 'almacen') {
        const c = document.getElementById('filtro-categoria-a')?.value;
        if (c) parts.push(`Categoría=${c}`);
    } else if (contexto === 'logistica') {
        const s   = document.getElementById('filtro-estatus-l')?.value;
        const d   = document.getElementById('filtro-fecha-l')?.value;
        const paq = document.getElementById('filtro-paqueteria-l')?.value;
        if (s)   parts.push(`Estatus=${s}`);
        if (d)   parts.push(`Fecha=${d}`);
        if (paq) parts.push(`Paquetería=${paq}`);
    }
    return parts.length > 1 ? parts.join(" | ") : "Sin filtros aplicados";
}

/**
 * Exportar a DOCX (Word) - usa html-docx-js si disponible, fallback a .doc HTML
 */
function exportarDocx(contexto) {
    contexto = contexto || 'ventas';

    const tableIds = {
        ventas:    'tabla-ventas-body',
        almacen:   'tabla-almacen-body',
        logistica: 'tabla-logistica-body'
    };

    const headersByCtx = {
        ventas:    ["Folio", "Cliente / Taller", "Piezas", "Fecha", "Total ($)", "Estatus"],
        almacen:   ["SKU", "Pieza", "Categoría", "Ubicación", "Stock", "Precio ($)"],
        logistica: ["Guía / ID", "Destinatario", "Paquetería", "Estado", "Entrega Est."]
    };

    const colsByCtx = {
        ventas:    [0, 1, 2, 3, 4, 5],
        almacen:   [0, 1, 2, 3, 4, 5],
        logistica: [0, 1, 2, 4, 5]
    };

    const headers  = headersByCtx[contexto];
    const colIdxs  = colsByCtx[contexto];
    const tbody    = document.getElementById(tableIds[contexto]);
    const filtersText = _getActiveFiltersText(contexto);
    const fecha    = new Date().toLocaleString();

    // Build HTML table with inline styles for Word compatibility
    let tableRows = "";
    if (tbody) {
        Array.from(tbody.rows).forEach(row => {
            if (row.style.display !== 'none') {
                const cells = colIdxs.map(ci => `<td style="border:1px solid #ccc; padding:6px 10px; font-size:11pt;">${(row.cells[ci]?.innerText || "").trim()}</td>`).join('');
                tableRows += `<tr>${cells}</tr>`;
            }
        });
    }

    const headerRow = headers.map(h => `<th style="background:#1e293b; color:white; border:1px solid #1e293b; padding:8px 10px; font-size:11pt; text-align:left;">${h}</th>`).join('');

    const htmlContent = `
    <!DOCTYPE html>
    <html xmlns:o='urn:schemas-microsoft-com:office:office'
          xmlns:w='urn:schemas-microsoft-com:office:word'
          xmlns='http://www.w3.org/TR/REC-html40'>
    <head>
        <meta charset='utf-8'>
        <title>MACUIN - Reporte de ${contexto.toUpperCase()}</title>
        <style>
            body { font-family: Calibri, Arial, sans-serif; margin: 20mm; }
            h1   { color: #1e293b; font-size: 16pt; margin-bottom: 4pt; }
            p    { font-size: 9pt; color: #64748b; margin: 2pt 0; }
            table { border-collapse: collapse; width: 100%; margin-top: 14pt; }
        </style>
    </head>
    <body>
        <h1>AUTOPARTES MACUIN &mdash; REPORTE DE ${contexto.toUpperCase()}</h1>
        <p>Generado: ${fecha}</p>
        <p>${filtersText}</p>
        <table>
            <thead><tr>${headerRow}</tr></thead>
            <tbody>${tableRows}</tbody>
        </table>
        <p style="margin-top:20pt; font-size:8pt; color:#94a3b8;">
            MACUIN Enterprise &mdash; Documento de Control Interno
        </p>
    </body>
    </html>`;

    const fileName = `MACUIN_${contexto.toUpperCase()}_${new Date().toISOString().split('T')[0]}`;

    // Use html-docx-js if loaded (produces real .docx), else fallback to .doc blob
    if (typeof htmlDocx !== 'undefined' && htmlDocx.asBlob) {
        try {
            const blob = htmlDocx.asBlob(htmlContent);
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href     = url;
            a.download = `${fileName}.docx`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            return;
        } catch (e) {
            console.warn("html-docx-js falló, usando fallback .doc:", e);
        }
    }

    // Fallback: HTML blob opened by Word as .doc
    const blob = new Blob(['\ufeff', htmlContent], { type: 'application/msword' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `${fileName}.doc`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// ================================================================
// SISTEMA DE NOTIFICACIONES DINÁMICAS
// ================================================================

function toggleNotificaciones() {
    const dropdown = document.getElementById('notif-dropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

function inicializarNotificaciones() {
    const list = document.getElementById('notif-list');
    const badge = document.getElementById('notif-badge');
    if (!list || !badge) return;

    list.innerHTML = '';
    let count = 0;

    // 1. Alertas de Stock Bajo (Stock < 5)
    const stockBajoItems = (window.inventarioData || []).filter(item => parseInt(item.stock) < 5);
    stockBajoItems.forEach(item => {
        count++;
        const itemDiv = document.createElement('div');
        itemDiv.className = 'notif-item';
        itemDiv.onclick = () => irAAccion('almacen', item.id_puro);
        itemDiv.innerHTML = `
            <div class="notif-icon icon-stock"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="notif-body">
                <span class="notif-msg">Stock Bajo: ${item.pieza}</span>
                <span class="notif-time">Quedan: ${item.stock} pzas</span>
            </div>`;
        list.appendChild(itemDiv);
    });

    // 2. Avisos de Nuevos Pedidos (Estado 1 - Recibido)
    const nuevosPedidos = (window.logisticaData || []).filter(p => p.id_estado == 1 || p.estado === "Recibido");
    nuevosPedidos.forEach(pedido => {
        count++;
        const itemDiv = document.createElement('div');
        itemDiv.className = 'notif-item';
        itemDiv.onclick = () => irAAccion('logistica', pedido.id_pedido);
        itemDiv.innerHTML = `
            <div class="notif-icon icon-order"><i class="fas fa-shopping-cart"></i></div>
            <div class="notif-body">
                <span class="notif-msg">Pedido Nuevo #${pedido.id_pedido}</span>
                <span class="notif-time">${pedido.cliente || 'Pendiente'}</span>
            </div>`;
        list.appendChild(itemDiv);
    });

    badge.innerText = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
    
    // Actualizar también los números del Dashboard si existen
    const dashboardAvisos = document.querySelector('.hero-welcome p + div div:nth-child(1) p');
    if (dashboardAvisos) dashboardAvisos.innerText = `${nuevosPedidos.length} Pedidos en espera`;
    
    const dashboardAlertas = document.querySelector('.hero-welcome p + div div:nth-child(2) p');
    if (dashboardAlertas) dashboardAlertas.innerText = `${stockBajoItems.length} Piezas en stock bajo`;

    if (count === 0) list.innerHTML = '<div class="notif-empty" style="padding:20px; text-align:center; color:#94a3b8;">No hay avisos hoy</div>';
}

function irAAccion(modulo, id) {
    const currentLoc = window.location.pathname;
    
    if ((modulo === 'almacen' && currentLoc.includes('/almacen')) || 
        (modulo === 'logistica' && currentLoc.includes('/logistica'))) {
        ejecutarResaltado(modulo, id);
    } else {
        // Guardar en sessionStorage y navegar
        sessionStorage.setItem('pendingAction', JSON.stringify({ modulo, id }));
        window.location.href = `/${modulo}`;
    }
}

function ejecutarResaltado(modulo, id) {
    if (modulo === 'almacen') {
        renderAlmacen(); // Asegurar que esté renderizado
        setTimeout(() => {
            const rows = document.querySelectorAll('#tabla-almacen-body tr');
            rows.forEach(row => {
                if (row.innerHTML.includes(id)) {
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    row.style.backgroundColor = '#fff3cd';
                    setTimeout(() => row.style.backgroundColor = '', 3000);
                }
            });
        }, 500);
    } else {
        // En logística, abrir el modal de estado
        const row = document.getElementById(`row-pedido-${id}`);
        if (row) {
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            row.style.backgroundColor = '#f0f9ff';
            setTimeout(() => row.style.backgroundColor = '', 3000);
            abrirAsignarPaqueteriaRapida(id); // Función ya existente
        }
    }
}

// Check for pending actions on load
window.addEventListener('DOMContentLoaded', () => {
    const pending = sessionStorage.getItem('pendingAction');
    if (pending) {
        const { modulo, id } = JSON.parse(pending);
        sessionStorage.removeItem('pendingAction');
        setTimeout(() => ejecutarResaltado(modulo, id), 800);
    }
});

// ================================================================
function ordenarAlmacen(criterio) {
    if (!window.inventarioData) {
        console.error("No hay datos de inventario disponibles.");
        return;
    }
    
    // 1. Clonar y ordenar los datos
    let sortedData = [...window.inventarioData];
    if (criterio === 'asc') {
        sortedData.sort((a, b) => (parseInt(a.stock) || 0) - (parseInt(b.stock) || 0));
    } else if (criterio === 'desc') {
        sortedData.sort((a, b) => (parseInt(b.stock) || 0) - (parseInt(a.stock) || 0));
    }

    // 2. IMPORTANTE: Actualizar la referencia local para que la paginación use el nuevo orden
    window.inventarioGlobalSorted = sortedData;
    paginaActualAlmacen = 1; // Reiniciar a la primera página
    
    console.log(`Ordenando almacén por: ${criterio}`);
    renderAlmacen();
}

function marcarComoVisto() {
    const badge = document.getElementById('notif-badge');
    badge.style.display = 'none';
    alert("Notificaciones marcadas como leídas.");
}

// ================================================================
// SISTEMA DE NAVEGACIÓN RÁPIDA (Modules Dropdown)
// ================================================================

function toggleNavMenu(event) {
    if (event) event.stopPropagation();
    const dropdown = document.querySelector('.nav-dropdown-content');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

// Llamar al cargar
window.addEventListener('DOMContentLoaded', () => {
    inicializarNotificaciones();
    
    // Cerrar notificaciones y menú nav al hacer clic fuera
    document.addEventListener('click', (e) => {
        // Notificaciones
        const wrapperNotif = document.querySelector('.notification-bell-wrapper');
        const dropdownNotif = document.getElementById('notif-dropdown');
        if (wrapperNotif && !wrapperNotif.contains(e.target)) {
            if (dropdownNotif) dropdownNotif.style.display = 'none';
        }

        // Menú Navegación
        const wrapperNav = document.querySelector('.nav-dropdown-wrapper');
        const dropdownNav = document.querySelector('.nav-dropdown-content');
        if (wrapperNav && !wrapperNav.contains(e.target)) {
            if (dropdownNav) dropdownNav.classList.remove('active');
        }
    });
});

// ================================================================
// MÓDULO DE ALMACÉN (Inventario Inteligente)
// ================================================================

let inventarioData = window.inventarioData || [];
let paginaActualAlmacen = 1;
const itemsPorPaginaAlmacen = 10;

function renderAlmacen() {
    const tbody = document.getElementById("tabla-almacen-body");
    if (!tbody) return;
    tbody.innerHTML = "";

    // Usar datos ordenados si existen, de lo contrario los originales
    const dataAlmacen = window.inventarioGlobalSorted || window.inventarioData || [];

    const isDark = document.body.classList.contains('theme-dark');
    const textColor = isDark ? '#ffffff' : '#000000';
    const borderColor = isDark ? '#333' : '#eee';

    // Paginación lógica
    const inicio = (paginaActualAlmacen - 1) * itemsPorPaginaAlmacen;
    const fin = inicio + itemsPorPaginaAlmacen;
    const dataPaginada = dataAlmacen.slice(inicio, fin);

    dataPaginada.forEach(item => {
        let stockVal = parseInt(item.stock) || 0;
        let stockStatus = stockVal < 5 ? 'badge-danger' : 'badge-success';
        let stockIcon = stockVal < 5 ? '<i class="fas fa-exclamation-triangle"></i> ' : '<i class="fas fa-check-circle"></i> ';

        tbody.innerHTML += `
            <tr style="border-bottom: 1px solid ${borderColor};">
                <td style="color: ${textColor}; font-weight: 800;">#${item.id_puro || item.id_producto}</td>
                <td style="color: ${textColor};">${item.pieza || item.nombre_producto}</td>
                <td><span class="tag-categoria" style="color: ${textColor}; border: 1px solid ${isDark ? '#444' : '#ddd'};">${item.categoria || 'Repuestos'}</span></td>
                <td style="color: ${textColor};"><i class="fas fa-map-marker-alt" style="opacity: 0.5;"></i> ${item.ubicacion || 'Cualquiera'}</td>
                <td><span class="badge ${stockStatus}" style="border-radius: 4px; padding: 4px 10px; font-weight: 700;">${stockIcon}${stockVal}</span></td>
                <td style="color: ${textColor}; font-weight: 700;">$${parseFloat(item.precio || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="row-actions" style="display:flex; gap:5px;">
                    <button class="btn-action-sm" onclick="sumarUnoStock('${item.id_puro || item.id_producto}')" title="Agregar 1 Stock" style="background:#2ecc71; color:white; border:none; border-radius:4px; padding:4px 8px; cursor:pointer;"><i class="fas fa-plus"></i></button>
                    <button class="btn-action-sm" onclick="restarUnoStock('${item.id_puro || item.id_producto}')" title="Restar 1 Stock" style="background:#e67e22; color:white; border:none; border-radius:4px; padding:4px 8px; cursor:pointer;"><i class="fas fa-minus"></i></button>
                    <button class="btn-action-sm btn-edit-sm" onclick="handleInventorySelection('${item.id_puro || item.id_producto}', 'edit')" title="Editar"><i class="fas fa-edit"></i></button>
                </td>
            </tr>
        `;
    });
    renderPaginacionAlmacen();
}

function renderPaginacionAlmacen() {
    const container = document.getElementById("paginacion-almacen");
    if (!container) return;
    container.innerHTML = "";
    
    const paginasTotales = Math.ceil(inventarioData.length / itemsPorPaginaAlmacen);
    if (paginasTotales <= 1) return;

    // Contenedor de Estilo Premium
    const nav = document.createElement("div");
    nav.style.cssText = "display: flex; align-items: center; gap: 5px; background: #f8fafc; padding: 10px 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);";

    // Etiqueta: Página X de Y
    const label = document.createElement("span");
    label.innerText = `Página ${paginaActualAlmacen} de ${paginasTotales}`;
    label.style.cssText = "margin-right: 15px; font-size: 0.85rem; font-weight: 700; color: #64748b; background: white; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0;";
    nav.appendChild(label);

    const createBtn = (text, page, isActive = false, isDisabled = false) => {
        const btn = document.createElement("button");
        btn.innerText = text;
        btn.disabled = isDisabled;
        btn.style.cssText = `
            min-width: 35px;
            height: 35px;
            padding: 0 10px;
            border: 1px solid ${isActive ? '#3b82f6' : '#e2e8f0'};
            border-radius: 6px;
            background: ${isActive ? '#3b82f6' : 'white'};
            color: ${isActive ? 'white' : '#1e293b'};
            font-weight: 700;
            font-size: 0.85rem;
            cursor: ${isDisabled ? 'default' : 'pointer'};
            transition: all 0.2s;
            opacity: ${isDisabled ? '0.5' : '1'};
        `;
        if (!isDisabled) {
            btn.onmouseover = () => { if(!isActive) btn.style.background = "#f1f5f9"; };
            btn.onmouseout = () => { if(!isActive) btn.style.background = "white"; };
            btn.onclick = () => {
                paginaActualAlmacen = page;
                renderAlmacen();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };
        }
        return btn;
    };

    // Botón Inicio
    nav.appendChild(createBtn("«", 1, false, paginaActualAlmacen === 1));

    // Lógica de Números con Elipses
    let range = [];
    let delta = 2;
    for (let i = Math.max(2, paginaActualAlmacen - delta); i <= Math.min(paginasTotales - 1, paginaActualAlmacen + delta); i++) {
        range.push(i);
    }

    // Primera Página siempre visible
    nav.appendChild(createBtn("1", 1, paginaActualAlmacen === 1));

    if (range[0] > 2) {
        const span = document.createElement("span");
        span.innerText = "...";
        span.style.cssText = "padding: 0 5px; color: #94a3b8;";
        nav.appendChild(span);
    }

    range.forEach(i => {
        nav.appendChild(createBtn(i.toString(), i, paginaActualAlmacen === i));
    });

    if (range[range.length - 1] < paginasTotales - 1) {
        const span = document.createElement("span");
        span.innerText = "...";
        span.style.cssText = "padding: 0 5px; color: #94a3b8;";
        nav.appendChild(span);
    }

    // Última Página siempre visible
    if (paginasTotales > 1) {
        nav.appendChild(createBtn(paginasTotales.toString(), paginasTotales, paginaActualAlmacen === paginasTotales));
    }

    // Botón Siguiente / Última
    nav.appendChild(createBtn("»", Math.min(paginasTotales, paginaActualAlmacen + 1), false, paginaActualAlmacen === paginasTotales));
    
    const lastBtn = createBtn("Última »", paginasTotales, false, paginaActualAlmacen === paginasTotales);
    lastBtn.style.minWidth = "auto";
    nav.appendChild(lastBtn);

    container.appendChild(nav);
}

async function sumarUnoStock(id) {
    if(!id) return;
    try {
        const response = await fetch(`/almacen/ajustar/${id}/1`, { method: 'POST' });
        const result = await response.json();
        if (result.status === "success") {
            const item = (window.inventarioData || []).find(i => i.id_puro == id);
            if(item) {
                item.stock = (parseInt(item.stock) || 0) + 1;
                renderAlmacen(); // Actualización inmediata sin recarga
            }
        }
    } catch (e) { console.error(e); }
}

async function restarUnoStock(id) {
    if(!id) return;
    try {
        const response = await fetch(`/almacen/ajustar/${id}/-1`, { method: 'POST' });
        const result = await response.json();
        if (result.status === "success") {
            const item = (window.inventarioData || []).find(i => i.id_puro == id);
            if(item && item.stock > 0) {
                item.stock = (parseInt(item.stock) || 0) - 1;
                renderAlmacen(); // Actualización inmediata sin recarga
            }
        }
    } catch (e) { console.error(e); }
}

/**
 * Filtro Maestro para Almacén
 */
function filtrarAlmacen() {
    const filter    = (document.getElementById("buscador-almacen")?.value || "").toLowerCase();
    const catFilter = (document.getElementById("filtro-categoria-a")?.value || "").toLowerCase();

    const tbody = document.getElementById("tabla-almacen-body");
    if (!tbody) return;
    const trs = tbody.getElementsByTagName("tr");

    for (let i = 0; i < trs.length; i++) {
        const tr = trs[i];
        const rowText = tr.textContent.toLowerCase();
        // categoria column index 2
        const cellCat = (tr.cells[2]?.innerText || "").toLowerCase();

        const matchText = rowText.includes(filter);
        const matchCat  = catFilter === "" || cellCat.includes(catFilter);

        tr.style.display = (matchText && matchCat) ? "" : "none";
    }
}

function actualizarKPIsAlmacen() {
    if (!document.getElementById("kpi-total-piezas") || !window.inventarioData) return;
    
    let totalPiezas = window.inventarioData.reduce((acc, item) => acc + (item.stock || 0), 0);
    let valorInventario = window.inventarioData.reduce((acc, item) => acc + ((item.stock || 0) * (item.precio || 0)), 0);
    let stockBajo = window.inventarioData.filter(item => item.stock <= item.min).length;

    document.getElementById("kpi-total-piezas").innerText = totalPiezas;
    document.getElementById("kpi-valor-total").innerText = `$${valorInventario.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    document.getElementById("kpi-stock-bajo").innerText = stockBajo;
}

// Visualización de imagen en tiempo real
function previewImage(input) {
    const placeholder = document.getElementById('upload-placeholder');
    const preview = document.getElementById('img-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

async function ajustarStock(id, cantidad) {
    const item = window.inventarioData.find(i => i.id === id);
    if (!item) return;

    if (item.stock + cantidad < 0) return alert("Error: No puedes tener stock negativo.");

    const idPuro = item.id_puro;

    try {
        const response = await fetch(`/almacen/ajustar/${idPuro}/${cantidad}`, { method: 'POST' });
        const result = await response.json();
        
        if (result.status === "success") {
            item.stock = result.data.nueva_cantidad;
            renderAlmacen();
        } else {
            alert("Error al procesar el ajuste en el servidor.");
        }
    } catch (e) {
        console.error(e);
        alert("Error de conexión al intentar ajustar el stock.");
    }
}

async function guardarEntrada() {
    const idPuro = document.getElementById("ent-id-puro").value;
    const cantidadStr = document.getElementById("ent-cantidad").value;
    const tipo = parseInt(document.getElementById("ent-tipo").value);
    const cantidad = parseInt(cantidadStr);
    
    if (!idPuro) return alert("Por favor seleccione una refacción.");
    if (isNaN(cantidad) || cantidad <= 0) return alert("Por favor ingrese una cantidad válida.");

    const totalAjuste = cantidad * tipo;
    
    try {
        const response = await fetch(`/almacen/ajustar/${idPuro}/${totalAjuste}`, { method: 'POST' });
        const result = await response.json();
        
        if (result.status === "success") {
            cerrarMasterModal();
            location.reload(); 
        } else {
            alert("Error al procesar la entrada: " + result.message);
        }
    } catch (e) {
        alert("Error de conexión al servidor.");
    }
}

function abrirMasterModal() {
    const modal = document.getElementById("modalAlmacen");
    if (modal) {
        handleInventorySelection('new'); // Predeterminado a nuevo
        modal.style.display = "flex";
    }
}

function cerrarMasterModal() {
    const modal = document.getElementById("modalAlmacen");
    if (modal) modal.style.display = "none";
}

function handleInventorySelection(id, forcedMode = null) {
    const modeInput = document.getElementById("manage-mode");
    const idInput = document.getElementById("manage-id");
    const stockControl = document.getElementById("unified-stock-control");
    const stockLabel = document.getElementById("unified-stock-label");
    const mainBtn = document.getElementById("main-manage-btn");
    const modalTitle = document.getElementById("modal-title");
    const modalSubtitle = document.getElementById("modal-subtitle");
    const modal = document.getElementById("modalAlmacen");

    if (modal) modal.style.display = "flex";

    document.getElementById("inventory-master-form").reset();
    document.getElementById("unified-quantity").value = 0;
    
    let mode = forcedMode;
    if (!mode) mode = (!id || id === 'new') ? 'new' : 'edit';

    if (mode === 'new') {
        modeInput.value = "new";
        idInput.value = "";
        
        // MOSTRAR TODOS LOS CAMPOS SEGÚN SOLICITUD
        const imgSec = document.getElementById("full-data-section-img");
        const fieldsSec = document.getElementById("full-data-section-fields");
        const descSec = document.getElementById("full-data-section-desc");
        const gridContainer = imgSec ? imgSec.parentElement : null;

        if(imgSec) imgSec.style.display = "flex";
        if(fieldsSec) fieldsSec.style.display = "contents";
        if(descSec) descSec.style.display = "block";
        if(gridContainer) {
            gridContainer.style.display = "grid";
            gridContainer.style.gridTemplateColumns = "1fr 1.5fr";
        }

        if(stockControl) stockControl.style.display = "flex";
        stockLabel.innerText = "Cantidad en Stock";
        modalTitle.innerText = "Nueva Entrada";
        modalSubtitle.innerText = "Complete todos los campos del catálogo maestro";
        mainBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Registrar Nuevo Producto';
        mainBtn.style.background = "linear-gradient(135deg, #e53935 0%, #b71c1c 100%)";
        
        const nombreField = document.getElementById("uni-nombre");
        if(nombreField) {
            nombreField.parentElement.style.display = "block";
            nombreField.required = true;
        }

        document.getElementById("upload-placeholder").style.display = "flex";
        document.getElementById("img-preview").style.display = "none";
        document.getElementById("img-preview").src = "";
    } 
    else if (mode === 'adjust') {
        // Modo Ajuste (+/-) - También simplificado
        const imgSec = document.getElementById("full-data-section-img");
        const fieldsSec = document.getElementById("full-data-section-fields");
        const descSec = document.getElementById("full-data-section-desc");
        const gridContainer = imgSec ? imgSec.parentElement : null;

        if(imgSec) imgSec.style.display = "none";
        if(fieldsSec) fieldsSec.style.display = "none";
        if(descSec) descSec.style.display = "none";
        if(gridContainer) {
            gridContainer.style.gridTemplateColumns = "1fr";
            gridContainer.style.display = "block";
        }

        modeInput.value = "adjust";
        idInput.value = id;
        stockControl.style.display = "flex";
        stockLabel.innerText = "Cantidad a Sumar (+/-)";
        modalTitle.innerText = "Ajuste de Existencias";
        modalSubtitle.innerText = "Incremente o reduzca el stock rápidamente";
        mainBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Ajuste';
        mainBtn.style.background = "#27ae60";

        const item = (window.inventarioData || []).find(i => i.id_puro == id);
        if (item) {
            if(document.getElementById("current-stock-badge")) {
                document.getElementById("current-stock-badge").innerText = item.stock;
            }
        }
    }
    else {
        // Modo EDIT (Tuerca) - MOSTRAR TODO
        const imgSec = document.getElementById("full-data-section-img");
        const fieldsSec = document.getElementById("full-data-section-fields");
        const descSec = document.getElementById("full-data-section-desc");
        const gridContainer = imgSec ? imgSec.parentElement : null;

        if(imgSec) imgSec.style.display = "flex";
        if(fieldsSec) fieldsSec.style.display = "contents";
        if(descSec) descSec.style.display = "block";
        if(gridContainer) {
            gridContainer.style.display = "grid";
            gridContainer.style.gridTemplateColumns = "1fr 1.5fr";
        }

        modeInput.value = "update";
        idInput.value = id;
        stockControl.style.display = "none";
        modalTitle.innerText = "Edición de Producto";
        modalSubtitle.innerText = "Modo dinámico de operación maestra";
        mainBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Actualizar Ficha Técnica';
        mainBtn.style.background = "#1e293b";

        const item = (window.inventarioData || []).find(i => i.id_puro == id);
        if (item) {
            document.getElementById("uni-nombre").value = item.pieza || "";
            document.getElementById("uni-precio").value = item.precio || 0;
            document.getElementById("uni-marca").value = item.marca || "";
            document.getElementById("uni-modelo").value = item.modelo || "";
            document.getElementById("uni-descripcion").value = item.descripcion || "";
            
            const preview = document.getElementById("img-preview");
            const placeholder = document.getElementById("upload-placeholder");
            if (item.imagen_url) {
                preview.src = item.imagen_url;
                preview.style.display = "block";
                placeholder.style.display = "none";
            } else {
                preview.style.display = "none";
                placeholder.style.display = "flex";
            }
        }
    }
}

async function abrirAjusteRapido(id) {
    const item = window.inventarioData.find(i => i.id_puro == id);
    if (!item) return;

    let cantidad = prompt(`Sumar stock a: ${item.pieza}\nExistencia actual: ${item.stock}\n\nIngrese la cantidad a SUMAR:`, "1");
    
    if (cantidad !== null && !isNaN(cantidad) && parseInt(cantidad) !== 0) {
        const num = parseInt(cantidad);
        try {
            const response = await fetch(`/almacen/ajustar/${id}/${num}`, { method: 'POST' });
            const result = await response.json();
            if (result.status === "success") {
                item.stock = result.data.nueva_cantidad;
                renderAlmacen(); // Refrescar tabla inmediatamente
            } else {
                alert("Error al ajustar stock: " + result.message);
            }
        } catch (e) {
            alert("Error de conexión.");
        }
    }
}

function changeUnifiedStock(delta) {
    const input = document.getElementById("unified-quantity");
    let val = parseInt(input.value) || 0;
    input.value = val + delta;
}

function submitUnifiedInventory(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const mode = document.getElementById("manage-mode").value;
    const id = document.getElementById("manage-id").value;

    let url = "/almacen/nuevo";
    if (mode === "update" || mode === "adjust") {
        url = mode === "adjust" ? `/almacen/ajustar/${id}` : `/almacen/editar/${id}`;
    }

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === "success" || data.success) {
            alert(data.message || "Operación realizada con éxito");
            location.reload(); 
        } else {
            alert("Error: " + (data.message || "No se pudo procesar la solicitud"));
        }
    })
    .catch(err => {
        console.error(err);
        alert("Error de conexión con el servidor maestro.");
    });
}

async function eliminarPieza(id, nombre) {
    if (!confirm(`¿Estás COMPLETAMENTE SEGURO de eliminar "${nombre}"?\nEsta acción no se puede deshacer.`)) {
        return;
    }

    try {
        const response = await fetch(`/almacen/eliminar/${id}`, { method: 'POST' });
        if (response.ok) {
            window.location.reload();
        } else {
            alert("Error al intentar eliminar el producto.");
        }
    } catch (e) {
        alert("Error de conexión al servidor.");
    }
}

// Unified logic replaces switchTab

function setTipoMovimiento(tipo) {
    const btnEntrada = document.getElementById("btn-tipo-entrada");
    const btnSalida = document.getElementById("btn-tipo-salida");
    const inputTipo = document.getElementById("ent-tipo");

    inputTipo.value = tipo;
    if (tipo === 1) {
        btnEntrada.style.background = "#2ecc71";
        btnEntrada.style.color = "white";
        btnSalida.style.background = "transparent";
        btnSalida.style.color = "#64748b";
    } else {
        btnSalida.style.background = "#e53935";
        btnSalida.style.color = "white";
        btnEntrada.style.background = "transparent";
        btnEntrada.style.color = "#64748b";
    }
}

function editarPieza(id) {
    const item = window.inventarioData.find(i => i.id === id);
    const idPuro = item.id_puro;

    let nuevoPrecio = prompt(`Editando ${item.pieza}.\nIngrese el nuevo precio unitario:`, item.precio);
    
    if (nuevoPrecio !== null && !isNaN(nuevoPrecio)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/almacen/editar/${idPuro}`;
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'precio';
        input.value = nuevoPrecio;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}


// ================================================================
// LÓGICA DE LOGÍSTICA (Envío y Rastreo - MACUIN Enterprise)
// ================================================================

let enviosData = window.logisticaData || [];

function renderLogistica() {
    const list = window.logisticaData || [];
    const tbody = document.getElementById('tabla-logistica-body');
    if (!tbody) return;
    tbody.innerHTML = "";

    const isDark = document.body.classList.contains('theme-dark');
    const textColor = isDark ? '#ffffff' : '#000000';
    const rowBg = isDark ? '#1a1a1a' : '#ffffff';
    const borderColor = isDark ? '#333' : '#f1f5f9';

    list.forEach(envio => {
        let progreso = "5%";
        let badgeColor = "#94a3b8"; 
        let statusText = envio.estado;
        let actions = "";

        const idEst = envio.id_estado;

        if (idEst === 1) {
            progreso = "25%"; badgeColor = "#3498db"; 
            actions = `<button onclick="actualizarEstadoLogistica(${envio.id_pedido}, 2)" class="btn-action" style="background:#e57373; color:white; border:none; padding:10px 18px; border-radius:10px; font-weight:700; cursor:pointer; box-shadow:0 4px 10px rgba(229,115,115,0.3);"><i class="fas fa-box-open"></i> Surtir</button>`;
        } else if (idEst === 2) {
            progreso = "50%"; badgeColor = "#f1c40f"; 
            actions = `<button onclick="actualizarEstadoLogistica(${envio.id_pedido}, 3)" class="btn-action" style="background:#e57373; color:white; border:none; padding:10px 18px; border-radius:10px; font-weight:700; cursor:pointer; box-shadow:0 4px 10px rgba(229,115,115,0.3);"><i class="fas fa-truck-ramp-box"></i> Despachar</button>`;
        } else if (idEst === 3) {
            progreso = "75%"; badgeColor = "#9b59b6"; 
            actions = `<button onclick="actualizarEstadoLogistica(${envio.id_pedido}, 4)" class="btn-action" style="background:#e57373; color:white; border:none; padding:10px 18px; border-radius:10px; font-weight:700; cursor:pointer; box-shadow:0 4px 10px rgba(229,115,115,0.3);"><i class="fas fa-check-double"></i> Entregar</button>`;
        } else if (idEst === 4) {
            progreso = "100%"; badgeColor = "#2ecc71"; 
            actions = `<span style="color:#2ecc71; font-weight:800; font-size:1.1rem;"><i class="fas fa-circle-check"></i> Finalizado</span>`;
        } else {
            progreso = "0%"; badgeColor = "#e74c3c"; 
            actions = `<span style="color:#e74c3c; font-weight:800;"><i class="fas fa-ban"></i> Cancelado</span>`;
        }

        tbody.innerHTML += `
            <tr style="border-bottom: 2px solid ${borderColor}; transition: all 0.2s; background: ${rowBg};">
                <td style="padding: 15px 15px;">
                    <div style="font-weight: 900; color: ${textColor}; font-size: 1rem;">#${envio.guia}</div>
                </td>
                <td style="padding: 15px 15px;">
                    <div style="font-weight: 800; color: ${textColor}; font-size: 0.9rem;">${envio.destino}</div>
                </td>
                <td style="padding: 15px 15px;">
                    <span style="font-size: 0.75rem; color: ${textColor}; font-weight:700;">${envio.courier}</span>
                </td>
                <td style="padding: 15px 15px; width: 120px;">
                    <div style="background:${isDark?'#444':'#f1f5f9'}; border-radius:10px; height:8px; overflow:hidden;">
                        <div style="background: ${badgeColor}; height:100%; width:${progreso}; transition: 0.5s;"></div>
                    </div>
                </td>
                <td style="padding: 15px 15px;">
                    <span style="font-size: 0.75rem; font-weight: 900; color: ${badgeColor}; border: 1px solid ${badgeColor}; padding: 2px 10px; border-radius: 12px; text-transform:uppercase;">${statusText}</span>
                </td>
                <td style="padding: 15px 15px; font-size: 0.85rem; color: ${textColor}; font-weight: 600;">
                    ${envio.fecha || 'Pendiente'}
                </td>
                <td style="padding: 15px 15px; text-align: center;">
                    <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                        <button class="btn-action" onclick="siguienteEtapaLogistica(${envio.id_pedido}, ${idEst})" title="Siguiente Etapa" style="background:#2c3e50; color:white; border:none; padding:8px; border-radius:8px; cursor:pointer;"><i class="fas fa-arrow-right"></i></button>
                        <button class="btn-action" onclick="abrirAsignarPaqueteriaRapida(${envio.id_pedido})" title="Asignar Paquetería" style="background:#3498db; color:white; border:none; padding:8px; border-radius:8px; cursor:pointer;"><i class="fas fa-truck-fast"></i></button>
                        <button class="icon-edit" onclick="verDetalleOrden('${envio.id_puro}')" title="Ver Pedido" style="background:none; border: 1px solid ${borderColor}; color:${textColor}; padding:8px; border-radius:8px; cursor:pointer;"><i class="fas fa-eye"></i></button>
                        <button class="icon-edit" onclick="abrirEditarPedido('${envio.id_puro}')" title="Editar Pedido" style="background:none; border: 1px solid ${borderColor}; color:${textColor}; padding:8px; border-radius:8px; cursor:pointer;"><i class="fas fa-edit"></i></button>
                        <button class="icon-delete" onclick="eliminarVenta('${envio.id_puro}')" title="Eliminar Pedido" style="background:none; border: 1px solid ${borderColor}; color:#e57373; padding:8px; border-radius:8px; cursor:pointer;"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </td>
            </tr>
        `;
    });
    actualizarKPIsLogistica();
}

function filtrarLogistica() {
    const filter       = (document.getElementById("buscador-logistica")?.value || "").toLowerCase();
    const statusFilter = (document.getElementById("filtro-estatus-l")?.value || "").toLowerCase();
    const dateFilter   = document.getElementById("filtro-fecha-l")?.value || "";
    const paqFilter    = (document.getElementById("filtro-paqueteria-l")?.value || "").toLowerCase();

    const tbody = document.getElementById("tabla-logistica-body");
    if (!tbody) return;
    const trs = tbody.getElementsByTagName("tr");

    for (let i = 0; i < trs.length; i++) {
        const tr = trs[i];
        const rowText   = tr.textContent.toLowerCase();
        // columns: 0=Guía, 1=Destinatario, 2=Paquetería, 3=Progreso, 4=Estado, 5=Entrega Est.
        const cellStatus = (tr.cells[4]?.innerText || "").toLowerCase();
        const cellDate   = (tr.cells[5]?.innerText || "").trim();
        const cellPaq    = (tr.cells[2]?.innerText || "").toLowerCase();

        const matchText   = rowText.includes(filter);
        const matchStatus = statusFilter === "" || cellStatus.includes(statusFilter);
        const matchDate   = dateFilter   === "" || cellDate.includes(dateFilter);
        const matchPaq    = paqFilter    === "" || cellPaq.includes(paqFilter);

        tr.style.display = (matchText && matchStatus && matchDate && matchPaq) ? "" : "none";
    }
}

async function actualizarEstadoLogistica(idPedido, nuevoEstado) {
    if (!idPedido) { alert("Error: ID de pedido no válido."); return; }
    const labels = {
        2: "RECIBIDO Y EN PREPARACIÓN",
        3: "DESPACHADO Y ENVIADO",
        4: "ENTREGADO FINAL"
    };
    
    if (confirm(`¿Mover el pedido #${idPedido} a: ${labels[nuevoEstado]}?`)) {
        try {
            const formData = new FormData();
            formData.append('id_estado', nuevoEstado);
            
            const response = await fetch(`/ventas/editar/${idPedido}`, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.status === "success") {
                alert(`¡Operación Exitosa!\nEtapa de logística actualizada correctamente.`);
                location.reload();
            } else {
                alert("Error: " + result.message);
            }
        } catch (e) {
            alert("Error de conexión al procesar el flujo logístico.");
        }
    }
}

async function siguienteEtapaLogistica(idPedido, currentStatus) {
    // Validar paquetería obligatoria (Requerimiento Usuario)
    const envioObj = (window.logisticaData || []).find(e => e.id_pedido == idPedido);
    if (!envioObj || !envioObj.courier || envioObj.courier.trim() === "" || envioObj.courier === "Pendiente" || envioObj.courier.includes("--")) {
        alert("¡ATENCIÓN! Es obligatorio asignar una paquetería real antes de avanzar el estado del pedido.");
        abrirAsignarPaqueteriaRapida(idPedido);
        return;
    }

    if (currentStatus >= 4) { alert("El pedido ya está finalizado."); return; }
    const nextStatus = currentStatus + 1;
    await actualizarEstadoLogistica(idPedido, nextStatus);
}

async function abrirAsignarPaqueteriaRapida(idPedido) {
    const modal = document.getElementById('modalAsignarCourier');
    if (!modal) return;
    
    document.getElementById('courier-id-pedido').value = idPedido;
    
    // Tratamos de encontrar si ya tiene paquetería
    const envioObj = (window.logisticaData || []).find(e => e.id_pedido == idPedido);
    if (envioObj && envioObj.courier) {
        const select = document.getElementById('courier-seleccion');
        // Solo asignamos si existe en las opciones, si no que quede el placeholder
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value === envioObj.courier) {
                select.selectedIndex = i;
                break;
            }
        }
    }
    
    modal.style.display = "flex";
}

async function guardarPaqueteriaRapida() {
    const idPedido = document.getElementById('courier-id-pedido').value;
    const paqueteria = document.getElementById('courier-seleccion').value;

    if (!paqueteria) {
        alert("Por favor selecciona una paquetería.");
        return;
    }

    console.log(`📤 Enviando actualización JSON: Pedido #${idPedido} -> ${paqueteria}`);

    try {
        const response = await fetch(`/logistica/asignar_paqueteria/${idPedido}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ paqueteria: paqueteria })
        });
        const result = await response.json();
        
        if (result.status === "success") {
            console.log("✅ Persistencia confirmada por el servidor.");
            document.getElementById('modalAsignarCourier').style.display = 'none';
            // Recarga para reflejar cambios en la tabla
            location.reload();
        } else {
            console.error("❌ Fallo en persistencia:", result.message);
            alert("Error: " + result.message);
        }
    } catch (e) {
        console.error("❌ Error de red/servidor:", e);
        alert("Error de conexión al asignar paquetería.");
    }
}

function abrirModalLogistica() {
    const modal = document.getElementById("modalLogistica");
    if(modal) modal.style.display = "flex";
}

function cerrarModalLogistica() {
    const modal = document.getElementById("modalLogistica");
    if(modal) modal.style.display = "none";
}

async function guardarPaqueteria() {
    const nombre = document.getElementById('paq-nombre').value;
    const web = document.getElementById('paq-web').value;
    const tel = document.getElementById('paq-tel').value;

    if (!nombre) { alert("El nombre es obligatorio."); return; }
    
    try {
        const response = await fetch('/logistica/paqueteria', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, web, tel })
        });
        const result = await response.json();
        
        if (result.status === "success") {
            alert(`¡Aliado Logístico "${nombre}" registrado correctamente!`);
            cerrarModalLogistica();
            location.reload(); 
        } else {
            alert("Error: " + result.message);
        }
    } catch (e) {
        alert("Error de conexión al registrar la paquetería.");
    }
}

async function abrirEditarPedido(id) {
    const modal = document.getElementById("modalEditarPedido");
    if (!modal) return;
    
    // Iniciar loader o limpiar
    if(document.getElementById("edit-folio-title")) document.getElementById("edit-folio-title").innerText = "Cargando...";
    if(document.getElementById("edit-id-puro")) document.getElementById("edit-id-puro").value = id;

    modal.style.display = "flex";

    try {
        const response = await fetch(`/ventas/detalle/${id}`);
        const data = await response.json();

        if (data && (data.id_pedido || data.id)) {
            if(document.getElementById('edit-folio-title')) document.getElementById('edit-folio-title').innerText = data.id_pedido || data.id;
            
            // Llenar campos de envío
            const envio = data.envio || data;
            if(document.getElementById('edit-direccion')) document.getElementById('edit-direccion').value = envio.direccion || "";
            if(document.getElementById('edit-ciudad')) document.getElementById('edit-ciudad').value = envio.ciudad || "";
            if(document.getElementById('edit-cp')) document.getElementById('edit-cp').value = envio.codigo_postal || "";
            if(document.getElementById('edit-telefono')) document.getElementById('edit-telefono').value = envio.telefono_contacto || envio.telefono || "";
            if(document.getElementById('edit-notas')) document.getElementById('edit-notas').value = envio.notas || "";
            
            // Llenar datos expanded
            const user = data.usuario || {};
            if (document.getElementById('edit-cliente-nombre')) {
                document.getElementById('edit-cliente-nombre').value = `${user.nombre || ""} ${user.apellido_paterno || ""}`.trim();
            }
            if (document.getElementById('edit-paqueteria')) {
                document.getElementById('edit-paqueteria').value = envio.paqueteria || "MACUIN Fleet Management";
            }

            // Llenar productos
            const container = document.getElementById('edit-productos-container');
            if (container) {
                container.innerHTML = "";
                const productos = data.detalles || [];
                if (productos.length === 0) {
                    container.innerHTML = '<p style="font-size:0.75rem; color:#999;">Sin productos registrados</p>';
                } else {
                    productos.forEach(p => {
                        container.innerHTML += `<div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:5px; border-bottom:1px solid #eee; padding-bottom:2px; color: #000;">
                            <span>${p.producto ? p.producto.nombre_producto : 'Producto #'+p.id_producto}</span>
                            <span style="font-weight:700;">x${p.cantidad}</span>
                        </div>`;
                    });
                }
            }

            // Llenar estado
            document.getElementById('edit-estado').value = data.id_estado;

            modal.style.display = "flex";
        } else {
            alert("No se pudieron cargar los datos del pedido.");
        }
    } catch (e) {
        console.error(e);
        alert("Error al intentar cargar los detalles del pedido.");
    }
}

function guardarDespacho() {
    const cliente = document.getElementById("log-cliente").value;
    const courier = document.getElementById("log-courier").value;
    const fecha = document.getElementById("log-fecha").value;
    const guia = "TRK-" + Math.floor(Math.random() * 9000 + 1000);

    if(!cliente || !fecha) {
        alert("⚠️ Por favor completa el destino y la fecha estimada.");
        return;
    }

    enviosData.unshift({
        guia: guia,
        cliente: cliente,
        courier: courier,
        status: "Preparando",
        progreso: 15,
        fecha: fecha
    });

    cerrarModalLogistica();
    renderLogistica();
    document.getElementById("formLogistica").reset();
}

function actualizarEstatus(guia) {
    const envio = enviosData.find(e => e.guia === guia);
    if (!envio) return;

    if (envio.status === "Preparando") {
        envio.status = "En Tránsito";
        envio.progreso = 50;
    } else if (envio.status === "En Tránsito") {
        envio.status = "Entregado";
        envio.progreso = 100;
    } else {
        alert("Este pedido ya ha sido entregado satisfactoriamente.");
    }

    renderLogistica();
}

function rastrearPaquete(guia) {
    alert(`🛰️ Conectando con GPS...\n\nEl envío ${guia} se encuentra actualmente en tránsito hacia su destino. \nPróxima parada: Centro de Distribución Norte.`);
}

function actualizarKPIsLogistica() {
    // Si el backend envió KPIs pre-calculados, usarlos (Phase 8 Expansion)
    const k = window.kpisLogistica;
    if (k && Object.keys(k).length > 0) {
        // Globales
        if(document.getElementById("kpi-transito")) document.getElementById("kpi-transito").innerText = k.transito || 0;
        if(document.getElementById("kpi-entregados-mes")) document.getElementById("kpi-entregados-mes").innerText = k.completos || 0;
        if(document.getElementById("kpi-cancelados-total")) document.getElementById("kpi-cancelados-total").innerText = k.total_cancelados || 0;

        // Desglose
        if(document.getElementById("kpi-recibido")) document.getElementById("kpi-recibido").innerText = k.recibido || 0;
        if(document.getElementById("kpi-surtido")) document.getElementById("kpi-surtido").innerText = k.surtido || 0;
        if(document.getElementById("kpi-enviado")) document.getElementById("kpi-enviado").innerText = k.transito || 0;
        if(document.getElementById("kpi-completado")) document.getElementById("kpi-completado").innerText = k.completos || 0;
        if(document.getElementById("kpi-cancelado")) document.getElementById("kpi-cancelado").innerText = k.cancelados || 0;
        return;
    }

    if (!document.getElementById("kpi-transito")) return;
    
    let transito = 0, pendientes = 0, entregadosMes = 0, entregadosTotal = 0, canceladosMes = 0, canceladosTotal = 0;
    const hoy = getFechaLocal();
    const mesActual = hoy.substring(0, 7);

    (window.logisticaData || []).forEach(envio => {
        const idEst = parseInt(envio.id_estado);
        if (idEst === 3) transito++;
        if (idEst === 1 || idEst === 2) pendientes++;
        
        if (idEst === 4 || envio.estado === "Finalizado" || envio.estado === "Entregado") {
            entregadosTotal++;
            if (envio.fecha && envio.fecha.startsWith(mesActual)) entregadosMes++;
        }
        
        if (idEst === 5 || envio.estado === "Cancelado") {
            canceladosTotal++;
            if (envio.fecha && envio.fecha.startsWith(mesActual)) canceladosMes++;
        }
    });

    document.getElementById("kpi-transito").innerText = transito;
    document.getElementById("kpi-pendientes").innerText = pendientes;
    document.getElementById("kpi-entregados-mes").innerText = entregadosMes;
    document.getElementById("kpi-entregados-total").innerText = entregadosTotal;
    document.getElementById("kpi-cancelados-mes").innerText = canceladosMes;
    document.getElementById("kpi-cancelados-total").innerText = canceladosTotal;
}



// ================================================================
// LÓGICA DE PERFIL Y CREDENCIALES (MACUIN Enterprise)
// ================================================================

function actualizarCredenciales() {
    const email = document.getElementById('perfil-email').value;
    const passActual = document.getElementById('pass-actual').value;
    const passNueva = document.getElementById('pass-nueva').value;
    const passConfirmar = document.getElementById('pass-confirmar').value;

    if (passNueva || passConfirmar || passActual) {
        if (!passActual) {
            alert("Seguridad: Debes ingresar tu contraseña actual para autorizar el cambio.");
            return;
        }
        if (passNueva !== passConfirmar) {
            alert("Error: Las contraseñas nuevas no coinciden. Por favor, revísalas.");
            return;
        }
    }

    alert(`Éxito: Credenciales actualizadas correctamente para ${email}.`);
    
    document.getElementById('pass-actual').value = "";
    document.getElementById('pass-nueva').value = "";
    document.getElementById('pass-confirmar').value = "";
}


// ================================================================
// PANEL SUPER ADMIN (Gestión Multi-Empresa)
// ================================================================

let empresasData = [
    { id: "EMP-001", nombre: "Taller Mecánico Los Hermanos", plan: "Pro", usuarios: 4, estado: "Activo" },
    { id: "EMP-002", nombre: "Refaccionaria Mendoza", plan: "Enterprise", usuarios: 12, estado: "Activo" },
    { id: "EMP-003", nombre: "Servicio Automotriz Rayo", plan: "Básico", usuarios: 2, estado: "Suspendido (Falta de Pago)" }
];

function renderSuperAdmin() {
    const tbody = document.getElementById("tabla-empresas-body");
    if (!tbody) return;
    tbody.innerHTML = "";

    let totalUsuarios = 0;

    empresasData.forEach(empresa => {
        totalUsuarios += empresa.usuarios;
        let statusClass = empresa.estado === "Activo" ? "badge-success" : "badge-danger";
        
        tbody.innerHTML += `
            <tr>
                <td style="color: #000000; font-weight: 800;">${empresa.id}</td>
                <td style="color: #000000;">${empresa.nombre}</td>
                <td><span class="tag-categoria" style="background: transparent; color: #000; border: none; font-weight: 800;">${empresa.plan}</span></td>
                <td style="color: #000000;"><i class="fas fa-users" style="color:#000000; opacity:0.6;"></i> ${empresa.usuarios}</td>
                <td><span class="badge ${statusClass}">${empresa.estado}</span></td>
                <td class="row-actions">
                    <button class="icon-view" onclick="alert('Iniciando sesión remota...')" title="Inspeccionar" style="color: #000000; border: 1px solid #ddd; border-radius: 4px; background: none; padding: 6px;"><i class="fas fa-sign-in-alt"></i></button>
                    <button class="icon-edit" onclick="alert('Configurando roles...')" title="Roles" style="color: #000000; border: 1px solid #ddd; border-radius: 4px; background: none; padding: 6px;"><i class="fas fa-cogs"></i></button>
                    <button class="icon-delete" onclick="alert('Suspendiendo servicio...')" title="Suspender" style="color: #000000; border: 1px solid #ddd; border-radius: 4px; background: none; padding: 6px;"><i class="fas fa-ban"></i></button>
                </td>
            </tr>
        `;
    });

    if(document.getElementById("kpi-empresas")) {
        document.getElementById("kpi-empresas").innerText = empresasData.length;
        document.getElementById("kpi-usuarios-global").innerText = totalUsuarios;
    }
}

function filtrarEmpresas() {
    let filter = document.getElementById("buscador-empresas").value.toLowerCase();
    let rows = document.getElementById("tabla-empresas-body").getElementsByTagName("tr");
    for (let row of rows) {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    }
}


// =========================================
// LÓGICA DEL PANEL SUPER ADMIN REDISEÑADO
// =========================================
let empresasActivas = ["Público General", "MACUIN Central", "Refaccionaria Mendoza", "Taller Los Hermanos"];

let usuariosGlobales = window.usuariosGlobales || [
    { id: "USR-001", nombre: "Carlos Mendoza", origen: "Refaccionaria Mendoza", rol: "Empresa", estado: "Activo" },
    { id: "USR-002", nombre: "Ana López", origen: "MACUIN Central", rol: "Trabajador", estado: "Activo" },
    { id: "USR-003", nombre: "Luis Rayo", origen: "Público General", rol: "Cliente", estado: "Suspendido" },
    { id: "USR-004", nombre: "Eduardo Castillo", origen: "MACUIN Central", rol: "Trabajador", estado: "Activo" }
];

let solicitudesEmpresas = [
    { id: "REQ-101", nombre: "AutoPartes del Norte", contacto: "norte@ejemplo.com", estado: "Pendiente" },
    { id: "REQ-102", nombre: "Mecánica Rápida Sur", contacto: "sur@ejemplo.com", estado: "Pendiente" }
];

let usuarioEnEdicion = null;

function renderTablas() {
    renderTablaUsuarios();
    renderTablaSolicitudes();
    actualizarListasEmpresas();
}

function renderTablaUsuarios() {
    const tbody = document.getElementById('tabla-body');
    if (!tbody) return; 
    
    tbody.innerHTML = '';
    let usuariosActivosCount = 0;

    usuariosGlobales.forEach(usr => {
        const badgeClass = usr.estado === 'Activo' ? 'badge-act' : 'badge-sus';
        if (usr.estado === 'Activo') usuariosActivosCount++;

        let iconoOrigen = "fa-user";
        if (usr.rol === "Empresa") iconoOrigen = "fa-building";
        if (usr.rol === "Trabajador") iconoOrigen = "fa-id-badge";

        tbody.innerHTML += `
            <tr>
                <td><strong>${usr.id}</strong></td>
                <td>${usr.nombre}</td>
                <td><i class="fas ${iconoOrigen}" style="color:#7f8c8d; font-size: 0.8rem; margin-right: 5px;"></i> ${usr.origen}</td>
                <td><strong>${usr.rol}</strong></td>
                <td><span class="${badgeClass}">${usr.estado}</span></td>
                <td>
                    <button style="background:none; border:none; color:#3498db; cursor:pointer; font-size: 1.1rem; margin-right: 15px;" title="Editar" onclick="abrirModalEditar('${usr.id}')"><i class="fas fa-edit"></i></button>
                    <button style="background:none; border:none; color:${usr.estado === 'Activo' ? '#e74c3c' : '#2ecc71'}; cursor:pointer; font-size: 1.1rem;" title="${usr.estado === 'Activo' ? 'Suspender' : 'Activar'}" onclick="suspenderUsuario(${usr.id_puro})">
                        <i class="fas ${usr.estado === 'Activo' ? 'fa-user-slash' : 'fa-user-check'}"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    const kpiElement = document.getElementById('kpi-usuarios');
    if (kpiElement) kpiElement.innerText = usuariosActivosCount;
    
    const kpiEmpresas = document.getElementById('kpi-empresas');
    if (kpiEmpresas) kpiEmpresas.innerText = empresasActivas.length > 2 ? empresasActivas.length - 2 : 0;
}

function renderTablaSolicitudes() {
    const tbody = document.getElementById('tabla-solicitudes-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    solicitudesEmpresas.forEach(sol => {
        tbody.innerHTML += `
            <tr>
                <td><strong>${sol.id}</strong></td>
                <td>${sol.nombre}</td>
                <td>${sol.contacto}</td>
                <td><span class="badge-pen">${sol.estado}</span></td>
                <td>
                    <button class="btn-accept" title="Aceptar Empresa" onclick="aceptarSolicitud('${sol.id}')"><i class="fas fa-check-circle"></i></button>
                    <button class="btn-reject" title="Rechazar Empresa" onclick="rechazarSolicitud('${sol.id}')"><i class="fas fa-times-circle"></i></button>
                </td>
            </tr>
        `;
    });
}

function actualizarListasEmpresas() {
    const selectNuevo = document.getElementById('nuevo-origen');
    const selectEdit = document.getElementById('edit-origen');
    if(!selectNuevo || !selectEdit) return;

    let opciones = "";
    empresasActivas.forEach(emp => {
        opciones += `<option value="${emp}">${emp}</option>`;
    });
    
    selectNuevo.innerHTML = opciones;
    selectEdit.innerHTML = opciones;
}

function abrirModalUsuariosMaster() { document.getElementById('modalUsuario').style.display = 'flex'; }
function cerrarModalUsuariosMaster() { document.getElementById('modalUsuario').style.display = 'none'; }

function guardarUsuarioMaster() {
    const nombre = document.getElementById('nuevo-nombre').value;
    if(!nombre) { alert("Ingresa el nombre."); return; }

    usuariosGlobales.push({ 
        id: "USR-00" + (usuariosGlobales.length + 1), 
        nombre: nombre, 
        origen: document.getElementById('nuevo-origen').value, 
        rol: document.getElementById('nuevo-rol').value, 
        estado: "Activo" 
    });

    document.getElementById('nuevo-nombre').value = '';
    cerrarModalUsuariosMaster();
    renderTablas();
}

function changeUnifiedStock(val) {
    const input = document.getElementById("unified-quantity");
    const current = parseInt(input.value) || 0;
    input.value = current + val;
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("img-preview").src = e.target.result;
            document.getElementById("img-preview").style.display = "block";
            document.getElementById("upload-placeholder").style.display = "none";
        };
        reader.readAsDataURL(input.files[0]);
    }
}


function cerrarMasterModal() {
    document.getElementById('modalAlmacen').style.display = 'none';
}

function abrirModalEditar(id) {
    const usuario = usuariosGlobales.find(u => u.id === id);
    if(usuario) {
        usuarioEnEdicion = id;
        document.getElementById('edit-nombre').value = usuario.nombre;
        document.getElementById('edit-rol').value = usuario.rol;
        document.getElementById('edit-origen').value = usuario.origen;
        document.getElementById('modalEditarUsuario').style.display = 'flex';
    }
}

function cerrarModalEditar() { 
    document.getElementById('modalEditarUsuario').style.display = 'none'; 
    usuarioEnEdicion = null; 
}

function guardarEdicionUsuario() {
    if(usuarioEnEdicion) {
        const usuario = usuariosGlobales.find(u => u.id === usuarioEnEdicion);
        usuario.nombre = document.getElementById('edit-nombre').value;
        usuario.rol = document.getElementById('edit-rol').value;
        usuario.origen = document.getElementById('edit-origen').value;
        
        cerrarModalEditar();
        renderTablas();
    }
}

function suspenderUsuario(id_puro) {
    if(confirm("¿Cambiar el estado de este usuario?")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/superadmin/toggle/${id_puro}`;
        document.body.appendChild(form);
        form.submit();
    }
}

function abrirModalEmpresa() { document.getElementById('modalEmpresa').style.display = 'flex'; }
function cerrarModalEmpresa() { document.getElementById('modalEmpresa').style.display = 'none'; }

function aceptarSolicitud(id) {
    const index = solicitudesEmpresas.findIndex(s => s.id === id);
    if(index !== -1) {
        const empresaAceptada = solicitudesEmpresas[index].nombre;
        empresasActivas.push(empresaAceptada); 
        solicitudesEmpresas.splice(index, 1); 
        renderTablas();
        alert(`¡Empresa aceptada! "${empresaAceptada}" ya aparece en el seleccionador.`);
    }
}

function rechazarSolicitud(id) {
    if(confirm("¿Seguro que deseas rechazar esta solicitud? Se eliminará de la tabla.")) {
        solicitudesEmpresas = solicitudesEmpresas.filter(s => s.id !== id);
        renderTablas();
    }
}

// ================================================================
// INICIALIZACIÓN GLOBAL INTELIGENTE (CARGADOR MAESTRO)
// ================================================================
window.onload = function() {
    if (document.getElementById("tabla-ventas-body") && typeof renderTabla === 'function') {
        renderTabla();
        poblarCategoriasVentas();
    }
    if (document.getElementById("tabla-almacen-body") && typeof renderAlmacen === 'function') {
        renderAlmacen();
        poblarCategoriasAlmacen();
    }
    if (document.getElementById("tabla-logistica-body") && typeof renderLogistica === 'function') {
        renderLogistica();
    }
    if (document.getElementById("tabla-empresas-body") && typeof renderSuperAdmin === 'function') {
        renderSuperAdmin();
    }
    if (document.getElementById("tabla-body") && typeof renderTablas === 'function') {
        renderTablas();
    }
    // ── Init Notifications ──
    initNotifications();
};

/**
 * Rellena el dropdown de categorías en la vista de Ventas.
 * En ventas el inventario se expone como window.inventarioGlobal (no inventarioData).
 */
function poblarCategoriasVentas() {
    const select = document.getElementById('filtro-categoria-v');
    if (!select) return;
    // ventas.html pasa el inventario como inventarioGlobal; almacen.html lo pasa como inventarioData
    const source = window.inventarioGlobal || window.inventarioData || [];
    const cats = [...new Set(source.map(i => i.categoria || i.nombre_categoria).filter(Boolean))].sort();
    cats.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat.toLowerCase();
        opt.textContent = cat;
        select.appendChild(opt);
    });
}

/**
 * Rellena el dropdown de categorías en la vista de Almacén
 * usando las categorías únicas de inventarioData.
 */
function poblarCategoriasAlmacen() {
    const select = document.getElementById('filtro-categoria-a');
    if (!select) return;
    const cats = [...new Set((window.inventarioData || []).map(i => i.categoria).filter(Boolean))].sort();
    cats.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat.toLowerCase();
        opt.textContent = cat;
        select.appendChild(opt);
    });
}

async function initNotifications() {
    const badge = document.getElementById("notif-badge");
    if (!badge) return;

    try {
        const response = await fetch('/v1/reportes/dashboard'); // Mock or real endpoint through Flask proxy if needed
        const data = await response.json();
        
        // Si hay pedidos pendientes (status < 3)
        const pendientes = data.pendientes_envio || 0;
        if (pendientes > 0) {
            badge.innerText = `${pendientes} NUEVOS`;
            badge.style.display = "block";
            
            // Si estamos en la home, animar la tarjeta de logística
            const card = document.getElementById("logistica-card");
            if (card) {
                card.style.border = "2px solid #e57373";
                card.style.boxShadow = "0 0 15px rgba(229,115,115,0.3)";
            }
        }
    } catch (e) {
        console.warn("Notifications check failed (Check API connectivity)");
    }
}

