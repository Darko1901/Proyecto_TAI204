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
let ventasData = [
    { id: "V-1024", cliente: "Taller Mecánico Los Hermanos", piezas: "Balatas Delanteras (BAL-001)", fecha: getFechaLocal(), total: 1250.00, estatus: "Completado" },
    { id: "V-1025", cliente: "Refaccionaria El Pistón", piezas: "Amortiguadores (AMORT-02)", fecha: getFechaLocal(), total: 3400.00, estatus: "Procesando" },
    { id: "V-1026", cliente: "Juan Pérez", piezas: "Filtro de Aceite (FIL-09)", fecha: "2026-02-25", total: 450.00, estatus: "Cancelado" },
    { id: "V-1027", cliente: "Frenos del Norte", piezas: "Discos de Freno (DIS-04)", fecha: "2026-02-02", total: 5200.00, estatus: "Completado" }
];

// Dibuja la tabla de ventas en el HTML
function renderTabla() {
    const tbody = document.getElementById("tabla-ventas-body");
    if (!tbody) return;
    
    tbody.innerHTML = ""; 
    
    ventasData.forEach(venta => {
        let badgeClass = venta.estatus === "Completado" ? "badge-success" : 
                         venta.estatus === "Procesando" ? "badge-warning" : "badge-danger";
                         
        tbody.innerHTML += `
            <tr>
                <td><strong>#${venta.id}</strong></td>
                <td>${venta.cliente}</td>
                <td>${venta.piezas}</td>
                <td>${venta.fecha}</td>
                <td>$${venta.total.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td><span class="badge ${badgeClass}">${venta.estatus}</span></td>
                <td class="row-actions">
                    <button class="icon-view" onclick="verVenta('${venta.id}')" title="Ver"><i class="fas fa-eye"></i></button>
                    <button class="icon-edit" onclick="editarVenta('${venta.id}')" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="icon-delete" onclick="eliminarVenta('${venta.id}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    });
    
    actualizarKPIs(); 
}

// Calcula los totales de las tarjetas superiores
function actualizarKPIs() {
    if (!document.getElementById("kpi-dia")) return;

    let totalDia = 0, totalMes = 0, completados = 0, pendientes = 0;
    const hoy = getFechaLocal();
    const mesActual = hoy.substring(0, 7); 

    ventasData.forEach(venta => {
        if (venta.estatus !== "Cancelado") {
            if (venta.fecha === hoy) totalDia += venta.total;
            if (venta.fecha.startsWith(mesActual)) totalMes += venta.total;
        }
        if (venta.estatus === "Completado") completados++;
        if (venta.estatus === "Procesando") pendientes++;
    });

    document.getElementById("kpi-dia").innerText = `$${totalDia.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    document.getElementById("kpi-mes").innerText = `$${totalMes.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    document.getElementById("kpi-completados").innerText = completados;
    document.getElementById("kpi-pendientes").innerText = pendientes;
}

// ================================================================
// 3. ACCIONES CRUD Y MODALES
// ================================================================

function abrirModal(creandoNuevo = true) {
    const modal = document.getElementById("modalVenta");
    if(!modal) return;
    modal.style.display = "flex";
    if (creandoNuevo) {
        modoEdicion = false;
        document.getElementById("formVenta").reset();
        document.getElementById("venta-id").value = "V-" + Math.floor(Math.random() * 9000 + 1000);
        document.getElementById("modal-titulo").innerText = "Registrar Nueva Venta";
    }
}

function cerrarModal() {
    document.getElementById("modalVenta").style.display = "none";
}

function guardarVenta() {
    const id = document.getElementById("venta-id").value;
    const cliente = document.getElementById("venta-cliente").value;
    const piezas = document.getElementById("venta-piezas").value;
    const total = parseFloat(document.getElementById("venta-total").value);
    const estatus = document.getElementById("venta-estatus").value;
    const fecha = getFechaLocal(); 

    if (!cliente || !piezas || isNaN(total)) {
        alert("Por favor llena todos los campos correctamente.");
        return;
    }

    if (modoEdicion) {
        const index = ventasData.findIndex(v => v.id === id);
        ventasData[index] = { ...ventasData[index], cliente, piezas, total, estatus };
    } else {
        ventasData.unshift({ id, cliente, piezas, fecha, total, estatus });
    }

    cerrarModal();
    renderTabla();
}

function eliminarVenta(id) {
    if (confirm(`¿Estás seguro de eliminar la venta #${id}?`)) {
        ventasData = ventasData.filter(v => v.id !== id);
        renderTabla();
    }
}

function editarVenta(id) {
    modoEdicion = true;
    const venta = ventasData.find(v => v.id === id);
    document.getElementById("modal-titulo").innerText = "Editar Venta #" + id;
    document.getElementById("venta-id").value = venta.id;
    document.getElementById("venta-cliente").value = venta.cliente;
    document.getElementById("venta-piezas").value = venta.piezas;
    document.getElementById("venta-total").value = venta.total;
    document.getElementById("venta-estatus").value = venta.estatus;
    document.getElementById("modalVenta").style.display = "flex";
}

function verVenta(id) {
    const v = ventasData.find(venta => venta.id === id);
    alert(`DETALLE DE VENTA\nFolio: ${v.id}\nCliente: ${v.cliente}\nPiezas: ${v.piezas}\nFecha: ${v.fecha}\nTotal: $${v.total.toFixed(2)}\nEstatus: ${v.estatus}`);
}

// ================================================================
// 4. FILTROS Y EXPORTACIÓN
// ================================================================

function filtrarTabla() {
    let filter = document.getElementById("buscador").value.toLowerCase();
    let tbody = document.getElementById("tabla-ventas-body");
    if(!tbody) return;
    let tr = tbody.getElementsByTagName("tr");

    for (let i = 0; i < tr.length; i++) {
        let textValue = tr[i].innerText;
        tr[i].style.display = textValue.toLowerCase().indexOf(filter) > -1 ? "" : "none";
    }
}

function descargarPDF() {
    const elemento = document.getElementById('panel-ventas');
    if (!elemento) return;
    
    const botones = document.querySelectorAll('.row-actions, .toolbar button');
    botones.forEach(btn => btn.style.display = 'none'); 

    const opciones = {
        margin: 10,
        filename: 'Reporte_Ventas_MACUIN.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };

    if (typeof html2pdf !== 'undefined') {
        html2pdf().set(opciones).from(elemento).save().then(() => {
            botones.forEach(btn => btn.style.display = ''); 
        });
    } else {
        alert("Librería PDF no disponible.");
        botones.forEach(btn => btn.style.display = '');
    }
}

// ================================================================
// 5. INICIALIZACIÓN
// ================================================================

window.onload = function() {
    renderTabla();
};


// ================================================================
// MÓDULO DE ALMACÉN (Inventario Inteligente)
// ================================================================

let inventarioData = [
    { id: "SKU-9901", pieza: "Alternador Bosch", categoria: "Eléctrico", pasillo: "A-1", stock: 5, min: 10, precio: 2400 },
    { id: "SKU-5520", pieza: "Bomba de Agua", categoria: "Motor", pasillo: "B-3", stock: 25, min: 15, precio: 850 },
    { id: "SKU-1122", pieza: "Aceite Sintético 5W30", categoria: "Lubricantes", pasillo: "C-2", stock: 100, min: 20, precio: 180 },
    { id: "SKU-8844", pieza: "Radiador de Aluminio", categoria: "Enfriamiento", pasillo: "A-4", stock: 3, min: 5, precio: 1950 }
];

function renderAlmacen() {
    const tbody = document.getElementById("tabla-almacen-body");
    if (!tbody) return;
    tbody.innerHTML = "";

    inventarioData.forEach(item => {
        // Lógica de Alerta de Stock
        let stockStatus = item.stock <= item.min ? 'badge-danger' : 'badge-success';
        let stockIcon = item.stock <= item.min ? '<i class="fas fa-exclamation-triangle"></i> ' : '';

        tbody.innerHTML += `
            <tr>
                <td><strong>${item.id}</strong></td>
                <td>${item.pieza}</td>
                <td><span class="tag-categoria">${item.categoria}</span></td>
                <td><i class="fas fa-map-marker-alt"></i> ${item.pasillo}</td>
                <td><span class="badge ${stockStatus}">${stockIcon}${item.stock} unidades</span></td>
                <td>$${item.precio.toLocaleString()}</td>
                <td class="row-actions">
                    <button class="icon-edit" onclick="ajustarStock('${item.id}', 1)" title="Entrada"><i class="fas fa-plus-circle"></i></button>
                    <button class="icon-delete" onclick="ajustarStock('${item.id}', -1)" title="Salida"><i class="fas fa-minus-circle"></i></button>
                    <button class="icon-view" onclick="editarPieza('${item.id}')"><i class="fas fa-cog"></i></button>
                </td>
            </tr>
        `;
    });
    actualizarKPIsAlmacen();
}

function actualizarKPIsAlmacen() {
    if (!document.getElementById("kpi-total-piezas")) return;
    
    let totalPiezas = inventarioData.reduce((acc, item) => acc + item.stock, 0);
    let valorInventario = inventarioData.reduce((acc, item) => acc + (item.stock * item.precio), 0);
    let stockBajo = inventarioData.filter(item => item.stock <= item.min).length;

    document.getElementById("kpi-total-piezas").innerText = totalPiezas;
    document.getElementById("kpi-valor-total").innerText = `$${valorInventario.toLocaleString()}`;
    document.getElementById("kpi-stock-bajo").innerText = stockBajo;
}

function ajustarStock(id, cantidad) {
    const item = inventarioData.find(i => i.id === id);
    if(item.stock + cantidad < 0) return alert("No hay suficiente stock para esta salida.");
    item.stock += cantidad;
    renderAlmacen();
}

// ================================================================
// BUSCADOR UNIVERSAL (Sirve para Ventas y Almacén)
// ================================================================
function filtrarAlmacen() {
    let input = document.getElementById("buscador-almacen");
    let filter = input.value.toLowerCase();
    let tbody = document.getElementById("tabla-almacen-body");
    let tr = tbody.getElementsByTagName("tr");

    for (let i = 0; i < tr.length; i++) {
        // Buscamos en el texto de toda la fila
        let textValue = tr[i].textContent || tr[i].innerText;
        tr[i].style.display = textValue.toLowerCase().indexOf(filter) > -1 ? "" : "none";
    }
}

// ================================================================
// ACCIONES DE LA TUERCA Y AJUSTES
// ================================================================
function editarPieza(id) {
    const item = inventarioData.find(i => i.id === id);
    // Por ahora lanzamos un prompt profesional, luego podemos hacer un Modal
    let nuevoPrecio = prompt(`Editando ${item.pieza}.\nIngrese el nuevo precio unitario:`, item.price || item.precio);
    
    if (nuevoPrecio !== null && !isNaN(nuevoPrecio)) {
        item.precio = parseFloat(nuevoPrecio);
        renderAlmacen(); // Refrescamos la tabla y KPIs
        alert("Actualización exitosa.");
    }
}

// Función para el botón "Registrar Entrada" (El botón naranja de arriba)
function abrirModalAlmacen() {
    alert("Abriendo Formulario de Recepción de Mercancía...\n(Aquí conectaremos con la API de Proveedores pronto)");
}

// ================================================================
// LÓGICA DE LOGÍSTICA (Envío y Rastreo - MACUIN Enterprise)
// ================================================================

// 1. Base de datos simulada de envíos
let enviosData = [
    { guia: "TRK-7721", cliente: "Autozone Querétaro", courier: "FedEx", status: "En Tránsito", progreso: 65, fecha: "2026-03-02" },
    { guia: "TRK-1104", cliente: "Refaccionaria Mendoza", courier: "DHL", status: "Entregado", progreso: 100, fecha: "2026-02-28" },
    { guia: "TRK-8840", cliente: "Taller Hermanos", courier: "Interno", status: "Preparando", progreso: 10, fecha: "2026-03-05" }
];

// 2. Renderizar la tabla con barras de progreso
function renderLogistica() {
    const tbody = document.getElementById("tabla-logistica-body");
    if (!tbody) return;
    tbody.innerHTML = "";

    enviosData.forEach(envio => {
        // Asignación de colores según estatus
        let statusClass = "";
        switch(envio.status) {
            case "Entregado": statusClass = "badge-success"; break;
            case "En Tránsito": statusClass = "badge-warning"; break;
            case "Preparando": statusClass = "badge-info"; break;
            case "Retrasado": statusClass = "badge-danger"; break;
            default: statusClass = "badge-info";
        }

        tbody.innerHTML += `
            <tr>
                <td><strong>#${envio.guia}</strong></td>
                <td>${envio.cliente}</td>
                <td><i class="fas fa-shipping-fast"></i> ${envio.courier}</td>
                <td style="width: 180px;">
                    <div style="background:#eee; border-radius:10px; height:8px; overflow:hidden; margin-bottom: 4px;">
                        <div style="background:#3498db; height:100%; width:${envio.progreso}%; transition: width 0.5s ease;"></div>
                    </div>
                    <small style="font-weight: bold; color: #555;">${envio.progreso}% completado</small>
                </td>
                <td><span class="badge ${statusClass}">${envio.status}</span></td>
                <td>${envio.fecha}</td>
                <td class="row-actions">
                    <button class="icon-view" onclick="rastrearPaquete('${envio.guia}')" title="Rastrear Ubicación">
                        <i class="fas fa-map-marker-alt"></i>
                    </button>
                    <button class="icon-edit" onclick="actualizarEstatus('${envio.guia}')" title="Actualizar Estado">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    actualizarKPIsLogistica();
}

// 3. Funciones de Modal
function abrirModalLogistica() {
    const modal = document.getElementById("modalLogistica");
    if(modal) modal.style.display = "flex";
}

function cerrarModalLogistica() {
    const modal = document.getElementById("modalLogistica");
    if(modal) modal.style.display = "none";
}

// 4. Guardar Nuevo Despacho
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

// 5. Actualizar Estatus (La lógica de la "Tuerca" o Sincronización)
function actualizarEstatus(guia) {
    const envio = enviosData.find(e => e.guia === guia);
    if (!envio) return;

    // Ciclo lógico de un paquete
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

// 6. Rastrear (Simulación de GPS)
function rastrearPaquete(guia) {
    alert(`🛰️ Conectando con GPS...\n\nEl envío ${guia} se encuentra actualmente en tránsito hacia su destino. \nPróxima parada: Centro de Distribución Norte.`);
}

// 7. KPIs y Buscador
function actualizarKPIsLogistica() {
    if (!document.getElementById("kpi-transito")) return;
    document.getElementById("kpi-transito").innerText = enviosData.filter(e => e.status === "En Tránsito").length;
    document.getElementById("kpi-despacho").innerText = enviosData.filter(e => e.status === "Preparando").length;
    document.getElementById("kpi-entregados").innerText = enviosData.filter(e => e.status === "Entregado").length;
}

function filtrarLogistica() {
    let filter = document.getElementById("buscador-logistica").value.toLowerCase();
    let rows = document.getElementById("tabla-logistica-body").getElementsByTagName("tr");
    for (let row of rows) {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    }
}

// ================================================================
// MÓDULO DE USUARIOS (Seguridad y Roles - MACUIN)
// ================================================================

let usuariosData = [
    { id: 1, nombre: "Admin Macuin", email: "admin@macuin.com", rol: "Administrador", ultimo: "2026-03-01", status: "Activo" },
    { id: 2, nombre: "Brenda López", email: "b.lopez@macuin.com", rol: "Ventas", ultimo: "2026-02-28", status: "Activo" },
    { id: 3, nombre: "Carlos Ruiz", email: "c.ruiz@macuin.com", rol: "Almacén", ultimo: "2026-03-01", status: "Inactivo" }
];

function renderUsuarios() {
    const tbody = document.getElementById("tabla-usuarios-body");
    if (!tbody) return;
    tbody.innerHTML = "";

    usuariosData.forEach(user => {
        let statusClass = user.status === "Activo" ? "badge-success" : "badge-danger";
        let rolIcon = user.rol === "Administrador" ? "fa-user-shield" : "fa-user";

        tbody.innerHTML += `
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:35px; height:35px; background:#ddd; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#666;">
                            ${user.nombre.charAt(0)}
                        </div>
                        <strong>${user.email.split('@')[0]}</strong>
                    </div>
                </td>
                <td>${user.nombre}</td>
                <td><i class="fas ${rolIcon}"></i> ${user.rol}</td>
                <td>${user.ultimo}</td>
                <td><span class="badge ${statusClass}" onclick="toggleUserStatus(${user.id})" style="cursor:pointer;">${user.status}</span></td>
                <td class="row-actions">
                    <button class="icon-edit" onclick="alert('Función para resetear contraseña enviada a ${user.email}')" title="Resetear Pass"><i class="fas fa-key"></i></button>
                    <button class="icon-delete" onclick="eliminarUsuario(${user.id})" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                </td>
            </tr>
        `;
    });
    actualizarKPIsUsuarios();
}

function abrirModalUsuario() {
    document.getElementById("modalUsuario").style.display = "flex";
}

function cerrarModalUsuario() {
    document.getElementById("modalUsuario").style.display = "none";
}

function guardarUsuario() {
    const nombre = document.getElementById("user-nombre").value;
    const email = document.getElementById("user-email").value;
    const rol = document.getElementById("user-rol").value;

    if(!nombre || !email) return alert("⚠️ Datos incompletos");

    usuariosData.unshift({
        id: Date.now(),
        nombre: nombre,
        email: email,
        rol: rol,
        ultimo: "Nunca",
        status: "Activo"
    });

    cerrarModalUsuario();
    renderUsuarios();
}

function toggleUserStatus(id) {
    const user = usuariosData.find(u => u.id === id);
    user.status = user.status === "Activo" ? "Inactivo" : "Activo";
    renderUsuarios();
}

function eliminarUsuario(id) {
    if(confirm("¿Seguro que desea eliminar este acceso? Esta acción no se puede deshacer.")) {
        usuariosData = usuariosData.filter(u => u.id !== id);
        renderUsuarios();
    }
}

function actualizarKPIsUsuarios() {
    if (!document.getElementById("kpi-activos")) return;
    document.getElementById("kpi-activos").innerText = usuariosData.filter(u => u.status === "Activo").length;
    document.getElementById("kpi-admins").innerText = usuariosData.filter(u => u.rol === "Administrador").length;
    document.getElementById("kpi-operativos").innerText = usuariosData.filter(u => u.rol !== "Administrador").length;
    document.getElementById("kpi-bajas").innerText = usuariosData.filter(u => u.status === "Inactivo").length;
}

function filtrarUsuarios() {
    let filter = document.getElementById("buscador-usuarios").value.toLowerCase();
    let rows = document.getElementById("tabla-usuarios-body").getElementsByTagName("tr");
    for (let row of rows) {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    }
}

