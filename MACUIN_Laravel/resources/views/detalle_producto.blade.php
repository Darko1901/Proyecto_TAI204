<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Producto - Autopartes MACUIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Autopartes MACUIN</h1>
            <div class="user-info">
                <a href="{{ route('carrito') }}" class="btn-logout" title="Mi Carrito"><i class="fas fa-shopping-cart"></i></a>
                <a href="{{ route('catalogo') }}" class="btn-logout">Volver al Catalogo</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <p class="detalle-breadcrumb">
                <a href="{{ route('catalogo') }}">Catalogo</a> &raquo; {{ $producto['categoria'] }} &raquo; {{ $producto['nombre'] }}
            </p>
            <h2>{{ $producto['nombre'] }}</h2>
            
            <div class="card" style="max-width: 800px; margin: 0 auto;">
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 250px; text-align: center;">
                        <div class="detalle-icono-container">
                            <i class="{{ $producto['icono'] }}"></i>
                        </div>
                        <span class="detalle-categoria-badge">{{ $producto['categoria'] }}</span>
                    </div>
                    <div style="flex: 1; min-width: 250px;">
                        <p style="font-size: 1.5rem; color: #333; font-weight: bold; margin-bottom: 15px;">{{ $producto['precio'] }}</p>
                        <p style="margin-bottom: 20px; color: #555; line-height: 1.6;">{{ $producto['descripcion'] }}</p>
                        
                        <div class="info-grid" style="grid-template-columns: 1fr 1fr;">
                            <div class="info-item">
                                <strong>Marca</strong>
                                <span>{{ $producto['marca'] }}</span>
                            </div>
                            <div class="info-item">
                                <strong>Modelo</strong>
                                <span>{{ $producto['modelo'] }}</span>
                            </div>
                            <div class="info-item full-width">
                                <strong>Compatibilidad</strong>
                                <span>{{ $producto['compatibilidad'] }}</span>
                            </div>
                            <div class="info-item">
                                <strong>Garantía</strong>
                                <span>{{ $producto['garantia'] }}</span>
                            </div>
                            <div class="info-item">
                                <strong>Disponibilidad</strong>
                                <span class="{{ $producto['disponible'] ? 'disponible' : 'no-disponible' }}">
                                    {{ $producto['disponible'] ? 'En stock' : 'Agotado' }}
                                </span>
                            </div>
                        </div>

                        <div class="form-actions" style="margin-top: 25px;">
                            <a href="{{ route('carrito') }}" class="btn-login" style="width: auto; margin-top: 0; padding: 15px 30px;">Agregar al Carrito</a>
                            <a href="{{ route('catalogo') }}" class="btn-secondary">Volver al Catálogo</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
