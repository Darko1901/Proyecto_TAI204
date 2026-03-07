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
                <h1>Autopartes<br>MACUIN</h1>
            </div>

            <div class="login-section">
                <h2>Recuperar acceso</h2>
                <p style="color: #666; font-size: 0.9rem; margin-bottom: 20px;">Ingresa tu correo electrónico y te enviaremos las instrucciones para restablecer tu contraseña.</p>

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
