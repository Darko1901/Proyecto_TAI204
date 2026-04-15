<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autopartes MACUIN - Pasión por las Refacciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #e53935 0%, #b71c1c 100%);
            --secondary-bg: #f8fafc;
        }
        body { font-family: 'Inter', sans-serif; background: var(--secondary-bg); color: #1e293b; overflow-x: hidden; }

        /* Navbar Premium */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0; width: 100%; z-index: 1000;
            box-shadow: 0 4px 30px rgba(0,0,0,0.03);
            box-sizing: border-box;
        }
        .nav-logo { font-size: 1.5rem; font-weight: 800; color: var(--primary); letter-spacing: -1px; text-transform: uppercase; }
        .nav-links { display: flex; gap: 30px; align-items: center; }
        .nav-links a { text-decoration: none; color: #475569; font-weight: 600; font-size: 0.95rem; transition: 0.3s; }
        .nav-links a:hover { color: var(--primary); }
        .btn-auth { background: var(--primary); color: white !important; padding: 10px 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(229, 57, 53, 0.2); }

        /* Hero Carousel */
        .hero-section { margin-top: 80px; position: relative; height: 600px; overflow: hidden; }
        .carousel-container { display: flex; transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1); height: 100%; }
        .slide { min-width: 100%; position: relative; height: 100%; }
        .slide img { width: 100%; height: 100%; object-fit: cover; filter: brightness(0.7); }
        .slide-content {
            position: absolute; top: 50%; left: 10%; transform: translateY(-50%);
            color: white; max-width: 600px; z-index: 10;
        }
        .slide-content h2 { font-size: 3.5rem; font-weight: 800; margin-bottom: 20px; line-height: 1.1; }
        .slide-content p { font-size: 1.2rem; margin-bottom: 30px; opacity: 0.9; }
        .btn-hero { background: var(--primary); color: white !important; padding: 15px 40px; border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 1.1rem; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }

        /* Categories Section */
        .section-padding { padding: 80px 50px; text-align: center; }
        .title-group { margin-bottom: 50px; }
        .title-group h2 { font-size: 2.2rem; font-weight: 800; color: #0f172a; margin-bottom: 10px; }
        .title-group p { color: #64748b; font-size: 1.1rem; }

        .category-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; }
        .category-card {
            background: white; border-radius: 20px; padding: 40px 20px; text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); cursor: pointer;
            border: 1px solid #f1f5f9; box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }
        .category-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); border-color: #ffcdd2; }
        .category-card i { font-size: 3rem; color: var(--primary); margin-bottom: 20px; }
        .category-card h3 { font-size: 1.25rem; font-weight: 700; margin-bottom: 10px; }
        .category-card p { color: #94a3b8; font-size: 0.9rem; }

        /* Trust Badges */
        .trust-section { background: #0f172a; color: white; padding: 60px 50px; display: flex; justify-content: space-around; flex-wrap: wrap; gap: 30px; }
        .trust-item { display: flex; align-items: center; gap: 20px; text-align: left; }
        .trust-item i { font-size: 2.5rem; color: #ef4444; }
        .trust-info h4 { font-size: 1.1rem; font-weight: 700; }
        .trust-info p { font-size: 0.85rem; opacity: 0.7; }

        /* Controls */
        .carousel-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.2); border: none; width: 50px; height: 50px;
            border-radius: 50%; color: white; cursor: pointer; backdrop-filter: blur(5px);
            z-index: 20; transition: 0.3s;
        }
        .carousel-btn:hover { background: var(--primary); }
        .prev { left: 20px; }
        .next { right: 20px; }

        @media (max-width: 768px) {
            .navbar { padding: 15px 20px; }
            .nav-links { display: none; }
            .slide-content h2 { font-size: 2.5rem; }
            .section-padding { padding: 50px 20px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-logo">MACUIN</div>
        <div class="nav-links">
            <a href="{{ route('catalogo') }}">Catálogo</a>
            @if(session()->has('token'))
                <a href="{{ route('pedidos') }}">Pedidos</a>
                <a href="{{ route('carrito') }}"><i class="fas fa-shopping-cart"></i> Carrito</a>
                <a href="{{ route('dashboard') }}" class="btn-auth">Panel</a>
            @else
                <a href="{{ route('login') }}" class="btn-auth">Acceder</a>
            @endif
        </div>
    </nav>

    <main>
        <div style="max-width:1200px; margin:20px auto 0 auto; padding:0 50px;">
            <a href="javascript:history.back()" style="text-decoration:none; color:#64748b; font-weight:700; display:flex; align-items:center; gap:8px; transition:0.3s; width:fit-content;" onmouseover="this.style.color='#b71c1c'" onmouseout="this.style.color='#64748b'">
                <i class="fas fa-arrow-left"></i> REGRESAR
            </a>
        </div>
        <!-- Hero Carousel -->
        <section class="hero-section">
            <div class="carousel-container" id="carousel">
                <div class="slide">
                    <img src="https://images.unsplash.com/photo-1486006920555-c77dcf18193c?auto=format&fit=crop&q=80&w=2000" alt="Motor">
                    <div class="slide-content">
                        <h2>El Corazón de tu Vehículo merece lo mejor</h2>
                        <p>Descubre nuestra línea premium de refacciones para motor con garantía extendida y envío flash.</p>
                        <a href="{{ route('catalogo', ['id_categoria' => 1]) }}" class="btn-hero">Explorar Motores</a>
                    </div>
                </div>
                <div class="slide">
                    <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=2000" alt="Frenos">
                    <div class="slide-content">
                        <h2>Seguridad Máxima en cada Frenado</h2>
                        <p>Balatas, discos y sensores de alta performance para asegurar tu camino y el de tu familia.</p>
                        <a href="{{ route('catalogo', ['id_categoria' => 2]) }}" class="btn-hero">Ver Sistema de Frenos</a>
                    </div>
                </div>
                <div class="slide">
                    <img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&q=80&w=2000" alt="Accesorios">
                    <div class="slide-content">
                        <h2>Detalles que marcan la diferencia</h2>
                        <p>Estética y funcionalidad con accesorios genuinos MACUIN para personalizar tu auto.</p>
                        <a href="{{ route('catalogo') }}" class="btn-hero">Ir al catálogo</a>
                    </div>
                </div>
            </div>
            <button class="carousel-btn prev" onclick="moveSlide(-1)"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-btn next" onclick="moveSlide(1)"><i class="fas fa-chevron-right"></i></button>
        </section>

        <!-- Trust Section -->
        <section class="trust-section">
            <div class="trust-item">
                <i class="fas fa-shipping-fast"></i>
                <div class="trust-info">
                    <h4>Envío Veloz</h4>
                    <p>Entrega en 24-48 horas a nivel nacional.</p>
                </div>
            </div>
            <div class="trust-item">
                <i class="fas fa-shield-alt"></i>
                <div class="trust-info">
                    <h4>Garantía MACUIN</h4>
                    <p>Todas tus piezas aseguradas por un año.</p>
                </div>
            </div>
            <div class="trust-item">
                <i class="fas fa-headset"></i>
                <div class="trust-info">
                    <h4>Soporte Experto</h4>
                    <p>Asesoría gratuita con mecánicos certificados.</p>
                </div>
            </div>
        </section>

        <!-- Product Categories -->
        <section class="section-padding">
            <div class="title-group">
                <h2>Categorías más Buscadas</h2>
                <p>Encuentra exactamente lo que tu auto necesita entre miles de refacciones.</p>
            </div>

            <div class="category-grid">
                <div class="category-card" onclick="window.location='{{ route('catalogo', ['categoria' => 1]) }}'">
                    <i class="fas fa-engine"></i>
                    <h3>Motor</h3>
                    <p>Componentes internos, juntas y sistemas de inyección.</p>
                </div>
                <div class="category-card" onclick="window.location='{{ route('catalogo', ['categoria' => 2]) }}'">
                    <i class="fas fa-dharmachakra"></i>
                    <h3>Frenos</h3>
                    <p>Discos, balatas y sistemas de seguridad ABS.</p>
                </div>
                <div class="category-card" onclick="window.location='{{ route('catalogo', ['categoria' => 3]) }}'">
                    <i class="fas fa-oil-can"></i>
                    <h3>Lubricantes</h3>
                    <p>Aceites sintéticos y filtros de alta eficiencia.</p>
                </div>
                <div class="category-card" onclick="window.location='{{ route('catalogo', ['categoria' => 4]) }}'">
                    <i class="fas fa-battery-full"></i>
                    <h3>Eléctrico</h3>
                    <p>Baterías, alternadores y sensores electrónicos.</p>
                </div>
            </div>
        </section>

        <!-- Banner Promoción -->
        <section style="background: var(--primary-gradient); padding: 100px 50px; color: white; text-align: center; border-radius: 40px; margin: 0 50px 80px 50px;">
            <h2 style="font-size: 3rem; font-weight: 800; margin-bottom: 20px;">¿Necesitas ayuda con tu pedido?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 40px; opacity: 0.9;">Nuestros expertos en logística y mecánica están listos para asesorarte hoy mismo.</p>
            <a href="https://wa.me/1234567890" class="btn-hero" style="background: white; color: var(--primary) !important;">Contactar por WhatsApp</a>
        </section>
    </main>

    @include('partials.footer')

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const container = document.getElementById('carousel');

        function moveSlide(direction) {
            currentSlide = (currentSlide + direction + slides.length) % slides.length;
            container.style.transform = `translateX(-${currentSlide * 100}%)`;
        }

        // Auto play
        setInterval(() => moveSlide(1), 8000);
    </script>
</body>
</html>
