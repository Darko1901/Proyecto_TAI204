<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de compras - Autopartes MACUIN</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
                <div class="card">
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
                            <tr>
                                <td>Frenos</td>
                                <td>$1,200.00</td>
                                <td>2</td>
                                <td>$2,400.00</td>
                                <td>
                                    <button class="btn-eliminar">Eliminar</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Amortiguadores</td>
                                <td>$2,300.00</td>
                                <td>1</td>
                                <td>$2,300.00</td>
                                <td>
                                    <button class="btn-eliminar">Eliminar</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><strong>Total:</strong></td>
                                <td colspan="2"><strong>$4,700.00</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="carrito-actions">
                        <a href="{{ route('checkout') }}" class="btn-login">Proceder al Pago</a>
                    </div>
                </div>
                
                <!-- Mensaje si el carrito está vacío -->
                <!-- 
                <div class="card">
                    <p>Tu carrito está vacío</p>
                    <a href="{{ route('catalogo') }}" class="btn-login">Ir al Catálogo</a>
                </div>
                -->
            </div>
        </main>
    </div>
</body>
</html>
