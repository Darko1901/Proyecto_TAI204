<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autopartes MACUIN - Mi Perfil</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<div class="dashboard-container">

    <header class="dashboard-header">
        <h1>Autopartes MACUIN</h1>
        <div class="user-info">
            <span>Mi Perfil</span>
            <a href="{{ route('dashboard') }}" class="btn-logout">
                Volver
            </a>
        </div>
    </header>

    <main class="dashboard-content">

        <h2>Actualizar Información Personal</h2>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        
        <div class="card">

            <form method="POST" action="{{ route('perfil.update') }}">
                @csrf

                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="correo" value="{{ old('correo', $usuario->correo ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $usuario->telefono ?? '') }}" required>
                </div>

                <button type="submit" name="guardar_datos" class="btn-login">
                    Guardar Cambios
                </button>

            </form>

        </div>

        
        <div class="card" style="margin-top:30px;">

            <h3 style="color:#e57373; margin-bottom:20px;">
                Cambiar Contraseña
            </h3>

            <form method="POST" action="{{ route('perfil.cambiar-password') }}">
                @csrf

                <div class="form-group">
                    <label>Contraseña Actual</label>
                    <input type="password" name="actual" required>
                </div>

                <div class="form-group">
                    <label>Nueva Contraseña</label>
                    <input type="password" name="nueva" required>
                </div>

                <div class="form-group">
                    <label>Confirmar Nueva Contraseña</label>
                    <input type="password" name="confirmar" required>
                </div>

                <button type="submit" name="cambiar_password" class="btn-login">
                    Actualizar Contraseña
                </button>

            </form>

        </div>

    </main>

</div>

</body>
</html>
