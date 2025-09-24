<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - CERCAP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            max-width: 200px;
            height: 90px;
            object-fit: contain;
        }
        .header h1 {
            color: #2c3e50;
            margin: 15px 0 10px 0;
        }
        .header p {
            color: #7f8c8d;
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .content h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .content p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background-color: #e74c3c;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #c0392b;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            color: #7f8c8d;
            font-size: 14px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .security {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://pacientesrhc.com/img/logo.png" alt="CERCAP Logo">
            <h1>Restablecer Contraseña</h1>
            <p>Clínica de Rehabilitación Cardiopulmonar</p>
        </div>
        
        <div class="content">
            <h2>Solicitud de Restablecimiento de Contraseña</h2>
            
            <p>Hola <strong>{{ $user->nombre }} {{ $user->apellidoPat }}</strong>,</p>
            
            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en CERCAP. Si fuiste tú quien hizo esta solicitud, haz clic en el siguiente botón para crear una nueva contraseña:</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">Restablecer Contraseña</a>
            </div>
            
            <div class="warning">
                <strong>Importante:</strong> Este enlace de restablecimiento expirará en 1 hora por motivos de seguridad.
            </div>
            
            <p>Si no puedes hacer clic en el botón, copia y pega la siguiente URL en tu navegador:</p>
            <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace;">{{ $resetUrl }}</p>
            
            <div class="security">
                <strong>Información de Seguridad:</strong><br>
                • Si no solicitaste este restablecimiento, puedes ignorar este correo de forma segura.<br>
                • Tu contraseña actual seguirá siendo válida hasta que la cambies.<br>
                • Te recomendamos usar una contraseña segura con al menos 8 caracteres.
            </div>
            
            <p>Si tienes problemas para acceder a tu cuenta, contacta a nuestro equipo de soporte.</p>
        </div>
        
        <div class="footer">
            <p><strong>CERCAP</strong><br>
            Clínica de Rehabilitación Cardiopulmonar<br>
            Tel: 5526255547<br>
            Email: cercap@example.com</p>
        </div>
    </div>
</body>
</html>
