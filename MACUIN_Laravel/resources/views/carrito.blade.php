<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - MACUIN</title>
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
            <a href="{{ route('catalogo') }}" class="nav-item"><i class="fas fa-store"></i> Catálogo</a>
            <a href="{{ route('pedidos') }}" class="nav-item"><i class="fas fa-box"></i> Pedidos</a>
            <a href="{{ route('perfil') }}" class="nav-item"><i class="fas fa-user-circle"></i> Mi Perfil</a>
            <a href="{{ route('carrito') }}" class="nav-item active cart-icon"><i class="fas fa-shopping-cart"></i></a>
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
        <div class="carrito-wrapper" style="max-width:1200px; margin:0 auto;">
            <div id="carrito-con-productos" style="display:none;">
                <h2 style="font-size: 1.8rem; font-weight: 800; color: #333; margin-bottom: 25px;">Mi Carrito</h2>
                <div class="card" style="padding:25px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="carrito-body"></tbody>
                        <tfoot>
                            <tr style="background:#f8f9fa;">
                                <td colspan="3" style="text-align:right; font-size:1.2rem; font-weight:600; padding:20px;">TOTAL:</td>
                                <td colspan="2"><strong id="carrito-total" style="font-size:1.8rem; color:#b71c1c;">$0.00</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div style="margin-top:30px; display:flex; justify-content:space-between; align-items:center;">
                    <button onclick="vaciarCarrito()" style="background:#fff; color:#f44336; border:1px solid #ffcdd2; padding:12px 25px; border-radius:10px; font-weight:700; cursor:pointer;"><i class="fas fa-trash"></i> Vaciar Carrito</button>
                    <a href="{{ route('checkout') }}" class="btn-login" style="width:auto; padding:15px 50px; font-weight:800; border-radius:12px;">CONTINUAR AL PAGO <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div id="carrito-vacio" style="display:none; text-align:center; padding:100px 40px;" class="card">
                <div style="background:#ffebee; width:100px; height:100px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 30px;">
                    <i class="fas fa-cart-plus fa-3x" style="color:var(--primary);"></i>
                </div>
                <h3 style="font-size:1.8rem; color:#333;">Tu carrito está vacío</h3>
                <p style="color:#666; margin-bottom:40px;">Parece que aún no has agregado nada. ¡Explora nuestro catálogo ahora!</p>
                <a href="{{ route('catalogo') }}" class="btn-login" style="width:auto; padding:15px 40px; display:inline-block; border-radius:10px;">IR AL CATÁLOGO</a>
            </div>
        </div>
    </main>

    <script>
        function cargarCarrito() {
            const carrito = JSON.parse(localStorage.getItem('macuin_carrito') || '[]');
            const tbody = document.getElementById('carrito-body');
            tbody.innerHTML = '';
            if (carrito.length === 0) {
                document.getElementById('carrito-con-productos').style.display = 'none';
                document.getElementById('carrito-vacio').style.display = 'block';
                return;
            }
            document.getElementById('carrito-con-productos').style.display = 'block';
            document.getElementById('carrito-vacio').style.display = 'none';
            let total = 0;
            carrito.forEach((item, index) => {
                const sub = item.precioNum * item.cantidad;
                total += sub;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding:15px 0;">
                        <div style="display:flex; align-items:center; gap:15px;">
                            <img src="${item.imagen}" alt="${item.nombre}" style="width:60px; height:60px; object-fit:contain; background:#f9f9f9; padding:5px; border-radius:8px;">
                            <div>
                                <strong style="font-size:0.95rem; color:#333;">${item.nombre}</strong><br>
                                <small style="color:#b71c1c; font-weight:700;">${item.marca}</small>
                            </div>
                        </div>
                    </td>
                    <td>${item.precio}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <button onclick="cambiarCant(${index}, -1)" style="width:28px; height:28px; border:1px solid #ddd; background:#fff; border-radius:6px; cursor:pointer;">-</button>
                            <span style="min-width:20px; text-align:center; font-weight:700;">${item.cantidad}</span>
                            <button onclick="cambiarCant(${index}, 1)" style="width:28px; height:28px; border:1px solid #ddd; background:#fff; border-radius:6px; cursor:pointer;">+</button>
                        </div>
                    </td>
                    <td style="font-weight:900; color:#333;">$${sub.toLocaleString()}</td>
                    <td><button onclick="borrarProd(${index})" style="color:#f44336; background:none; border:none; cursor:pointer;"><i class="fas fa-times-circle fa-lg"></i></button></td>
                `;
                tbody.appendChild(tr);
            });
            document.getElementById('carrito-total').textContent = '$' + total.toLocaleString() + ' MXN';
        }
        function cambiarCant(index, delta) {
            let car = JSON.parse(localStorage.getItem('macuin_carrito'));
            const item = car[index];
            const maxStock = item.stock || 999; // Default si no tuviera el campo, aunque ahora lo tiene
            const nuevaCant = item.cantidad + delta;

            if (delta > 0 && nuevaCant > maxStock) {
                alert(`¡Máximo stock alcanzado!\nSolo hay ${maxStock} unidades disponibles de este producto.`);
                return;
            }

            item.cantidad = nuevaCant;
            if(item.cantidad < 1) car.splice(index, 1);
            localStorage.setItem('macuin_carrito', JSON.stringify(car));
            cargarCarrito();
        }
        function borrarProd(index) {
            let car = JSON.parse(localStorage.getItem('macuin_carrito'));
            car.splice(index, 1);
            localStorage.setItem('macuin_carrito', JSON.stringify(car));
            cargarCarrito();
        }
        function vaciarCarrito() {
            if(confirm('¿Vaciar carrito?')) { localStorage.removeItem('macuin_carrito'); cargarCarrito(); }
        }
        cargarCarrito();
    </script>
</body>
</html>
