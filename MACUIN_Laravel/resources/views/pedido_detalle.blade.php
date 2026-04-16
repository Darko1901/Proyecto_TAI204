<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Pedido #{{ $pedido['id_pedido'] }} - MACUIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <header class="dashboard-header">
        <div class="header-left">
            <h1>MACUIN</h1>
        </div>
        <nav class="top-nav">
            <a href="{{ route('home') }}" class="nav-item"><i class="fas fa-home"></i> Inicio</a>
            <a href="{{ route('dashboard') }}" class="nav-item"><i class="fas fa-th-large"></i> Panel</a>
            <a href="{{ route('catalogo') }}" class="nav-item"><i class="fas fa-store"></i> Catálogo</a>
            <a href="{{ route('pedidos') }}" class="nav-item active"><i class="fas fa-box"></i> Pedidos</a>
            <a href="{{ route('perfil') }}" class="nav-item"><i class="fas fa-user-circle"></i> Mi Perfil</a>
            <a href="{{ route('carrito') }}" class="nav-item cart-icon"><i class="fas fa-shopping-cart"></i></a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline; margin-left:10px;">
                @csrf
                <button type="submit" class="nav-item logout-btn" style="background:rgba(255,255,255,0.1); border:none; cursor:pointer; padding:8px 15px; border-radius:8px; color:white;">Salir</button>
            </form>
        </nav>
    </header>
    
    <main class="dashboard-content">
        <div style="margin-bottom:20px;">
            <a href="javascript:history.back()" style="text-decoration:none; color:#64748b; font-weight:700; display:flex; align-items:center; gap:8px; transition:0.3s; width:fit-content;" onmouseover="this.style.color='#b71c1c'" onmouseout="this.style.color='#64748b'">
                <i class="fas fa-arrow-left"></i> REGRESAR
            </a>
        </div>
        <div class="detalle-wrapper" style="max-width:1200px; margin:0 auto;">
            <div class="card" style="padding:40px; border-radius:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #f0f0f0; padding-bottom:20px; margin-bottom:30px;">
                    <div>
                        <h2 style="font-size:1.8rem; font-weight:800; color:#333; margin:0;">Pedido #{{ str_pad($pedido['id_pedido'], 5, '0', STR_PAD_LEFT) }}</h2>
                        <p style="color:#666; margin-top:5px;">Fecha: {{ \Carbon\Carbon::parse($pedido['fecha_pedido'])->format('d/m/Y - H:i') }}</p>
                    </div>
                    <div style="text-align:right; display:flex; flex-direction:column; align-items:flex-end; gap:10px;">
                        <span class="status-badge" style="background:#e8f5e9; color:#2e7d32; padding:10px 25px; border-radius:30px; font-weight:800; text-transform:uppercase;">{{ [1 => 'Recibido', 2 => 'Surtido', 3 => 'Enviado', 4 => 'Entregado', 5 => 'Cancelado'][$pedido['id_estado']] }}</span>
                        <a href="{{ route('pedido.factura', $id) }}" class="btn-login" style="background:#fff; color:#b71c1c; border:1px solid #ffcdd2; padding:8px 15px; font-size:0.85rem; width:auto; font-weight:700;">
                            <i class="fas fa-file-invoice-dollar"></i> Descargar Factura PDF
                        </a>
                    </div>
                </div>

                <!-- Timeline de Seguimiento -->
                <div style="margin-bottom:50px; padding:20px 0;">
                    <h4 style="margin-bottom:30px; color:#333; font-size:1.1rem; text-align:center;">Seguimiento de tu Pedido</h4>
                    <div style="display:flex; justify-content:space-between; position:relative; max-width:800px; margin:0 auto;">
                        <div style="position:absolute; top:20px; left:0; right:0; height:4px; background:#e2e8f0; z-index:1;"></div>
                        <div style="position:absolute; top:20px; left:0; width:{{ ($pedido['id_estado']-1)*33.3 }}%; height:4px; background:#2e7d32; z-index:2; transition: width 1s ease;"></div>
                        
                        @php
                            $steps = [
                                ['id' => 1, 'label' => 'Recibido', 'icon' => 'fa-check-circle'],
                                ['id' => 2, 'label' => 'Surtido', 'icon' => 'fa-box'],
                                ['id' => 3, 'label' => 'Enviado', 'icon' => 'fa-truck'],
                                ['id' => 4, 'label' => 'Entregado', 'icon' => 'fa-home']
                            ];
                        @endphp

                        @foreach($steps as $step)
                            @php
                                $isActive = $pedido['id_estado'] >= $step['id'];
                                $isCurrent = $pedido['id_estado'] == $step['id'];
                            @endphp
                            <div style="text-align:center; position:relative; z-index:3; width:60px;">
                                <div style="width:44px; height:44px; background:{{ $isActive ? '#2e7d32' : '#fff' }}; border:4px solid {{ $isActive ? '#2e7d32' : '#e2e8f0' }}; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto; color:{{ $isActive ? '#fff' : '#cbd5e1' }}; transition:0.3s; {{ $isCurrent ? 'box-shadow: 0 0 0 5px rgba(46, 125, 50, 0.2);' : '' }}">
                                    <i class="fas {{ $step['icon'] }}"></i>
                                </div>
                                <span style="display:block; margin-top:10px; font-size:0.75rem; font-weight:{{ $isActive ? '800' : '600' }}; color:{{ $isActive ? '#1e293b' : '#94a3b8' }}; text-transform:uppercase;">{{ $step['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:40px;">
                    <div>
                        <h4 style="margin-bottom:20px; color:#333; font-size:1.2rem;">Productos en este pedido</h4>
                        <table class="table">
                            <thead>
                                <tr><th>Producto</th><th>Cant.</th><th>P.U.</th><th>Subtotal</th></tr>
                            </thead>
                            <tbody>
                                @foreach($pedido['detalles'] as $d)
                                    <tr>
                                        <td>{{ $d['producto']['nombre_producto'] ?? 'ID: ' . ($d['id_producto'] ?? 'N/A') }}</td>
                                        <td>{{ $d['cantidad'] ?? 0 }}</td>
                                        <td>${{ number_format($d['precio_unitario'] ?? 0, 2) }}</td>
                                        <td style="font-weight:700;">${{ number_format($d['subtotal'] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align:right; font-weight:700; padding:15px;">TOTAL:</td>
                                    <td style="font-weight:900; color:#b71c1c; font-size:1.3rem;">${{ number_format($pedido['total'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div>
                        <div class="card" style="background:#f9f9f9; padding:25px; border:none; margin-bottom:20px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                                <h4 style="color:#333; margin:0;">Información de Envío</h4>
                                <button onclick="document.getElementById('edit-envio').style.display='block'; document.getElementById('view-envio').style.display='none';" style="background:none; border:none; color:var(--primary); cursor:pointer; font-weight:700;"><i class="fas fa-edit"></i> Modificar</button>
                            </div>
                            
                            <div id="view-envio" style="font-size:0.9rem; color:#666; line-height:1.6;">
                                <strong>Dirección:</strong><br>{{ $pedido['direccion_entrega'] ?? ($envio['direccion'] ?? 'No especificada') }}<br><br>
                                <strong>C.P.:</strong><br>{{ $envio['codigo_postal'] ?? 'N/A' }}<br><br>
                                <strong>Teléfono:</strong><br>{{ $envio['telefono_contacto'] ?? 'N/A' }}<br><br>
                                <strong>Referencias:</strong><br>{{ $pedido['referencias'] ?? ($envio['referencias'] ?? 'Ninguna') }}<br><br>
                                <strong>Pago:</strong><br>{{ $pedido['metodo_pago'] ?? 'Tarjeta' }}
                            </div>

                            <div id="edit-envio" style="display:none; font-size:0.9rem; color:#444;">
                                <form method="POST" action="{{ route('pedido.updateEnvio', $pedido['id_pedido']) }}">
                                    @csrf
                                    <div style="margin-bottom:10px;">
                                        <label style="font-weight:700; margin-bottom:5px; display:block;">Dirección</label>
                                        <input type="text" name="direccion" value="{{ $envio['direccion'] ?? '' }}" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                                    </div>
                                    <div style="margin-bottom:10px;">
                                        <label style="font-weight:700; margin-bottom:5px; display:block;">Código Postal</label>
                                        <input type="text" name="codigo_postal" value="{{ $envio['codigo_postal'] ?? '' }}" required maxlength="5" pattern="\d{5}" oninput="this.value=this.value.replace(/[^0-9]/g, '');" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                                    </div>
                                    <div style="margin-bottom:10px;">
                                        <label style="font-weight:700; margin-bottom:5px; display:block;">Teléfono Contacto</label>
                                        <input type="tel" name="telefono_contacto" value="{{ $envio['telefono_contacto'] ?? '' }}" required maxlength="10" pattern="\d{10}" oninput="this.value=this.value.replace(/[^0-9]/g, '');" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                                    </div>
                                    <div style="margin-bottom:15px;">
                                        <label style="font-weight:700; margin-bottom:5px; display:block;">Referencias</label>
                                        <input type="text" name="referencias" value="{{ $envio['referencias'] ?? '' }}" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                                    </div>
                                    <div style="display:flex; gap:10px;">
                                        <button type="button" onclick="document.getElementById('edit-envio').style.display='none'; document.getElementById('view-envio').style.display='block';" style="background:#eee; color:#333; border:none; padding:10px; border-radius:8px; cursor:pointer; flex:1;">Cancelar</button>
                                        <button type="submit" style="background:var(--primary); color:white; border:none; padding:10px; border-radius:8px; cursor:pointer; flex:1;">Guardar Cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card" style="padding:20px; text-align:center; background:#fff8f8; border:1px solid #ffcdd2;">
                            <i class="fas fa-headset fa-2x" style="color:var(--primary); margin-bottom:15px;"></i>
                            <h5 style="margin:0;">¿Necesitas soporte?</h5>
                            <p style="font-size:0.8rem; color:#666; margin:8px 0 15px;">Para dudas logísticas o aclaraciones, contáctanos vía WhatsApp.</p>
                            <a href="https://wa.me/1234567890" class="btn-login" style="width:100%; border-radius:8px; padding:10px; font-size:0.85rem;">WHATSAPP SOPORTE</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
