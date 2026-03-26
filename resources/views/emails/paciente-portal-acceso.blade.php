<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de paciente</title>
</head>
<body style="font-family: sans-serif; line-height: 1.5; color: #333; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="font-size: 1.25rem;">Hola{{ $nombrePaciente ? ', '.$nombrePaciente : '' }}</h1>
    <p>Ya registramos tu aceptación del aviso de privacidad y términos. Puedes entrar al <strong>portal del paciente</strong> para ver la información que tu clínica comparta contigo (citas, datos, etc.).</p>
    <p style="margin: 28px 0;">
        <a href="{{ $accesoUrl }}" style="display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px;">Ir al portal</a>
    </p>
    <p style="font-size: 0.875rem; color: #666;">Si no solicitaste esto, puedes ignorar este mensaje.</p>
</body>
</html>
