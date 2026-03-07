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
                <a href="{{ route('dashboard') }}" class="btn-logout">Volver al Dashboard</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <h2>Carrito de Compras</h2>
            
            <div class="carrito-container">
                <!-- Ejemplo de carrito con productos -->
                <div class="card" id="carrito-con-productos">
                    <h3>Productos en el Carrito</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr data-precio="1200" data-cantidad="2">
                                <td>Frenos</td>
                                <td>$1,200.00</td>
                                <td>2</td>
                                <td class="subtotal">$2,400.00</td>
                                <td>
                                    <button class="btn-eliminar" onclick="eliminarProducto(this)"><i class="fas fa-trash"></i> Eliminar</button>
                                </td>
                            </tr>
                            <tr data-precio="2300" data-cantidad="1">
                                <td>Amortiguadores</td>
                                <td>$2,300.00</td>
                                <td>1</td>
                                <td class="subtotal">$2,300.00</td>
                                <td>
                                    <button class="btn-eliminar" onclick="eliminarProducto(this)"><i class="fas fa-trash"></i> Eliminar</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><strong>Total:</strong></td>
                                <td colspan="2"><strong id="carrito-total">$4,700.00</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="carrito-actions">
                        <a href="{{ route('checkout') }}" class="btn-login">Proceder al Pago</a>
                    </div>
                </div>
                
                <!-- Mensaje si el carrito está vacío -->
                <div class="card" id="carrito-vacio" style="display: none; text-align: center;">
                    <i class="fas fa-shopping-cart" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                    <p>Tu carrito está vacío</p>
                    <a href="{{ route('catalogo') }}" class="btn-login" style="width: auto; padding: 15px 30px; display: inline-block; margin-top: 15px;">Ir al Catálogo</a>
                </div>
            </div>
        </main>
    </div>

    <script>
        function eliminarProducto(btn) {
            const fila = btn.closest('tr');
            const tbody = fila.closest('tbody');
            
            fila.style.transition = 'opacity 0.3s';
            fila.style.opacity = '0';
            
            setTimeout(() => {
                fila.remove();
                recalcularTotal(tbody);
            }, 300);
        }

        function recalcularTotal(tbody) {
            const filas = tbody.querySelectorAll('tr');
            
            if (filas.length === 0) {
                document.getElementById('carrito-con-productos').style.display = 'none';
                document.getElementById('carrito-vacio').style.display = 'block';
                return;
            }

            let total = 0;
            filas.forEach(fila => {
                const precio = parseFloat(fila.getAttribute('data-precio'));
                const cantidad = parseInt(fila.getAttribute('data-cantidad'));
                total += precio * cantidad;
            });

            document.getElementById('carrito-total').innerHTML = '$' + total.toLocaleString('en-US', { minimumFractionDigits: 2 });
        }
    </script>
</body>
</html>
