<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; background: #f8fafc; margin: 0; padding: 0; }
    .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,.08); }
    .header { background: #0A1628; color: #fff; padding: 28px 32px; }
    .header h1 { font-size: 22px; margin: 0 0 4px; }
    .header p { font-size: 13px; color: #94a3b8; margin: 0; }
    .folio-badge { display: inline-block; background: #3b82f6; color: #fff; border-radius: 6px; font-size: 16px; font-weight: 700; padding: 4px 14px; margin-top: 10px; }
    .body { padding: 28px 32px; }
    .info-row { display: flex; gap: 0; margin-bottom: 16px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
    .info-cell { flex: 1; padding: 12px 16px; border-right: 1px solid #e2e8f0; }
    .info-cell:last-child { border-right: none; }
    .info-label { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; }
    .info-value { font-size: 14px; font-weight: 700; color: #0A1628; margin-top: 2px; }
    .section { margin-bottom: 20px; }
    .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; margin-bottom: 8px; }
    .section-body { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; font-size: 14px; color: #334155; line-height: 1.6; white-space: pre-wrap; }
    .portal-box { background: #eff6ff; border: 2px solid #3b82f6; border-radius: 8px; padding: 18px 20px; margin: 20px 0; text-align: center; }
    .portal-box p { font-size: 13px; color: #1e40af; margin: 0 0 10px; }
    .portal-btn { display: inline-block; background: #3b82f6; color: #fff; border-radius: 6px; padding: 10px 24px; font-size: 14px; font-weight: 700; text-decoration: none; }
    .footer { background: #f1f5f9; padding: 16px 32px; font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Nueva Orden de Laboratorio</h1>
        <p>{{ $orden->clinica->nombre ?? 'Clínica' }}</p>
        <div class="folio-badge">Folio #{{ str_pad($orden->folio, 4, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="body">
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Paciente</div>
                <div class="info-value">{{ $orden->paciente->nombre ?? '' }} {{ $orden->paciente->apellidoPat ?? '' }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Solicitado por</div>
                <div class="info-value">{{ $orden->user->nombre ?? '' }} {{ $orden->user->apellidoPat ?? '' }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Fecha</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($orden->created_at)->format('d/m/Y') }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Estudios Solicitados</div>
            <div class="section-body">{{ $orden->estudios }}</div>
        </div>

        @if($orden->diagnostico_clinico)
        <div class="section">
            <div class="section-title">Diagnóstico Clínico</div>
            <div class="section-body">{{ $orden->diagnostico_clinico }}</div>
        </div>
        @endif

        @if($orden->indicaciones)
        <div class="section">
            <div class="section-title">Indicaciones</div>
            <div class="section-body">{{ $orden->indicaciones }}</div>
        </div>
        @endif

        @if(isset($portalUrl))
        <div class="portal-box">
            <p>Use el siguiente enlace para actualizar el estado de la orden y las fechas de entrega:</p>
            <a class="portal-btn" href="{{ $portalUrl }}">Acceder al Portal →</a>
            <p style="margin-top: 10px; font-size: 11px; color: #3b82f6;">{{ $portalUrl }}</p>
        </div>
        @endif

        <p style="font-size: 13px; color: #64748b;">Se adjunta la orden completa en PDF a este correo.</p>
    </div>

    <div class="footer">
        {{ $orden->clinica->nombre ?? '' }} — Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</div>
</body>
</html>
