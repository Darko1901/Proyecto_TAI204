<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autopartes MACUIN - Finalizar Pedido</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Autopartes MACUIN</h1>
            <div class="user-info">
                <span>Finalizar Pedido</span>
                <a href="{{ route('carrito') }}" class="btn-logout">Volver al Carrito</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <h2>Finalizar Pedido</h2>
            
            <div class="checkout-container">
                <div class="checkout-section">
                    <h3>Resumen del Pedido</h3>
                    <div class="card">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Frenos</td>
                                    <td>2</td>
                                    <td>$1,200.00</td>
                                    <td>$2,400.00</td>
                                </tr>
                                <tr>
                                    <td>Amortiguadores</td>
                                    <td>1</td>
                                    <td>$2,300.00</td>
                                    <td>$2,300.00</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><strong>Total:</strong></td>
                                    <td><strong>$4,700.00</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="checkout-section">
                    <h3>Información de Entrega</h3>
                    <div class="card">
                        <form method="POST" action="{{ route('pedido.crear') }}">
                            @csrf
                            
                            <div class="form-group">
                                <label for="direccion">Dirección de Entrega</label>
                                <textarea id="direccion" name="direccion" rows="3" required></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group form-group-half">
                                    <label for="ciudad">Ciudad</label>
                                    <input type="text" id="ciudad" name="ciudad" required>
                                </div>

                                <div class="form-group form-group-half">
                                    <label for="codigo_postal">Código Postal</label>
                                    <input type="text" id="codigo_postal" name="codigo_postal" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="telefono">Teléfono de Contacto</label>
                                <input type="tel" id="telefono" name="telefono" required>
                            </div>

                            <div class="form-group">
                                <label for="notas">Notas Adicionales (Opcional)</label>
                                <textarea id="notas" name="notas" rows="2"></textarea>
                            </div>

                            <div class="form-actions">
                                <a href="{{ route('carrito') }}" class="btn-secondary">Cancelar</a>
                                <button type="submit" class="btn-login">Confirmar Pedido</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
