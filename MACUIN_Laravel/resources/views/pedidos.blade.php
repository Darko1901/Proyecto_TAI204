<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - MACUIN</title>
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
        <h2 style="font-size: 1.8rem; font-weight: 800; color: #333; margin-bottom: 25px;">Historial de Pedidos</h2>
        
        @forelse($pedidos as $pedido)
            @php
                $statusLabel = [1 => 'Recibido', 2 => 'Surtido', 3 => 'Enviado', 4 => 'Entregado', 5 => 'Cancelado'][$pedido['id_estado']] ?? 'Desconocido';
                $statusClass = [1 => 'status-recibido', 2 => 'status-surtido', 3 => 'status-enviado', 4 => 'status-entregado', 5 => 'status-cancelado'][$pedido['id_estado']] ?? '';
            @endphp
            <div class="card" style="margin-bottom: 15px; padding: 20px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h4 style="margin:0; font-size:1.15rem; color:#333;">Pedido #{{ str_pad($pedido['id_pedido'], 5, '0', STR_PAD_LEFT) }}</h4>
                        <p style="margin:5px 0 0 0; color:#666; font-size:0.9rem;">Fecha: {{ \Carbon\Carbon::parse($pedido['fecha_pedido'])->format('d/m/Y - H:i') }}</p>
                    </div>
                    <div style="text-align:right;">
                        <span class="status-badge {{ $statusClass }}" style="display:inline-block; padding:8px 20px; border-radius:20px; font-weight:700; text-transform:uppercase; font-size:0.75rem;">{{ $statusLabel }}</span>
                        <div style="margin-top:10px; font-weight:900; color:#b71c1c; font-size:1.1rem;">Total: ${{ number_format($pedido['total'], 2) }}</div>
                    </div>
                </div>
                <div style="margin-top:20px; padding-top:15px; border-top:1px solid #f0f0f0; display:flex; gap:12px; align-items:center;">
                    <a href="{{ route('pedido.detalle', $pedido['id_pedido']) }}" class="btn-logout" style="background:#f1f5f9; color:#475569; border:none; padding:10px 20px; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-eye"></i> Detalles
                    </a>
                    <a href="{{ route('pedido.factura', $pedido['id_pedido']) }}" class="btn-logout" style="background:#b71c1c; color:#ffffff !important; border:none; padding:10px 20px; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-file-invoice-dollar" style="color: #ffffff;"></i> Descargar Factura
                    </a>
                    @if($pedido['id_estado'] == 1)
                        <form action="{{ route('pedido.cancelar', $pedido['id_pedido']) }}" method="POST" style="display:inline; margin-left:auto;">
                            @csrf
                            <button type="submit" class="btn-logout" style="color:#f44336; border:none; background:none; font-weight:700;" onclick="return confirm('¿Seguro que deseas cancelar?')">
                                <i class="fas fa-times-circle"></i> Cancelar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card" style="text-align:center; padding:80px 40px;">
                <i class="fas fa-box-open fa-4x" style="color:#ddd; margin-bottom:20px;"></i>
                <h3 style="color:#999;">No has realizado ningún pedido aún</h3>
                <a href="{{ route('catalogo') }}" class="btn-login" style="width:auto; padding:12px 30px; display:inline-block; margin-top:20px;">IR AL CATÁLOGO</a>
            </div>
        @endforelse
    </main>

    <style>
        .status-recibido { background:#fff3cd; color:#856404; }
        .status-surtido { background:#d1ecf1; color:#0c5460; }
        .status-enviado { background:#d4edda; color:#155724; }
        .status-entregado { background:#c3e6cb; color:#155724; }
        .status-cancelado { background:#f8d7da; color:#721c24; }
    </style>
</body>
</html>
