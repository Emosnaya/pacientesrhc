<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
    <div style="text-align: center; margin-bottom: 32px;">
        @include('emails.partials.lynkamed-logo-inline', ['height' => 72, 'style' => 'margin-bottom:12px;'])
        @php
            $clinicaSrc = isset($clinica) ? \App\Helpers\EmailHelper::embedMailImage($message ?? null, \App\Helpers\EmailHelper::clinicaLogoPath($clinica)) : null;
        @endphp
        @if(!empty($clinicaSrc))
            <img src="{{ $clinicaSrc }}" alt="{{ $clinica->nombre }}" style="height: 40px; width: auto; padding: 6px; background: white; border-radius: 4px;">
        @endif
    </div>
    <p>Hola <strong>{{ $paciente->nombre }}</strong>,</p>
    <p>
        <strong>{{ $clinica->nombre }}</strong> está solicitando vincular tu expediente médico a su sistema.
    </p>
    <p>Tu código de verificación es:</p>
    <p style="margin: 20px 0; padding: 20px; text-align: center; font-size: 28px; font-weight: 700; letter-spacing: 0.35em; color: #071F4A; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px;">
        {{ $otpCode }}
    </p>
    <p style="font-size: 13px; color: #64748b; border-left: 4px solid #f59e0b; padding-left: 12px; margin: 20px 0;">
        <strong>Este código expira en 15 minutos.</strong> Si no solicitaste esta vinculación, ignora este correo. Tu información permanece segura.
    </p>
    <p>
        Al verificar este código, permites que <strong>{{ $clinica->nombre }}</strong> acceda a tu historial médico para brindarte una mejor atención.
    </p>
    <p style="font-size: 13px; color: #64748b;">
        Este mensaje se envía de forma automática en relación con tu expediente en el sistema de la clínica.
    </p>
    <p style="font-size: 12px; color: #94a3b8;">No respondas a este correo automático.</p>
    
    <!-- Branding Footer -->
    <div style="margin-top: 32px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #94a3b8;">
        <span>Powered by</span> <strong style="color: #0A1628;">Lynkamed</strong>
    </div>
</body>
</html>
