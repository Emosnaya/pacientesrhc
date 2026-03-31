<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
    <div style="text-align: center; margin-bottom: 32px;">
        @include('emails.partials.lynkamed-logo-inline', ['height' => 72])
    </div>
    <p>Hola{{ $nombre ? ' '.$nombre : ' Paciente' }},</p>
    <p>Tu código para continuar en el portal es:</p>
    <p style="margin: 20px 0; padding: 20px; text-align: center; font-size: 28px; font-weight: 700; letter-spacing: 0.35em; color: #071F4A; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px;">
        {{ $code }}
    </p>
    <p style="font-size: 13px; color: #64748b;">
        Este código vence en <strong>15 minutos</strong>. Si no lo solicitaste, ignora este mensaje; nadie podrá acceder sin el código.
    </p>
    <p style="font-size: 12px; color: #94a3b8;">No respondas a este correo automático.</p>
    
    <!-- Branding Footer -->
    <div style="margin-top: 32px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #94a3b8;">
        <span>Powered by</span> <strong style="color: #0A1628;">Lynkamed</strong>
    </div>
</body>
</html>
