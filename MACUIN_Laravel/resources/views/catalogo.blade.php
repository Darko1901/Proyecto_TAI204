<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogo - Autopartes MACUIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Autopartes MACUIN</h1>
            <div class="user-info">
                <a href="{{ route('carrito') }}" class="btn-logout" title="Mi Carrito"><i class="fas fa-shopping-cart"></i></a>
                <a href="{{ route('dashboard') }}" class="btn-logout">Volver al Dashboard</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <h2>Catalogo de Productos</h2>

            <!-- Filtro rapido por categorias -->
            <div class="catalogo-filtros">
                <a href="#todas" class="filtro-btn active" onclick="filtrarCategoria('todas', this)">Todas</a>
                @foreach($categorias as $nombreCat => $prods)
                    <a href="#{{ Str::slug($nombreCat) }}" class="filtro-btn" onclick="filtrarCategoria('{{ Str::slug($nombreCat) }}', this)">{{ $nombreCat }}</a>
                @endforeach
            </div>

            @foreach($categorias as $nombreCat => $prods)
                <div class="categoria-section" data-categoria="{{ Str::slug($nombreCat) }}">
                    <h3 class="categoria-titulo">
                        <i class="fas fa-chevron-right"></i> {{ $nombreCat }}
                        <span class="categoria-count">({{ count($prods) }} productos)</span>
                    </h3>
                    <div class="dashboard-cards">
                        @foreach($prods as $id => $prod)
                            <a href="{{ route('detalle_producto', $id) }}" class="card-link">
                                <div class="card producto-card">
                                    <div class="producto-icono">
                                        <i class="{{ $prod['icono'] }}"></i>
                                    </div>
                                    <h3>{{ $prod['nombre'] }}</h3>
                                    <p class="producto-marca"><i class="fas fa-tag"></i> {{ $prod['marca'] }}</p>
                                    <p class="producto-compat"><i class="fas fa-car"></i> {{ Str::limit($prod['compatibilidad'], 40) }}</p>
                                    <p class="producto-precio">{{ $prod['precio'] }}</p>
                                    @if(!$prod['disponible'])
                                        <span class="badge-agotado">Agotado</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </main>
    </div>

    <script>
        function filtrarCategoria(slug, btn) {
            // Activar boton
            document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Mostrar/ocultar secciones
            document.querySelectorAll('.categoria-section').forEach(sec => {
                if (slug === 'todas') {
                    sec.style.display = 'block';
                } else {
                    sec.style.display = sec.dataset.categoria === slug ? 'block' : 'none';
                }
            });
        }
    </script>
</body>
</html>
