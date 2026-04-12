<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - MACUIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="logo-section">
                <div style="margin-bottom:20px;">
                    <a href="{{ route('login') }}" style="text-decoration:none; color:#64748b; font-weight:700; display:flex; align-items:center; gap:8px; transition:0.3s; width:fit-content;" onmouseover="this.style.color='#b71c1c'" onmouseout="this.style.color='#64748b'">
                        <i class="fas fa-arrow-left"></i> REGRESAR AL LOGIN
                    </a>
                </div>
                <h1>Autopartes<br>MACUIN</h1>

            </div>

            <div class="login-section">
                <h2>Recuperar acceso</h2>
                <p style="color: #666; font-size: 0.9rem; margin-bottom: 20px;">Ingresa tu correo electrónico y te enviaremos las instrucciones para restablecer tu contraseña.</p>

                @if(session('success'))
                    <div style="background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 0.9rem;">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 0.9rem;">
                        <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('recuperar.post') }}">
                    @csrf
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>
                    </div>

                    <button type="submit" class="btn-login">Enviar enlace</button>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="{{ route('login') }}" class="forgot-password">
                            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="right-panel">
            <!-- Espacio para imagen o diseño -->
        </div>
    </div>
</body>
</html>
