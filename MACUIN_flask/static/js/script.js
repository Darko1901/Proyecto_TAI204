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
    
    // Ocultar toolbar y columna de acciones
    const ocultarPDF = document.querySelectorAll('.row-actions, .toolbar, .col-acciones');
    ocultarPDF.forEach(el => el.style.display = 'none'); 

    // Mostrar título del PDF
    const tituloPDF = document.getElementById('titulo-pdf');
    if (tituloPDF) tituloPDF.style.display = 'block';

    const opciones = {
        margin: 10,
        filename: 'Reporte_Ventas_MACUIN.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };

    if (typeof html2pdf !== 'undefined') {
        html2pdf().set(opciones).from(elemento).save().then(() => {
            ocultarPDF.forEach(el => el.style.display = ''); 
            if (tituloPDF) tituloPDF.style.display = 'none';
        });
    } else {
        alert("Librería PDF no disponible.");
        ocultarPDF.forEach(el => el.style.display = '');
        if (tituloPDF) tituloPDF.style.display = 'none';
    }
}


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

function filtrarAlmacen() {
    let input = document.getElementById("buscador-almacen");
    let filter = input.value.toLowerCase();
    let tbody = document.getElementById("tabla-almacen-body");
    let tr = tbody.getElementsByTagName("tr");

    for (let i = 0; i < tr.length; i++) {
        let textValue = tr[i].textContent || tr[i].innerText;
        tr[i].style.display = textValue.toLowerCase().indexOf(filter) > -1 ? "" : "none";
    }
}

function editarPieza(id) {
    const item = inventarioData.find(i => i.id === id);
    let nuevoPrecio = prompt(`Editando ${item.pieza}.\nIngrese el nuevo precio unitario:`, item.price || item.precio);
    
    if (nuevoPrecio !== null && !isNaN(nuevoPrecio)) {
        item.precio = parseFloat(nuevoPrecio);
        renderAlmacen(); 
        alert("Actualización exitosa.");
    }
}

function abrirModalAlmacen() {
    alert("Abriendo Formulario de Recepción de Mercancía...\n(Aquí conectaremos con la API de Proveedores pronto)");
}


// ================================================================
// LÓGICA DE LOGÍSTICA (Envío y Rastreo - MACUIN Enterprise)
// ================================================================

let enviosData = [
    { guia: "TRK-7721", cliente: "Autozone Querétaro", courier: "FedEx", status: "En Tránsito", progreso: 65, fecha: "2026-03-02" },
    { guia: "TRK-1104", cliente: "Refaccionaria Mendoza", courier: "DHL", status: "Entregado", progreso: 100, fecha: "2026-02-28" },
    { guia: "TRK-8840", cliente: "Taller Hermanos", courier: "Interno", status: "Preparando", progreso: 10, fecha: "2026-03-05" }
];

function renderLogistica() {
    const tbody = document.getElementById("tabla-logistica-body");
    if (!tbody) return;
    tbody.innerHTML = "";

    enviosData.forEach(envio => {
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

function abrirModalLogistica() {
    const modal = document.getElementById("modalLogistica");
    if(modal) modal.style.display = "flex";
}

function cerrarModalLogistica() {
    const modal = document.getElementById("modalLogistica");
    if(modal) modal.style.display = "none";
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
                <td><strong>${empresa.id}</strong></td>
                <td>${empresa.nombre}</td>
                <td><span class="tag-categoria">${empresa.plan}</span></td>
                <td><i class="fas fa-users" style="color:#7f8c8d;"></i> ${empresa.usuarios}</td>
                <td><span class="badge ${statusClass}">${empresa.estado}</span></td>
                <td class="row-actions">
                    <button class="icon-view" onclick="alert('Entrando al servidor de ${empresa.nombre} como SuperUsuario...')" title="Inspeccionar Empresa"><i class="fas fa-sign-in-alt"></i></button>
                    <button class="icon-edit" onclick="alert('Modificando roles y permisos de ${empresa.nombre}')" title="Asignar Roles"><i class="fas fa-cogs"></i></button>
                    <button class="icon-delete" onclick="alert('Suspendiendo servicio a ${empresa.nombre}')" title="Suspender Cuenta"><i class="fas fa-ban"></i></button>
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

let usuariosGlobales = [
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
                    <button style="background:none; border:none; color:#e74c3c; cursor:pointer; font-size: 1.1rem;" title="Suspender/Activar" onclick="suspenderUsuario('${usr.id}')"><i class="fas fa-ban"></i></button>
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

function suspenderUsuario(id) {
    if(confirm("¿Cambiar el estado de este usuario?")) {
        const usuario = usuariosGlobales.find(u => u.id === id);
        if(usuario) {
            usuario.estado = usuario.estado === "Activo" ? "Suspendido" : "Activo";
            renderTablas(); 
        }
    }
}

function abrirModalEmpresa() { document.getElementById('modalEmpresa').style.display = 'flex'; }
function cerrarModalEmpresa() { document.getElementById('modalEmpresa').style.display = 'none'; }

function guardarEmpresa() {
    const nombre = document.getElementById('nueva-empresa-nombre').value;
    if(nombre) {
        solicitudesEmpresas.push({
            id: "REQ-" + Math.floor(Math.random() * 900 + 100),
            nombre: nombre,
            contacto: "pendiente@porasignar.com",
            estado: "Pendiente"
        });
        
        document.getElementById('nueva-empresa-nombre').value = '';
        cerrarModalEmpresa();
        renderTablas();
        alert(`¡Listo! La empresa "${nombre}" se ha enviado a la tabla de solicitudes.`);
    }
}

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
    }
    if (document.getElementById("tabla-almacen-body") && typeof renderAlmacen === 'function') {
        renderAlmacen();
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
};