<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripción Renovada</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        .wrap { max-width: 560px; margin: 0 auto; padding: 24px 16px; }
        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        /* Header con gradiente Lynkamed */
        .card-head {
            background: linear-gradient(135deg, #0A1628 0%, #1e3a5f 100%);
            padding: 32px 28px 24px;
            text-align: center;
        }
        .card-head h1 {
            margin: 16px 0 4px;
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
        }
        .card-head p {
            margin: 0;
            font-size: 14px;
            color: #94a3b8;
        }
        /* Cuerpo */
        .card-body { padding: 28px; }
        .greeting { font-size: 16px; color: #111827; margin: 0 0 16px; }
        /* Badge de éxito */
        .badge-success {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            font-size: 13px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 20px;
        }
        /* Caja de detalles */
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h3 {
            margin: 0 0 14px;
            font-size: 14px;
            font-weight: 700;
            color: #0A1628;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .label { color: #6b7280; }
        .info-row .value { font-weight: 600; color: #111827; }
        .info-row .value.monto { font-size: 18px; color: #15803d; }
        .info-row .value.fecha { color: #0A1628; }
        /* Alerta de próxima renovación */
        .alert {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            border-radius: 0 8px 8px 0;
            padding: 14px 16px;
            font-size: 13px;
            color: #1e40af;
            margin: 20px 0;
        }
        /* Botón CTA */
        .btn-wrap { text-align: center; margin: 24px 0 8px; }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #0A1628 0%, #1e3a5f 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 13px 32px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        /* Footer */
        .footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #9ca3af;
        }
        .powered {
            margin-top: 8px;
            font-size: 11px;
            color: #94a3b8;
        }
        .powered strong { color: #0A1628; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">

        {{-- Header --}}
        <div class="card-head">
            @include('emails.partials.lynkamed-logo-inline', ['height' => 52, 'maxWidth' => 200])
            <h1>✅ ¡Suscripción renovada!</h1>
            <p>{{ $clinica->nombre }}</p>
        </div>

        {{-- Cuerpo --}}
        <div class="card-body">

            <p class="greeting">
                Hola <strong>{{ $user->nombre }} {{ $user->apellidoPat }}</strong>,
            </p>

            <span class="badge-success">Pago confirmado por Stripe</span>

            <p style="font-size:14px;color:#4b5563;margin:0 0 20px;">
                Tu suscripción para <strong>{{ $clinica->nombre }}</strong> ha sido renovada exitosamente.
                A continuación encontrarás los detalles de tu pago.
            </p>

            {{-- Detalles del pago --}}
            <div class="info-box">
                <h3>📋 Detalles de la renovación</h3>

                <div class="info-row">
                    <span class="label">Clínica / Consultorio</span>
                    <span class="value">{{ $clinica->nombre }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Plan</span>
                    <span class="value">{{ ucfirst($clinica->plan ?? 'Profesional') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Ciclo facturado</span>
                    <span class="value">{{ ucfirst($billingCycle) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Monto cobrado</span>
                    <span class="value monto">${{ number_format($monto, 2) }} MXN</span>
                </div>
                <div class="info-row">
                    <span class="label">Nueva fecha de vencimiento</span>
                    <span class="value fecha">
                        {{ \Carbon\Carbon::parse($nuevaFechaVencimiento)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                    </span>
                </div>
                @if($stripeSessionId)
                <div class="info-row">
                    <span class="label">Referencia de pago</span>
                    <span class="value" style="font-size:11px;color:#6b7280;word-break:break-all;">{{ $stripeSessionId }}</span>
                </div>
                @endif
            </div>

            {{-- Alerta próxima renovación --}}
            <div class="alert">
                📅 Tu próxima renovación será el
                <strong>{{ \Carbon\Carbon::parse($nuevaFechaVencimiento)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</strong>.
                Te enviaremos un recordatorio con anticipación.
            </div>

            {{-- CTA --}}
            <div class="btn-wrap">
                <a href="{{ config('app.frontend_url', env('FRONTEND_URL')) }}" class="btn">
                    Ir a mi sistema →
                </a>
            </div>

            <p style="font-size:13px;color:#6b7280;text-align:center;margin-top:16px;">
                Si tienes alguna pregunta sobre tu suscripción, contáctanos en
                <a href="mailto:{{ config('mail.from.address', 'contacto@lynkamed.mx') }}" style="color:#0A1628;">
                    {{ config('mail.from.address', 'contacto@lynkamed.mx') }}
                </a>
            </p>

        </div>
    </div>

    <div class="footer">
        <p>Este correo fue enviado a <strong>{{ $user->email }}</strong></p>
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Lynkamed') }}. Todos los derechos reservados.</p>
        <p class="powered">Powered by <strong>Lynkamed</strong></p>
    </div>
</div>
</body>
</html>
