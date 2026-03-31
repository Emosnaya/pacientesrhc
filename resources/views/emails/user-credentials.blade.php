<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales de Acceso - {{ $clinica->nombre ?? 'Sistema Médico' }}</title>
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
        .credentials {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0A1628;
            border-radius: 6px;
            padding: 18px 20px;
            margin: 18px 0;
        }
        .credentials h3 {
            color: #0A1628;
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 14px 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .credential-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .credential-row:last-child { margin-bottom: 0; }
        .credential-label {
            font-weight: 700;
            min-width: 120px;
            color: #64748b;
            font-size: 13px;
        }
        .credential-value {
            flex: 1;
            font-family: 'SFMono-Regular', Consolas, monospace;
            background: #e2e8f0;
            color: #1e293b;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 13px;
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
            line-height: 1.8;
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
            .credential-row { flex-direction: column; align-items: flex-start; }
            .credential-label { margin-bottom: 4px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @php
                $lynkamedSrc = \App\Helpers\EmailHelper::embedMailImage($message ?? null, \App\Helpers\EmailHelper::getLynkamedLogoPath());
                $clinicaSrc = \App\Helpers\EmailHelper::embedMailImage($message ?? null, \App\Helpers\EmailHelper::clinicaLogoPath($clinica));
            @endphp
            @if($lynkamedSrc)
                <img src="{{ $lynkamedSrc }}" alt="Lynkamed" class="lynkamed-logo">
            @endif
            @if($clinicaSrc)
                <img src="{{ $clinicaSrc }}" alt="{{ $clinica->nombre ?? 'Clínica' }} Logo" class="clinic-logo">
            @endif
            <h1>Credenciales de Acceso</h1>
            <p>{{ $clinica->nombre ?? 'Clínica Médica' }}</p>
        </div>
        
        <div class="content">
            <h2>¡Bienvenido a {{ $clinica->nombre ?? 'nuestro sistema' }}!</h2>
            
            <p>Hola <strong>{{ $user->nombre }} {{ $user->apellidoPat }}</strong>,</p>
            
            <p>Se ha creado tu cuenta en el sistema. A continuación encontrarás tus credenciales de acceso:</p>
            
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
            <p><strong>{{ $clinica->nombre ?? 'Clínica Médica' }}</strong><br>
            @if($clinica->telefono ?? null)Tel: {{ $clinica->telefono }}<br>@endif
            @if($clinica->email ?? null)Email: {{ $clinica->email }}<br>@endif
            @if($clinica->direccion ?? null){{ $clinica->direccion }}@endif
        </p>
        <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8;">
            <span>Powered by</span> <strong style="color: #0A1628;">Lynkamed</strong>
        </div>
        </div>
    </div>
</body>
</html>
