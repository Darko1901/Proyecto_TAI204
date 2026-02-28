<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autopartes MACUIN - Detalle del Pedido</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Autopartes MACUIN</h1>
            <div class="user-info">
                <span>Detalle del Pedido</span>
                <a href="{{ route('pedidos') }}" class="btn-logout">Volver a Mis Pedidos</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <h2>Pedido #00001</h2>
            
            <div class="pedido-detalle-container">
                <!-- Información del Pedido -->
                <div class="card">
                    <h3>Información del Pedido</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Número de Pedido:</strong>
                            <span>#00001</span>
                        </div>
                        <div class="info-item">
                            <strong>Fecha de Pedido:</strong>
                            <span>15 de febrero de 2026</span>
                        </div>
                        <div class="info-item">
                            <strong>Estado:</strong>
                            <span class="status-badge status-enviado">Enviado</span>
                        </div>
                        <div class="info-item">
                            <strong>Fecha Estimada de Entrega:</strong>
                            <span>28 de febrero de 2026</span>
                        </div>
                    </div>
                </div>

                <!-- Productos del Pedido -->
                <div class="card">
                    <h3>Productos</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                                <th>Disponibilidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Frenos</td>
                                <td>2</td>
                                <td>$1,200.00</td>
                                <td>$2,400.00</td>
                                <td><span class="disponible">En Stock</span></td>
                            </tr>
                            <tr>
                                <td>Amortiguadores</td>
                                <td>1</td>
                                <td>$2,300.00</td>
                                <td>$2,300.00</td>
                                <td><span class="disponible">En Stock</span></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><strong>Total:</strong></td>
                                <td colspan="2"><strong>$4,700.00</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Información de Entrega -->
                <div class="card">
                    <h3>Información de Entrega</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Dirección:</strong>
                            <span>Av. Principal #123, Col. Centro</span>
                        </div>
                        <div class="info-item">
                            <strong>Ciudad:</strong>
                            <span>Querétaro</span>
                        </div>
                        <div class="info-item">
                            <strong>Código Postal:</strong>
                            <span>76000</span>
                        </div>
                        <div class="info-item">
                            <strong>Teléfono:</strong>
                            <span>442-123-4567</span>
                        </div>
                        <div class="info-item full-width">
                            <strong>Notas:</strong>
                            <span>Entregar en horario de oficina (9:00 AM - 6:00 PM)</span>
                        </div>
                    </div>
                </div>

                <!-- Seguimiento del Pedido -->
                <div class="card">
                    <h3>Seguimiento del Pedido</h3>
                    <div class="timeline">
                        <div class="timeline-item completed">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h4>Pedido Recibido</h4>
                                <p>15 de febrero de 2026 - 10:30 AM</p>
                                <p>Tu pedido ha sido recibido y está siendo procesado.</p>
                            </div>
                        </div>
                        <div class="timeline-item completed">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h4>Pedido Surtido</h4>
                                <p>16 de febrero de 2026 - 2:15 PM</p>
                                <p>Los productos han sido preparados para envío.</p>
                            </div>
                        </div>
                        <div class="timeline-item active">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h4>Pedido Enviado</h4>
                                <p>18 de febrero de 2026 - 9:00 AM</p>
                                <p>Tu pedido está en camino.</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h4>Pedido Entregado</h4>
                                <p>Pendiente</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card">
                    <div class="pedido-actions">
                        <a href="{{ route('pedido.descargar', 1) }}" class="btn-login">Descargar Comprobante PDF</a>
                        
                        <!-- Solo mostrar si el pedido está en estado "Recibido" -->
                        <!-- 
                        <form method="POST" action="{{ route('pedido.cancelar', 1) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-cancelar" onclick="return confirm('¿Estás seguro de que deseas cancelar este pedido?')">
                                Cancelar Pedido
                            </button>
                        </form>
                        -->
                        
                        <p class="nota-cancelacion">
                            <strong>Nota:</strong> Los pedidos solo pueden cancelarse mientras estén en estado "Recibido".
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
