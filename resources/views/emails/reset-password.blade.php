<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Lynkamed</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f1f5f9;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .header {
            background: #0A1628;
            padding: 28px 30px;
            text-align: center;
        }
        .header .clinic-logo {
            max-width: 160px;
            max-height: 60px;
            object-fit: contain;
            background: white;
            padding: 8px 14px;
            border-radius: 6px;
            margin-bottom: 12px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .header .lynkamed-logo {
            height: 72px;
            width: auto;
            max-width: 520px;
            object-fit: contain;
            margin-bottom: 16px;
        }
        .header h1 {
            color: white;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }
        .header p {
            color: #94a3b8;
            font-size: 13px;
            margin: 0;
        }
        .content {
            padding: 28px 30px;
        }
        .content h2 {
            color: #0A1628;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .content p {
            color: #475569;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 14px;
        }
        .content strong {
            color: #1e293b;
        }
        .button {
            display: inline-block;
            background: #1d4ed8;
            color: white !important;
            padding: 13px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 15px;
            margin: 16px 0;
        }
        .warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            color: #92400e;
            padding: 14px 16px;
            border-radius: 6px;
            margin: 18px 0;
            font-size: 14px;
        }
        .security {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-left: 4px solid #0d9488;
            color: #334155;
            padding: 14px 16px;
            border-radius: 6px;
            margin: 18px 0;
            font-size: 14px;
        }
        .footer {
            background: #f8fafc;
            border-top: 2px solid #0A1628;
            padding: 20px 30px;
            text-align: center;
        }
        .footer p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.8;
            margin: 0;
        }
        .footer strong {
            color: #0A1628;
            font-weight: 700;
        }
        @media only screen and (max-width: 600px) {
            body { padding: 16px 10px; }
            .header, .content, .footer { padding-left: 16px; padding-right: 16px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @php
                $lynkamedSrc = \App\Helpers\EmailHelper::embedMailImage($message ?? null, \App\Helpers\EmailHelper::getLynkamedLogoPath());
            @endphp
            @if($lynkamedSrc)
                <img src="{{ $lynkamedSrc }}" alt="Lynkamed" class="lynkamed-logo">
            @endif
            <h1>Restablecer Contraseña</h1>
        </div>
        
        <div class="content">
            <h2>Solicitud de Restablecimiento de Contraseña</h2>
            
            <p>Hola <strong>{{ $user->nombre }} {{ $user->apellidoPat }}</strong>,</p>
            
            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta. Si fuiste tú quien hizo esta solicitud, haz clic en el siguiente botón para crear una nueva contraseña:</p>
            
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
    
        <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8;">
            <span>Powered by</span> <strong style="color: #0A1628;">Lynkamed</strong>
        </div>
        </div>
    </div>
</body>
</html>
