<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
    <p>Hola {{ $nombrePaciente ?: 'Paciente' }},</p>
    <p>
        <strong>{{ $clinicaNombre }}</strong> ha registrado tus datos en su sistema.
        Para continuar conforme a la <strong>Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP)</strong>,
        necesitamos que confirmes que has leído y aceptas el <strong>aviso de privacidad</strong> y los <strong>términos y condiciones</strong> aplicables.
    </p>
    @if(!empty($urlAviso))
        <p><a href="{{ $urlAviso }}">Ver aviso de privacidad</a></p>
    @endif
    @if(!empty($urlTerminos))
        <p><a href="{{ $urlTerminos }}">Ver términos y condiciones</a></p>
    @endif
    <p style="margin: 28px 0;">
        <a href="{{ $urlAceptacion }}" style="display: inline-block; background: #071F4A; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600;">
            Aceptar aviso y términos
        </a>
    </p>
    <p style="font-size: 13px; color: #64748b;">
        Este enlace es personal y caduca en {{ $diasValidez }} días. Si no solicitaste este registro, puedes ignorar este mensaje o contactar a la clínica.
    </p>
    <p style="font-size: 12px; color: #94a3b8;">No respondas a este correo automático.</p>
</body>
</html>
