<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal de paciente</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
    <div style="text-align: center; margin-bottom: 32px;">
        @include('emails.partials.lynkamed-logo-inline', ['height' => 72])
    </div>
    <p>Hola {{ $nombrePaciente ?: 'Paciente' }},</p>
    <p>
        Ya registramos tu aceptación del aviso de privacidad y términos. Puedes entrar al <strong>portal del paciente</strong> para ver la información que tu clínica comparta contigo (citas, datos, etc.).
    </p>
    <p style="margin: 28px 0;">
        <a href="{{ $accesoUrl }}" style="display: inline-block; background: #071F4A; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600;">
            Ir al portal
        </a>
    </p>
    <p style="font-size: 13px; color: #64748b;">
        Si no solicitaste esto o no reconoces este acceso, puedes ignorar este mensaje o contactar a la clínica.
    </p>
    <p style="font-size: 12px; color: #94a3b8;">No respondas a este correo automático.</p>
    
    <!-- Branding Footer -->
    <div style="margin-top: 32px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #94a3b8;">
        <span>Powered by</span> <strong style="color: #0A1628;">Lynkamed</strong>
    </div>
</body>
</html>
