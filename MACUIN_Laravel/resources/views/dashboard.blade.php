<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MACUIN - Dashboard</title>
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
            <a href="{{ route('dashboard') }}" class="nav-item active"><i class="fas fa-th-large"></i> Panel</a>
            <a href="{{ route('catalogo') }}" class="nav-item"><i class="fas fa-store"></i> Catálogo</a>
            <a href="{{ route('pedidos') }}" class="nav-item"><i class="fas fa-box"></i> Pedidos</a>
            <a href="{{ route('perfil') }}" class="nav-item"><i class="fas fa-user-circle"></i> Mi Perfil</a>
            <a href="{{ route('carrito') }}" class="nav-item cart-icon"><i class="fas fa-shopping-cart"></i></a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline; margin-left: 10px;">
                @csrf
                <button type="submit" class="nav-item logout-btn" style="background: rgba(255,255,255,0.1); border: none; cursor: pointer; padding: 8px 15px; border-radius: 8px; color: white;">Salir</button>
            </form>
        </nav>
    </header>

    <main class="dashboard-content">
        <div style="margin-bottom:20px;">
            <a href="javascript:history.back()" style="text-decoration:none; color:#64748b; font-weight:700; display:flex; align-items:center; gap:8px; transition:0.3s; width:fit-content;" onmouseover="this.style.color='#b71c1c'" onmouseout="this.style.color='#64748b'">
                <i class="fas fa-arrow-left"></i> REGRESAR
            </a>
        </div>
        <div style="margin-bottom: 30px;">
            <h2 style="font-size: 2rem; font-weight: 800; color: #333;">Bienvenido de nuevo</h2>
            <p style="color: #666; font-size: 1.1rem;">Gestiona tus compras y pedidos de forma intuitiva.</p>
        </div>
        
        <div class="dashboard-cards">
            <a href="{{ route('catalogo') }}" class="card-link">
                <div class="card">
                    <i class="fas fa-search" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 20px;"></i>
                    <h3>Buscar Refacciones</h3>
                    <p>Explora miles de productos con los mejores precios del mercado.</p>
                </div>
            </a>

            <a href="{{ route('pedidos') }}" class="card-link">
                <div class="card">
                    <i class="fas fa-truck-loading" style="font-size: 2.5rem; color: #333; margin-bottom: 20px;"></i>
                    <h3>Mis Pedidos</h3>
                    <p>Revisa el estado de tus compras y descarga tus comprobantes.</p>
                </div>
            </a>
            
            <a href="{{ route('perfil') }}" class="card-link">
                <div class="card">
                    <i class="fas fa-id-card" style="font-size: 2.5rem; color: #666; margin-bottom: 20px;"></i>
                    <h3>Configurar Perfil</h3>
                    <p>Actualiza tus datos personales y direcciones de envío.</p>
                </div>
            </a>
        </div>

        <div class="card" style="margin-top: 40px; border-left: 6px solid var(--primary); display: flex; align-items: center; gap: 20px;">
            <div style="background: #ffebee; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-bullhorn" style="color: var(--primary); font-size: 1.5rem;"></i>
            </div>
            <div>
                <h4 style="margin: 0; font-size: 1.2rem; color: #333;">Aviso Importante</h4>
                <p style="margin: 5px 0 0 0; color: #666;">Ahora puedes facturar tus pedidos directamente desde la sección de detalles.</p>
            </div>
        </div>
    </main>
</body>
</html>
