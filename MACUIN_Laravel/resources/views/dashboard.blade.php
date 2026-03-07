<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autopartes MACUIN - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Autopartes MACUIN</h1>
            <div class="user-info">
                <span>Bienvenido, Usuario</span>
                <a href="{{ route('carrito') }}" class="btn-logout" title="Mi Carrito"><i class="fas fa-shopping-cart"></i></a>
                <a href="{{ route('login') }}" class="btn-logout">Cerrar sesión</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <h2>Panel de Control</h2>
            <p>Has iniciado sesión exitosamente en el sistema de Autopartes MACUIN.</p>
            
            <div class="dashboard-cards">
                <a href="{{ route('catalogo') }}" class="card-link">
                    <div class="card">
                        <h3>Catálogo</h3>
                        <p>Explora nuestro catálogo de autopartes</p>
                    </div>
                </a>

                <a href="{{ route('pedidos') }}" class="card-link">
                    <div class="card">
                        <h3>Mis Pedidos</h3>
                        <p>Consulta el estado de tus pedidos</p>
                    </div>
                </a>
                
                <a href="{{ route('perfil') }}" class="card-link">
                    <div class="card">
                        <h3>Mi Perfil</h3>
                        <p>Actualiza tu información personal</p>
                    </div>
                </a>
            </div>
        </main>
    </div>
</body>
</html>
