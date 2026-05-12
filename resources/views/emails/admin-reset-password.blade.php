<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de contraseña — Lynkamed Admin</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #374151; background: #f3f4f6; margin: 0; padding: 0; }
        .wrap { max-width: 500px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); overflow: hidden; }
        .card-head { background: linear-gradient(135deg, #0A1628 0%, #1e3a5f 100%); padding: 28px; text-align: center; }
        .card-head h1 { margin: 12px 0 4px; font-size: 20px; font-weight: 700; color: #fff; }
        .card-head p { margin: 0; font-size: 13px; color: #94a3b8; }
        .card-body { padding: 28px; }
        .greeting { font-size: 15px; color: #111827; margin: 0 0 16px; }
        .info { font-size: 14px; color: #4b5563; margin: 0 0 24px; }
        .btn-wrap { text-align: center; margin: 24px 0; }
        .btn { display: inline-block; background: linear-gradient(135deg, #0A1628 0%, #1e3a5f 100%); color: #fff !important; text-decoration: none; padding: 14px 36px; border-radius: 8px; font-size: 15px; font-weight: 600; }
        .warn { background: #fef9c3; border: 1px solid #fde047; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #854d0e; margin: 20px 0; }
        .url-fallback { word-break: break-all; font-size: 12px; color: #6b7280; background: #f8fafc; padding: 10px 12px; border-radius: 6px; border: 1px solid #e2e8f0; margin-top: 16px; }
        .footer { text-align: center; margin-top: 24px; font-size: 12px; color: #9ca3af; }
        .powered { margin-top: 8px; font-size: 11px; color: #94a3b8; }
        .powered strong { color: #0A1628; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="card-head">
            @include('emails.partials.lynkamed-logo-inline', ['height' => 44, 'maxWidth' => 180])
            <h1>🔐 Recuperar contraseña</h1>
            <p>Panel de Administración Lynkamed</p>
        </div>
        <div class="card-body">
            <p class="greeting">Hola <strong>{{ $admin->name }}</strong>,</p>
            <p class="info">
                Recibimos una solicitud para restablecer la contraseña de tu cuenta en el
                panel de administración de Lynkamed. Haz clic en el botón para crear una nueva contraseña:
            </p>

            <div class="btn-wrap">
                <a href="{{ $resetUrl }}" class="btn">Restablecer contraseña →</a>
            </div>

            <div class="warn">
                ⏱ Este enlace es válido por <strong>60 minutos</strong>.
                Si no solicitaste este cambio, ignora este correo — tu contraseña no será modificada.
            </div>

            <p style="font-size:13px;color:#6b7280;">
                Si el botón no funciona, copia y pega este enlace en tu navegador:
            </p>
            <div class="url-fallback">{{ $resetUrl }}</div>
        </div>
    </div>

    <div class="footer">
        <p>Este correo fue enviado a <strong>{{ $admin->email }}</strong></p>
        <p>&copy; {{ date('Y') }} Lynkamed. Todos los derechos reservados.</p>
        <p class="powered">Powered by <strong>Lynkamed</strong></p>
    </div>
</div>
</body>
</html>
