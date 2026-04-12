<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Autopartes MACUIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        <main class="dashboard-content" style="text-align: center; padding: 60px 20px;">
            <div style="max-width:600px; margin:0 auto 20px auto; text-align:left;">
                <a href="{{ route('dashboard') }}" style="text-decoration:none; color:#64748b; font-weight:700; display:flex; align-items:center; gap:8px; transition:0.3s; width:fit-content;" onmouseover="this.style.color='#b71c1c'" onmouseout="this.style.color='#64748b'">
                    <i class="fas fa-arrow-left"></i> REGRESAR AL PANEL
                </a>
            </div>
            
            <div class="card" style="max-width: 600px; margin: 0 auto; padding: 50px; border-radius:30px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); border:none;">
                <div style="width:100px; height:100px; background:#e8f5e9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin: 0 auto 30px;">
                    <i class="fas fa-check" style="font-size: 3.5rem; color: #2e7d32;"></i>
                </div>
                
                <h2 style="color: #1b5e20; font-size:2.5rem; font-weight:900; margin-bottom: 10px; letter-spacing:-1px;">¡Pedido Confirmado!</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 40px;">
                    Tu pedido <strong>#{{ str_pad($pedido['id_pedido'], 5, '0', STR_PAD_LEFT) }}</strong> ha sido registrado con éxito y ya está siendo procesado.
                </p>
                
                <div style="background: #f8fafc; padding: 30px; border-radius: 20px; margin-bottom: 40px; text-align: left; border: 1px solid #e2e8f0;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                        <span style="color:#64748b; font-weight:600;">Total Pagado:</span>
                        <strong style="color:#1e293b; font-size:1.1rem;">${{ number_format($pedido['total'], 2) }} MXN</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                        <span style="color:#64748b; font-weight:600;">Método de Pago:</span>
                        <strong style="color:#1e293b;">{{ $pedido['metodo_pago'] ?? 'Tarjeta' }}</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#64748b; font-weight:600;">Estado:</span>
                        <span style="background:#fff3cd; color:#856404; padding:2px 12px; border-radius:10px; font-size:0.8rem; font-weight:700;">RECIBIDO</span>
                    </div>
                </div>
                
                <div class="confirm-actions" style="display: grid; grid-template-columns: 1fr; gap:15px;">
                    <a href="{{ route('pedidos') }}" class="btn-login" style="height:60px; display:flex; align-items:center; justify-content:center; font-weight:900; background:linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%); border:none; box-shadow: 0 10px 20px rgba(27, 94, 32, 0.2); border-radius:15px; font-size:1.1rem;">
                        <i class="fas fa-box" style="margin-right:10px;"></i> VER MIS PEDIDOS
                    </a>
                    <a href="{{ route('catalogo') }}" class="btn-secondary" style="height:60px; display:flex; align-items:center; justify-content:center; font-weight:700; border-radius:15px; background:white; border:2px solid #e2e8f0; color:#475569;">
                        SEGUIR COMPRANDO
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Al llegar a esta página, vaciamos el carrito local
        localStorage.removeItem('macuin_carrito');

        // Disparar notificación de éxito
        document.addEventListener('DOMContentLoaded', function() {
            const metodo = "{{ $pedido['metodo_pago'] ?? 'Tarjeta' }}";
            let title = '¡Pedido Realizado!';
            let text = 'Tu orden #{{ str_pad($pedido['id_pedido'], 5, '0', STR_PAD_LEFT) }} ha sido registrada correctamente.';
            
            if(metodo === 'Efectivo') {
                title = '¡Ticket Generado!';
                text = 'Tu orden #{{ str_pad($pedido['id_pedido'], 5, '0', STR_PAD_LEFT) }} está pendiente de pago. Por favor liquida tu ticket en cualquier sucursal autorizada.';
            }

            Swal.fire({
                title: title,
                text: text,
                icon: 'success',
                confirmButtonColor: metodo === 'Efectivo' ? '#1e293b' : '#2e7d32',
                confirmButtonText: 'Entendido',
                backdrop: metodo === 'Efectivo' ? `rgba(30, 41, 59, 0.2)` : `rgba(46, 125, 50, 0.1)`
            });
        });
    </script>
</body>
</html>
