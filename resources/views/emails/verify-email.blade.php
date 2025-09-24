<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Correo - CERCAP</title>
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
            background-color: #3498db;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #2980b9;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://pacientesrhc.com/img/logo.png" alt="CERCAP Logo">
            <h1>Verificación de Correo Electrónico</h1>
            <p>Clínica de Rehabilitación Cardiopulmonar</p>
        </div>
        
        <div class="content">
            <h2>¡Bienvenido a CERCAP!</h2>
            
            <p>Hola <strong>{{ $user->nombre }} {{ $user->apellidoPat }}</strong>,</p>
            
            <p>Gracias por registrarte en nuestro sistema. Para completar tu registro y activar tu cuenta, necesitamos verificar tu dirección de correo electrónico.</p>
            
            <p>Haz clic en el siguiente botón para verificar tu correo:</p>
            
            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Verificar Correo Electrónico</a>
            </div>
            
            <div class="warning">
                <strong>Importante:</strong> Este enlace de verificación expirará en 24 horas por motivos de seguridad.
            </div>
            
            <p>Si no puedes hacer clic en el botón, copia y pega la siguiente URL en tu navegador:</p>
            <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace;">{{ $verificationUrl }}</p>
            
            <p>Si no creaste esta cuenta, puedes ignorar este correo de forma segura.</p>
        </div>
        
        <div class="footer">
            <p><strong>CERCAP</strong><br>
            Clínica de Rehabilitación Cardiopulmonar<br>
            Tel: 5526255547 / 5526255548<br>
            Email: cercap.cardiopulmonar@gmail.com<br>
            wwww.cercap.mx
        </p>
        </div>
    </div>
</body>
</html>
