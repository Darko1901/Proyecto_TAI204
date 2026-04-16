<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - MACUIN</title>
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
            <a href="{{ route('perfil') }}" class="nav-item active"><i class="fas fa-user-circle"></i> Mi Perfil</a>
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
        <div style="max-width:800px; margin:0 auto;">
            <h2 style="font-size: 1.8rem; font-weight: 800; color: #333; margin-bottom: 25px;">Configuración de Perfil</h2>
            
            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            <div class="card" style="padding:40px;">
                <form method="POST" action="{{ route('perfil.update') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group form-group-half">
                            <label>Nombre(s)</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $usuario['nombre'] ?? '') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-group-half">
                            <label>Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $usuario['apellido_paterno'] ?? '') }}" required>
                        </div>

                        <div class="form-group form-group-half">
                            <label>Apellido Materno</label>
                            <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $usuario['apellido_materno'] ?? '') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Correo electrónico <span style="font-size:0.75rem; color:#999;">(No editable)</span></label>
                        <input type="email" value="{{ $usuario['correo'] ?? '' }}" disabled style="background:#f5f5f5; color:#999;">
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $usuario['telefono'] ?? '') }}" required maxlength="10" pattern="\d{10}" oninput="this.value=this.value.replace(/[^0-9]/g, '');">
                    </div>

                    <div style="margin-top:30px; border-top:1px solid #f0f0f0; padding-top:20px;">
                        <h4 style="margin-bottom:15px; color:#333;">Actualizar Seguridad</h4>
                        <div class="form-group">
                            <label>Nueva Contraseña <span style="font-size:0.75rem; color:#999;">(Opcional)</span></label>
                            <input type="password" name="password" placeholder="Min. 8 caracteres">
                        </div>
                    </div>

                    <button type="submit" class="btn-login" style="width:auto; padding:15px 40px; border-radius:10px; display:flex; align-items:center; gap:10px; margin-top:20px;">
                        <i class="fas fa-save"></i> GUARDAR CAMBIOS
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
