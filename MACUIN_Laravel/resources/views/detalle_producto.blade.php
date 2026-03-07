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
                        <div class="detalle-imagen-container">
                            <img src="{{ asset($producto['imagen']) }}" alt="{{ $producto['nombre'] }}">
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
                            @if($producto['disponible'])
                                <button onclick="agregarAlCarrito()" class="btn-login" style="width: auto; margin-top: 0; padding: 15px 30px; cursor: pointer;">
                                    <i class="fas fa-cart-plus"></i> Agregar al Carrito
                                </button>
                            @else
                                <button class="btn-login" style="width: auto; margin-top: 0; padding: 15px 30px; background-color: #ccc; cursor: not-allowed;" disabled>
                                    <i class="fas fa-ban"></i> Producto Agotado
                                </button>
                            @endif
                            <a href="{{ route('catalogo') }}" class="btn-secondary">Volver al Catalogo</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Alerta de producto agregado -->
    <div id="alerta-carrito" style="display:none; position:fixed; top:20px; right:20px; background:#28a745; color:white; padding:15px 25px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.2); z-index:9999; font-size:0.95rem;">
        <i class="fas fa-check-circle"></i> Producto agregado al carrito
    </div>

    <script>
        function agregarAlCarrito() {
            const producto = {
                id: {{ $id }},
                nombre: "{{ addslashes($producto['nombre']) }}",
                precio: "{{ $producto['precio'] }}",
                precioNum: {{ (float) str_replace(['$', ',', ' MXN'], '', $producto['precio']) }},
                imagen: "{{ asset($producto['imagen']) }}",
                marca: "{{ addslashes($producto['marca']) }}",
                cantidad: 1
            };

            let carrito = JSON.parse(localStorage.getItem('macuin_carrito') || '[]');

            // Si ya existe, incrementar cantidad
            const idx = carrito.findIndex(p => p.id === producto.id);
            if (idx >= 0) {
                carrito[idx].cantidad++;
            } else {
                carrito.push(producto);
            }

            localStorage.setItem('macuin_carrito', JSON.stringify(carrito));

            // Mostrar alerta
            const alerta = document.getElementById('alerta-carrito');
            alerta.style.display = 'block';
            setTimeout(() => { alerta.style.display = 'none'; }, 2500);
        }
    </script>
</body>
</html>
