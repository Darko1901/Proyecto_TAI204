<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $producto['nombre_producto'] ?? 'Detalle de Producto' }} - MACUIN</title>
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
        <div class="detalle-main" style="max-width:1200px; margin:0 auto; display:grid; grid-template-columns:1fr 1fr; gap:60px; margin-bottom:50px;">
            <div class="card" style="padding:40px; display:flex; align-items:center; justify-content:center; border-radius:20px;">
                @if(isset($producto['imagen']) && $producto['imagen'])
                    <img src="http://localhost:8080{{ $producto['imagen'] }}" alt="{{ $producto['nombre_producto'] ?? 'Producto' }}" style="max-width:100%; max-height:400px; object-fit:contain;" id="main-image">
                @else
                    <img src="{{ asset('img/placeholder.png') }}" alt="Sin imagen" style="width:200px; opacity:0.3;">
                @endif
            </div>

            <div class="info-section">
                <span style="font-size:0.75rem; color:#b71c1c; font-weight:800; text-transform:uppercase; letter-spacing:1px;">{{ $producto['marca'] ?? 'MACUIN ORIGINAL' }}</span>
                <h1 style="font-size: 2.2rem; font-weight: 800; color: #333; margin: 10px 0 20px 0;">{{ $producto['nombre_producto'] ?? 'Producto sin nombre' }}</h1>
                
                @if(isset($producto['review_count']) && $producto['review_count'] > 0)
                <div style="margin-bottom:25px; display:flex; align-items:center; gap:10px;">
                    <div style="color: #fbc02d; font-size:1.1rem;">
                        @for($i=1; $i<=5; $i++)
                            <i class="{{ $i <= round($producto['avg_rating'] ?? 0) ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                    </div>
                    <span style="color:#666; font-size:0.9rem;">({{ $producto['review_count'] }} opiniones verificadas)</span>
                </div>
                @endif

                <div class="card" style="padding:30px; border-radius:15px; background:#fff;">
                    <div style="font-size: 2.5rem; font-weight: 900; color: #b71c1c; margin-bottom:25px;">
                        <span style="font-size:1.2rem; vertical-align:top;">$</span> {{ number_format($producto['precio'] ?? 0, 2) }}
                    </div>

                    @if(($producto['stock'] ?? 0) > 0)
                        <div style="margin-bottom:20px; font-weight:700; color:#1b5e20;">
                            <i class="fas fa-check-circle"></i> DISPONIBLE PARA ENVÍO INMEDIATO
                            <div style="font-size:0.85rem; color:#666; margin-top:5px; font-weight:400;">
                                Quedan <strong>{{ $producto['stock'] }} {{ $producto['stock'] == 1 ? 'unidad' : 'unidades' }}</strong> en el almacén central.
                            </div>
                        </div>
                        <div style="display:flex; gap:15px;">
                            <div style="display:flex; align-items:center; border:1px solid #ddd; border-radius:10px; padding:5px; background:#f9f9f9;">
                                <button onclick="changeCant(-1)" style="padding:10px 15px; border:none; background:none; cursor:pointer; font-weight:700; color:#555;">-</button>
                                <input type="number" id="buy-qty" value="1" min="1" max="{{ $producto['stock'] ?? 1 }}" style="width:45px; border:none; text-align:center; font-weight:700; background:none; font-size:1.1rem;">
                                <button onclick="changeCant(1)" style="padding:10px 15px; border:none; background:none; cursor:pointer; font-weight:700; color:#555;">+</button>
                            </div>
                            <div style="display:flex; flex-direction:column; gap:12px; flex:1;">
                                <button onclick="addCart()" class="btn-login" style="width:100%; margin:0; padding:15px; border-radius:10px; font-weight:900; letter-spacing:1px; background:#f1f5f9; color:#1e293b; border:2px solid #e2e8f0;">
                                    AGREGAR AL CARRITO
                                </button>
                                <button onclick="buyNow()" class="btn-login" style="width:100%; margin:0; padding:15px; border-radius:10px; font-weight:900; letter-spacing:1px; background:linear-gradient(135deg, #e53935 0%, #b71c1c 100%); border:none;">
                                    COMPRAR AHORA
                                </button>
                            </div>
                        </div>
                    @else
                        <div style="padding:20px; text-align:center; color:#f44336; background:#ffebee; border-radius:10px; font-weight:800;">
                            <i class="fas fa-clock"></i> PRODUCTO AGOTADO
                        </div>
                    @endif
                    
                    <div style="margin-top:25px; padding-top:20px; border-top:1px solid #f0f0f0; display:flex; flex-direction:column; gap:12px; font-size:0.85rem; color:#666;">
                        <span><i class="fas fa-shield-alt" style="color:var(--primary); margin-right:8px;"></i> Garantía MACUIN por 12 meses.</span>
                        <span><i class="fas fa-certificate" style="color:#2196f3; margin-right:8px;"></i> Repuesto 100% Genuino Garantizado.</span>
                    </div>
                </div>

                <div style="margin-top:40px;">
                    <h3 style="font-size: 1.3rem; margin-bottom: 15px; color: #333;">Descripción del Componente</h3>
                    <p style="color: #666; line-height: 1.8; font-size: 1rem;">{{ $producto['descripcion'] ?? 'No hay descripción disponible para este producto.' }}</p>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE RESEÑAS -->
        <div class="reviews-wrapper" style="max-width:1200px; margin:0 auto; display:grid; grid-template-columns:1.2fr 1fr; gap:60px; border-top:1px solid #eee; padding-top:50px; margin-top:50px;">
            <div>
                <h3 style="font-size:1.8rem; font-weight:800; color:#333; margin-bottom:30px;">Opiniones de Clientes</h3>
                @forelse($resenas ?? [] as $r)
                    <div class="card" style="padding:25px; margin-bottom:20px; border-radius:15px; border:1px solid #f0f0f0;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                            <div>
                                <div style="color: #fbc02d; margin-bottom:5px;">
                                    @for($i=1; $i<=5; $i++)
                                        <i class="{{ $i <= $r['calificacion'] ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </div>
                                <strong style="color:#333;">Comentario verificado</strong>
                            </div>
                            <span style="font-size:0.75rem; color:#999;">{{ isset($r['fecha_reseña']) ? \Carbon\Carbon::parse($r['fecha_reseña'])->format('d/m/Y') : (isset($r['fecha_resena']) ? \Carbon\Carbon::parse($r['fecha_resena'])->format('d/m/Y') : 'Hoy') }}</span>
                        </div>
                        <p style="color:#666; font-size:0.95rem; line-height:1.6; margin:0; font-style:italic;">"{{ $r['comentario'] }}"</p>
                    </div>
                @empty
                    <div style="text-align:center; padding:50px; background:#f9f9f9; border-radius:15px;">
                        <i class="fas fa-comment-slash fa-3x" style="color:#ddd; margin-bottom:20px;"></i>
                        <p style="color:#999;">Aún no hay opiniones sobre este producto.</p>
                        <p style="color:#999; font-size:0.8rem;">¡Sé el primero en calificarlo!</p>
                    </div>
                @endforelse
            </div>

            <div>
                <div class="card" style="padding:40px; border-radius:15px; position:sticky; top:20px;">
                    <h4 style="font-size:1.4rem; font-weight:800; margin-bottom:10px;">Danos tu opinión</h4>
                    <p style="font-size:0.9rem; color:#666; margin-bottom:25px;">Tu experiencia ayuda a miles de usuarios a elegir la mejor refacción.</p>
                    
                    <form action="{{ route('resena.crear') }}" method="POST" id="review-form">
                        @csrf
                        <input type="hidden" name="id_producto" value="{{ $id }}">
                        
                        <div class="form-group" style="margin-bottom:25px;">
                            <label style="display:block; margin-bottom:10px; font-weight:700; font-size:0.85rem;">CALIFICACIÓN</label>
                            <div class="star-rating" style="display:flex; gap:10px; font-size:1.8rem; color:#ddd; cursor:pointer;">
                                <i class="far fa-star rating-star" data-val="1"></i>
                                <i class="far fa-star rating-star" data-val="2"></i>
                                <i class="far fa-star rating-star" data-val="3"></i>
                                <i class="far fa-star rating-star" data-val="4"></i>
                                <i class="far fa-star rating-star" data-val="5"></i>
                            </div>
                            <input type="hidden" name="calificacion" id="rating-input" value="0" required>
                        </div>

                        <div class="form-group" style="margin-bottom:25px;">
                            <label style="display:block; margin-bottom:10px; font-weight:700; font-size:0.85rem;">COMENTARIO</label>
                            <textarea name="comentario" rows="4" placeholder="¿Qué te pareció el ajuste, durabilidad y calidad del material?" style="width:100%; border:1px solid #ddd; border-radius:10px; padding:15px; font-family:inherit; resize:none;" required></textarea>
                        </div>

                        <button type="submit" class="btn-login" style="width:100%; letter-spacing:1px; font-weight:800;">ENVIAR MI OPINIÓN</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        function changeCant(d) {
            const i = document.getElementById('buy-qty');
            let v = parseInt(i.value) + d;
            if(v<1) v=1; if(v > {{ $producto['stock'] ?? 0 }}) v = {{ $producto['stock'] ?? 0 }};
            i.value = v;
        }
        function addCart(shouldRedirect = false) {
            const stockMax = {{ $producto['stock'] ?? 0 }};
            const q = parseInt(document.getElementById('buy-qty').value);
            
            let car = JSON.parse(localStorage.getItem('macuin_carrito') || '[]');
            const idx = car.findIndex(x => x.id === {{ $producto['id_producto'] ?? 0 }});
            
            let currentInCart = idx > -1 ? car[idx].cantidad : 0;
            
            if ((currentInCart + q) > stockMax) {
                alert(`¡Lo sentimos! No hay stock suficiente. \n\nDisponible: ${stockMax} unidades\nYa tienes ${currentInCart} en tu carrito.\nSolo puedes agregar ${stockMax - currentInCart} más.`);
                return false;
            }

            const p = {
                id: {{ $producto['id_producto'] ?? 0 }},
                nombre: '{{ $producto['nombre_producto'] ?? 'Producto' }}',
                marca: '{{ $producto['marca'] ?? 'MACUIN' }}',
                precio: '$ {{ number_format($producto['precio'] ?? 0, 2) }}',
                precioNum: {{ $producto['precio'] ?? 0 }},
                imagen: 'http://localhost:8080{{ $producto['imagen'] ?? '' }}',
                cantidad: q,
                stock: stockMax
            };
            
            if(idx>-1) car[idx].cantidad+=q; else car.push(p);
            localStorage.setItem('macuin_carrito', JSON.stringify(car));
            
            if(shouldRedirect) return true;

            const b = document.querySelectorAll('.btn-login')[0];
            const originalText = b.innerHTML;
            b.innerHTML = '¡AGREGADO!'; b.style.background = '#1b5e20'; b.style.color = '#fff';
            setTimeout(() => { 
                b.innerHTML = originalText; 
                b.style.background = ''; b.style.color = '';
                if(confirm('¿Ir al carrito?')) window.location.href = "{{ route('carrito') }}"; 
            }, 1000);
            return true;
        }

        function buyNow() {
            if(addCart(true)) {
                window.location.href = "{{ route('checkout') }}";
            }
        }

        // Lógica de Estrellas
        document.querySelectorAll('.rating-star').forEach(star => {
            star.addEventListener('mouseover', function() {
                const val = this.getAttribute('data-val');
                highlightStars(val);
            });
            star.addEventListener('mouseout', function() {
                const currentVal = document.getElementById('rating-input').value;
                highlightStars(currentVal);
            });
            star.addEventListener('click', function() {
                const val = this.getAttribute('data-val');
                document.getElementById('rating-input').value = val;
                highlightStars(val);
            });
        });

        function highlightStars(val) {
            document.querySelectorAll('.rating-star').forEach(s => {
                const sVal = s.getAttribute('data-val');
                if(sVal <= val) {
                    s.classList.replace('far', 'fas');
                    s.style.color = '#fbc02d';
                } else {
                    s.classList.replace('fas', 'far');
                    s.style.color = '#ddd';
                }
            });
        }
    </script>
</body>
</html>
