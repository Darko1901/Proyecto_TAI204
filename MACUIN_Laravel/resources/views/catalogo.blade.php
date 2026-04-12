<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Autopartes MACUIN</title>
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
            <a href="{{ route('catalogo') }}" class="nav-item active"><i class="fas fa-store"></i> Catálogo</a>
            <a href="{{ route('pedidos') }}" class="nav-item"><i class="fas fa-box"></i> Pedidos</a>
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
        <!-- Filtros y Búsqueda -->
        <div class="card" style="margin-bottom: 25px; padding: 25px;">
            <form action="{{ route('catalogo') }}" method="GET" style="display:flex; gap:20px; align-items:center;">
                <div style="flex:1; position:relative;">
                    <input type="text" name="q" value="{{ $q }}" placeholder="¿Qué pieza buscas hoy?" style="width:100%; padding:15px 45px; border-radius:10px; border:1px solid #ddd; font-size:1rem;">
                    <i class="fas fa-search" style="position:absolute; left:18px; top:50%; transform:translateY(-50%); color:#999;"></i>
                </div>
                
                <div style="width:200px;">
                    <select name="sort" onchange="this.form.submit()" style="width:100%; padding:14px; border-radius:10px; border:1px solid #ddd; font-weight:600; cursor:pointer;">
                        <option value="" {{ $sort == '' ? 'selected' : '' }}>Relevancia</option>
                        <option value="price_asc" {{ $sort == 'price_asc' ? 'selected' : '' }}>Menor precio</option>
                        <option value="price_desc" {{ $sort == 'price_desc' ? 'selected' : '' }}>Mayor precio</option>
                    </select>
                </div>

                <button type="submit" class="btn-login" style="width:auto; padding:15px 40px; margin-top:0; border-radius:10px;">BUSCAR</button>
                @if($q || $sort)
                    <a href="{{ route('catalogo') }}" style="color:#666; text-decoration:none; font-weight:600; font-size:0.9rem;">Limpiar</a>
                @endif
            </form>
        </div>

        <div id="productos-grid" class="dashboard-cards">
            @forelse($items as $prod)
            <a href="{{ route('detalle_producto', $prod['id']) }}" class="card-link">
                <div class="card" style="text-align:center; padding: 25px; height:100%; display:flex; flex-direction:column;">
                    <div style="height:200px; display:flex; justify-content:center; align-items:center; margin-bottom: 15px;">
                        @if(isset($prod['imagen']) && $prod['imagen'])
                            <img src="http://localhost:8080{{ $prod['imagen'] }}" alt="{{ $prod['nombre'] }}" style="max-width:100%; max-height:100%; object-fit:contain;" onerror="this.src='{{ asset('img/placeholder.png') }}'">
                        @else
                            <img src="{{ asset('img/placeholder.png') }}" alt="Sin imagen" style="width:140px; opacity:0.3;">
                        @endif
                    </div>
                    
                    <div style="flex:1;">
                        <span style="font-size:0.7rem; color:var(--primary); font-weight:800; text-transform:uppercase; margin-bottom:5px;">{{ $prod['marca'] ?? 'GENUINE PARTS' }}</span>
                        <h3 style="font-size:1.1rem; color:#333; margin-top:5px; height:2.8rem; overflow:hidden;">{{ $prod['nombre'] }}</h3>
                        
                        @if(isset($prod['review_count']) && $prod['review_count'] > 0)
                        <div style="margin: 8px 0; color: #fbc02d; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 5px;">
                            <div class="stars">
                                @for($i=1; $i<=5; $i++)
                                    <i class="{{ $i <= round($prod['rating'] ?? 0) ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </div>
                            <span style="color: #999; font-size: 0.75rem;">({{ $prod['review_count'] }})</span>
                        </div>
                        @endif
                        
                        <div style="margin: 15px 0; color:#b71c1c; font-size:1.6rem; font-weight:900;">
                            <span style="font-size:0.9rem; vertical-align:top;">$</span> {{ number_format($prod['price_num'], 2) }}
                        </div>

                        <div style="font-size: 0.85rem; color: #1b5e20; background:#e8f5e9; padding:8px; border-radius:6px; margin-bottom:15px; border-left:4px solid #4caf50;">
                            <i class="fas fa-shipping-fast"></i> Entrega: <strong>En 3 días</strong>
                        </div>
                        
                        @if($prod['stock'] > 0)
                            <div style="font-size:0.75rem; font-weight:700; color: {{ $prod['stock'] < 10 ? '#ef5350' : '#2196f3' }};">
                                <i class="fas {{ $prod['stock'] < 10 ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i> 
                                {{ $prod['stock'] }} {{ $prod['stock'] == 1 ? 'disponible' : 'disponibles' }}
                            </div>
                        @else
                            <span style="font-size:0.75rem; color:#999; font-weight:700;"><i class="fas fa-times-circle"></i> Agotado</span>
                        @endif
                    </div>
                </div>
            </a>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 100px 0;">
                    <i class="fas fa-search-minus fa-4x" style="color: #ddd; margin-bottom: 20px;"></i>
                    <h3 style="color: #999;">No hay coincidencias para tu búsqueda</h3>
                </div>
            @endforelse
        </div>

        @if($totalPages > 1)
        <div style="display:flex; justify-content:center; gap:8px; margin-top:60px;">
            @if($currentPage > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="btn-logout" style="background:#fff;">&laquo; Anterior</a>
            @endif

            @for($i = 1; $i <= $totalPages; $i++)
                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" 
                   class="btn-logout {{ $i == $currentPage ? 'active' : '' }}"
                   style="{{ $i == $currentPage ? 'background:var(--primary); color:white; border-color:var(--primary);' : 'background:#fff;' }}">
                    {{ $i }}
                </a>
            @endfor

            @if($currentPage < $totalPages)
                <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="btn-logout" style="background:#fff;">Siguiente &raquo;</a>
            @endif
        </div>
        @endif
    </main>

    @include('partials.footer')
</body>
</html>
