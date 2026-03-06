<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Autopartes MACUIN</title>
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
            <h2>Catálogo de Productos</h2>
            
            <div class="dashboard-cards">
                <a href="{{ route('detalle_producto', 1) }}" class="card-link">
                    <div class="card">
                        <img src="{{ asset('img/amortiguador.png') }}" alt="Amortiguador" style="width:100%; height:180px; object-fit:contain; margin-bottom:10px;">
                        <h3>Amortiguador Delantero</h3>
                        <p>Amortiguador de alta resistencia para suspensión delantera.</p>
                        <p><strong>$1,250.00 MXN</strong></p>
                    </div>
                </a>
                <a href="{{ route('detalle_producto', 2) }}" class="card-link">
                    <div class="card">
                        <img src="{{ asset('img/frenos.png') }}" alt="Frenos" style="width:100%; height:180px; object-fit:contain; margin-bottom:10px;">
                        <h3>Kit de Frenos</h3>
                        <p>Kit completo de pastillas y discos de freno.</p>
                        <p><strong>$890.00 MXN</strong></p>
                    </div>
                </a>
            </div>
        </main>
    </div>
</body>
</html>
