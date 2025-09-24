<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales de Acceso - CERCAP</title>
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
        .credentials {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .credential-row {
            display: flex;
            margin-bottom: 10px;
        }
        .credential-label {
            font-weight: bold;
            width: 120px;
            color: #555;
        }
        .credential-value {
            flex: 1;
            font-family: monospace;
            background-color: #e9ecef;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            color: #7f8c8d;
            font-size: 14px;
        }
        .security {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
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
            <h1>Credenciales de Acceso</h1>
            <p>Clínica de Rehabilitación Cardiopulmonar</p>
        </div>
        
        <div class="content">
            <h2>¡Bienvenido a CERCAP!</h2>
            
            <p>Hola <strong>{{ $user->nombre }} {{ $user->apellidoPat }}</strong>,</p>
            
            <p>Se ha creado tu cuenta en el sistema CERCAP. A continuación encontrarás tus credenciales de acceso:</p>
            
            <div class="credentials">
                <h3>Credenciales de Acceso</h3>
                <div class="credential-row">
                    <div class="credential-label">Email:</div>
                    <div class="credential-value">{{ $user->email }}</div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Contraseña:</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>
            </div>
            
            <div class="warning">
                <strong>Importante:</strong> Por motivos de seguridad, te recomendamos cambiar tu contraseña en tu primer inicio de sesión.
            </div>
            
            <div class="security">
                <strong>Información de Seguridad:</strong><br>
                • Guarda estas credenciales en un lugar seguro.<br>
                • No compartas tu contraseña con nadie.<br>
                • Si sospechas que tu cuenta ha sido comprometida, contacta inmediatamente al administrador.
            </div>
            
            <p>Puedes acceder al sistema usando estas credenciales. Si tienes alguna pregunta, no dudes en contactarnos.</p>
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
