<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de compras - Autopartes MACUIN</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Autopartes MACUIN</h1>
            <div class="user-info">
                <a href="{{ route('catalogo') }}" class="btn-logout"><i class="fas fa-store"></i> Catalogo</a>
                <a href="{{ route('dashboard') }}" class="btn-logout">Volver al Dashboard</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <h2>Carrito de Compras</h2>
            
            <div class="carrito-container">
                <!-- Carrito con productos -->
                <div class="card" id="carrito-con-productos" style="display: none;">
                    <h3>Productos en el Carrito</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio Unitario</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="carrito-body">
                            <!-- Se llena con JavaScript -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><strong>Total:</strong></td>
                                <td colspan="2"><strong id="carrito-total">$0.00</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="carrito-actions">
                        <button onclick="vaciarCarrito()" class="btn-cancelar"><i class="fas fa-trash-alt"></i> Vaciar Carrito</button>
                        <a href="{{ route('checkout') }}" class="btn-login" style="width: auto; margin-top: 0; padding: 15px 30px;">Proceder al Pago</a>
                    </div>
                </div>
                
                <!-- Mensaje si el carrito esta vacio -->
                <div class="card" id="carrito-vacio" style="display: none; text-align: center; padding: 50px 30px;">
                    <i class="fas fa-shopping-cart" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                    <p style="color: #888; font-size: 1.1rem;">Tu carrito esta vacio</p>
                    <a href="{{ route('catalogo') }}" class="btn-login" style="width: auto; padding: 15px 30px; display: inline-block; margin-top: 15px;">
                        <i class="fas fa-store"></i> Ir al Catalogo
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        function cargarCarrito() {
            const carrito = JSON.parse(localStorage.getItem('macuin_carrito') || '[]');
            const tbody = document.getElementById('carrito-body');
            tbody.innerHTML = '';

            if (carrito.length === 0) {
                document.getElementById('carrito-con-productos').style.display = 'none';
                document.getElementById('carrito-vacio').style.display = 'block';
                return;
            }

            document.getElementById('carrito-con-productos').style.display = 'block';
            document.getElementById('carrito-vacio').style.display = 'none';

            let total = 0;

            carrito.forEach((item, index) => {
                const subtotal = item.precioNum * item.cantidad;
                total += subtotal;

                const tr = document.createElement('tr');
                tr.setAttribute('data-index', index);
                tr.innerHTML = `
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:40px; height:40px; background:linear-gradient(135deg,#fce4e4,#f8d7d7); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                <i class="${item.icono}" style="color:#e57373;"></i>
                            </div>
                            <div>
                                <strong>${item.nombre}</strong>
                                <br><small style="color:#888;">${item.marca}</small>
                            </div>
                        </div>
                    </td>
                    <td>${item.precio}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:5px;">
                            <button onclick="cambiarCantidad(${index}, -1)" class="btn-cantidad" style="width:28px; height:28px; border:1px solid #ddd; background:white; border-radius:4px; cursor:pointer; font-size:1rem;">-</button>
                            <span style="min-width:30px; text-align:center; font-weight:bold;">${item.cantidad}</span>
                            <button onclick="cambiarCantidad(${index}, 1)" class="btn-cantidad" style="width:28px; height:28px; border:1px solid #ddd; background:white; border-radius:4px; cursor:pointer; font-size:1rem;">+</button>
                        </div>
                    </td>
                    <td style="font-weight:bold; color:#e57373;">$${subtotal.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                    <td>
                        <button class="btn-eliminar" onclick="eliminarProducto(${index})"><i class="fas fa-trash"></i> Eliminar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('carrito-total').textContent = '$' + total.toLocaleString('en-US', {minimumFractionDigits:2});
        }

        function cambiarCantidad(index, delta) {
            let carrito = JSON.parse(localStorage.getItem('macuin_carrito') || '[]');
            if (!carrito[index]) return;

            carrito[index].cantidad += delta;
            if (carrito[index].cantidad < 1) {
                carrito.splice(index, 1);
            }

            localStorage.setItem('macuin_carrito', JSON.stringify(carrito));
            cargarCarrito();
        }

        function eliminarProducto(index) {
            let carrito = JSON.parse(localStorage.getItem('macuin_carrito') || '[]');
            
            // Animacion de desvanecimiento
            const fila = document.querySelector(`tr[data-index="${index}"]`);
            if (fila) {
                fila.style.transition = 'opacity 0.3s';
                fila.style.opacity = '0';
            }

            setTimeout(() => {
                carrito.splice(index, 1);
                localStorage.setItem('macuin_carrito', JSON.stringify(carrito));
                cargarCarrito();
            }, 300);
        }

        function vaciarCarrito() {
            if (confirm('¿Estas seguro de vaciar el carrito?')) {
                localStorage.removeItem('macuin_carrito');
                cargarCarrito();
            }
        }

        // Cargar al inicio
        cargarCarrito();
    </script>
</body>
</html>
