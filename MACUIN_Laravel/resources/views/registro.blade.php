<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autopartes MACUIN - Registro</title>
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
                <h2>Registro</h2>

                @if($errors->any())
                    <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                        {{ $errors->first() }}
                    </div>
                @endif
                
                <form method="POST" action="{{ route('registro.post') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group form-group-half">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" required value="{{ old('nombre') }}">
                        </div>
                        
                        <div class="form-group form-group-half">
                            <label for="apellido_paterno">Apellido Paterno</label>
                            <input type="text" id="apellido_paterno" name="apellido_paterno" required value="{{ old('apellido_paterno') }}">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-group-half">
                            <label for="apellido_materno">Apellido Materno</label>
                            <input type="text" id="apellido_materno" name="apellido_materno" required value="{{ old('apellido_materno') }}">
                        </div>
                        
                        <div class="form-group form-group-half">
                            <label for="correo">Correo electrónico</label>
                            <input type="email" id="correo" name="correo" required value="{{ old('correo') }}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye-slash eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <p class="register-link" style="margin-top: 15px; text-align: center;">
                        ¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a>
                    </p>
                    
                    <button type="submit" class="btn-login" style="margin-top: 10px;">Registrar</button>
                </form>
            </div>
        </div>
        
        <div class="right-panel">
            <!-- Espacio para imagen o diseño -->
        </div>
    </div>
    
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
