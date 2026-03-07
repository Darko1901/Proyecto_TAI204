<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autopartes MACUIN - Mis Pedidos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Autopartes MACUIN</h1>
            <div class="user-info">
                <span>Mis Pedidos</span>
                <a href="{{ route('carrito') }}" class="btn-logout" title="Mi Carrito"><i class="fas fa-shopping-cart"></i></a>
                <a href="{{ route('dashboard') }}" class="btn-logout">Volver al Dashboard</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <h2>Historial de Pedidos</h2>
            
            <div class="pedidos-container">
                <!-- Pedido 1 -->
                <div class="card pedido-card">
                    <div class="pedido-header">
                        <div class="pedido-info">
                            <h3>Pedido #00001</h3>
                            <p class="pedido-fecha">Fecha: 15 de febrero de 2026</p>
                        </div>
                        <div class="pedido-status">
                            <span class="status-badge status-enviado">Enviado</span>
                        </div>
                    </div>
                    
                    <div class="pedido-body">
                        <div class="pedido-items">
                            <p><strong>Productos:</strong></p>
                            <ul>
                                <li>Frenos (x2) - $2,400.00</li>
                                <li>Amortiguadores (x1) - $2,300.00</li>
                            </ul>
                        </div>
                        <div class="pedido-total">
                            <p><strong>Total:</strong> $4,700.00</p>
                        </div>
                    </div>
                    
                    <div class="pedido-footer">
                        <a href="{{ route('pedido.detalle', 1) }}" class="btn-secondary">Ver Detalles</a>
                    </div>
                </div>

                <!-- Pedido 2 -->
                <div class="card pedido-card">
                    <div class="pedido-header">
                        <div class="pedido-info">
                            <h3>Pedido #00002</h3>
                            <p class="pedido-fecha">Fecha: 20 de febrero de 2026</p>
                        </div>
                        <div class="pedido-status">
                            <span class="status-badge status-surtido">Surtido</span>
                        </div>
                    </div>
                    
                    <div class="pedido-body">
                        <div class="pedido-items">
                            <p><strong>Productos:</strong></p>
                            <ul>
                                <li>Llantas (x4) - $8,000.00</li>
                            </ul>
                        </div>
                        <div class="pedido-total">
                            <p><strong>Total:</strong> $8,000.00</p>
                        </div>
                    </div>
                    
                    <div class="pedido-footer">
                        <a href="{{ route('pedido.detalle', 2) }}" class="btn-secondary">Ver Detalles</a>
                    </div>
                </div>

                <!-- Pedido 3 -->
                <div class="card pedido-card">
                    <div class="pedido-header">
                        <div class="pedido-info">
                            <h3>Pedido #00003</h3>
                            <p class="pedido-fecha">Fecha: 25 de febrero de 2026</p>
                        </div>
                        <div class="pedido-status">
                            <span class="status-badge status-recibido">Recibido</span>
                        </div>
                    </div>
                    
                    <div class="pedido-body">
                        <div class="pedido-items">
                            <p><strong>Productos:</strong></p>
                            <ul>
                                <li>Filtro de Aceite (x3) - $450.00</li>
                                <li>Bujías (x8) - $800.00</li>
                            </ul>
                        </div>
                        <div class="pedido-total">
                            <p><strong>Total:</strong> $1,250.00</p>
                        </div>
                    </div>
                    
                    <div class="pedido-footer">
                        <a href="{{ route('pedido.detalle', 3) }}" class="btn-secondary">Ver Detalles</a>
                        <button class="btn-cancelar" disabled>Cancelar Pedido</button>
                    </div>
                </div>

                <!-- Mensaje si no hay pedidos -->
                <!-- 
                <div class="card">
                    <p>No tienes pedidos registrados aún.</p>
                    <a href="{{ route('catalogo') }}" class="btn-login">Ir al Catálogo</a>
                </div>
                -->
            </div>
        </main>
    </div>
</body>
</html>
