<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { text-align: center; border-bottom: 2px solid #e53935; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #e53935; }
        .content { padding: 20px 0; }
        .footer { font-size: 12px; color: #888; text-align: center; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #e53935; color: #fff !important; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Autopartes MACUIN</div>
        </div>
        <div class="content">
            <h2>Hola, {{ $nombre }}</h2>
            <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta asociada al correo <strong>{{ $email }}</strong>.</p>
            <p>Para continuar con el proceso, haz clic en el siguiente enlace de seguridad:</p>
            <p style="text-align: center;">
                <a href="{{ url('/recuperar/cambiar?email=' . $email) }}" class="btn">Restablecer Contraseña</a>
            </p>
            <p>Si tú no realizaste esta solicitud, puedes ignorar este correo con total seguridad.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Autopartes MACUIN. Todos los derechos reservados.<br>
            Este es un correo automático, por favor no respondas.
        </div>
    </div>
</body>
</html>
