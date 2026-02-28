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
                <!-- Aquí irá el contenido del carrito -->
                <p>Tu carrito está vacío</p>
            </div>
        </main>
    </div>
</body>
</html>
