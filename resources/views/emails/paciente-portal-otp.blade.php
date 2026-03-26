<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; line-height: 1.5; color: #333; max-width: 560px; margin: 0 auto; padding: 24px;">
    <p>Hola{{ $nombre ? ' '.$nombre : '' }},</p>
    <p>Tu código para continuar en el portal es:</p>
    <p style="font-size: 1.75rem; font-weight: bold; letter-spacing: 0.2em;">{{ $code }}</p>
    <p style="font-size: 0.875rem; color: #666;">Vence en 15 minutos. Si no lo solicitaste, ignora este mensaje.</p>
</body>
</html>
