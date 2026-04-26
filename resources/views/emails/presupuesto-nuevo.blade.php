<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuevo presupuesto disponible</title>
</head>
<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; line-height: 1.5; color: #1f2937; margin: 0; padding: 24px; background: #f8fafc;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <div style="background: #071F4A; padding: 20px 24px;">
            <h1 style="margin: 0; color: #fff; font-size: 18px;">Tienes un nuevo presupuesto</h1>
            <p style="margin: 6px 0 0; color: #cbd5e1; font-size: 13px;">{{ $clinicaNombre }}</p>
        </div>

        <div style="padding: 20px 24px;">
            <p style="margin-top: 0;">Hola {{ $nombrePaciente ?: 'Paciente' }},</p>
            <p>
                {{ $clinicaNombre }} te compartió un nuevo presupuesto para revisión.
                Puedes entrar al portal del paciente para verlo, aceptarlo y firmarlo digitalmente.
            </p>

            <div style="margin: 14px 0; padding: 12px; border-radius: 8px; background: #f8fafc; border: 1px solid #e5e7eb; font-size: 14px;">
                <p style="margin: 0 0 6px;"><strong>Presupuesto:</strong> {{ $titulo }}</p>
                <p style="margin: 0;"><strong>Total:</strong> ${{ number_format((float) $montoTotal, 2) }} MXN</p>
            </div>

            <p style="margin: 22px 0;">
                <a href="{{ $portalUrl }}" style="display: inline-block; background: #071F4A; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
                    Ir al portal para aceptar y firmar
                </a>
            </p>

            <p style="font-size: 13px; color: #6b7280; margin-bottom: 0;">
                Si no reconoces este presupuesto, ponte en contacto con {{ $clinicaNombre }}.
            </p>
        </div>

        <div style="padding: 14px 24px; background: #f8fafc; border-top: 1px solid #e5e7eb; font-size: 11px; color: #94a3b8; text-align: center;">
            Powered by <strong style="color: #0A1628;">Lynkamed</strong>
        </div>
    </div>
</body>
</html>
