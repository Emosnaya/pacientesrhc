<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo de pago - {{ $clinica->nombre }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #374151; margin: 0; padding: 0; background: #f3f4f6; }
        .wrap { max-width: 520px; margin: 0 auto; padding: 24px 16px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden; }
        .card-head { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 20px 24px; }
        .card-head h1 { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        .card-head p { margin: 4px 0 0 0; font-size: 13px; color: #6b7280; }
        .card-body { padding: 24px; }
        .resumen { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
        .resumen p { margin: 0 0 6px 0; font-size: 14px; color: #166534; }
        .resumen p:last-child { margin-bottom: 0; }
        .resumen .monto { font-size: 20px; font-weight: 700; color: #15803d; }
        .mensaje { font-size: 14px; color: #4b5563; margin: 0 0 12px 0; }
        .adjunto { font-size: 13px; color: #6b7280; padding: 12px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #7c3aed; }
        .footer { text-align: center; margin-top: 24px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="card-head">
                <h1>Recibo de pago</h1>
                <p>{{ $clinica->nombre}}</p>
            </div>
            <div class="card-body">
                <div class="resumen">
                    <p><strong>Paciente:</strong> {{ $nombrePaciente }}</p>
                    <p><strong>Monto:</strong> <span class="monto">${{ number_format((float) $pago->monto, 2) }} MXN</span></p>
                    <p><strong>No. recibo:</strong> {{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
                @if(!empty($mensaje))
                <p class="mensaje">{{ $mensaje }}</p>
                @endif
                <div class="adjunto">
                    <strong>Archivo adjunto:</strong> encontrará el comprobante de pago en PDF en los archivos adjuntos de este correo.
                </div>
                <p style="text-align: center; margin-top: 16px; font-size: 12px; color: #dc2626; font-weight: 600;">⚠️ NO ES COMPROBANTE FISCAL</p>
            </div>
        </div>
        <p class="footer">Este es un correo automático. No responder.</p>
    </div>
</body>
</html>
