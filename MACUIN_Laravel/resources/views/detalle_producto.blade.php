<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Producto - Autopartes MACUIN</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Autopartes MACUIN</h1>
            <div class="user-info">
                <a href="{{ route('catalogo') }}" class="btn-logout">Volver al Catálogo</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <h2>Detalle del Producto</h2>
            
            <div class="producto-detalle">
                <!-- Aquí irá el detalle del producto -->
                <p>Información del producto</p>
            </div>
        </main>
    </div>
</body>
</html>
