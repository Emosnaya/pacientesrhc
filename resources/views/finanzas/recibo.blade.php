<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo de Pago {{ $clinica->nombre ?? 'CERCAP' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            font-size: 9px;
            line-height: 1.35;
            color: #1f2937;
            margin: 0;
            padding: 12px;
        }
        .page-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #6B21A8;
        }
        .page-header h1 { font-size: 16px; font-weight: 700; color: #4c1d95; margin: 0 0 2px 0; }
        .recibo-num { font-size: 10px; color: #64748b; margin: 0; }
        .recibo-fecha { font-size: 8px; color: #94a3b8; margin-top: 2px; }
        .two-cols { display: table; width: 100%; margin-bottom: 8px; }
        .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
        .col:last-child { padding-right: 0; padding-left: 10px; }
        .block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 8px;
        }
        .block-title {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin: 0 0 6px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #cbd5e1;
        }
        .row { display: table; width: 100%; font-size: 9px; }
        .row dt { display: table-cell; width: 38%; font-weight: 600; color: #475569; padding: 2px 4px 2px 0; }
        .row dd { display: table-cell; padding: 2px 0; margin: 0; }
        .monto-box {
            background: #ecfdf5;
            border: 2px solid #10b981;
            border-radius: 6px;
            padding: 10px 14px;
            margin: 10px 0;
            text-align: right;
        }
        .monto-label { font-size: 8px; color: #047857; margin: 0 0 4px 0; }
        .monto-valor { font-size: 20px; font-weight: 700; color: #059669; margin: 0; }
        .monto-letras { font-size: 8px; color: #047857; margin-top: 2px; font-style: italic; }
        .firma-box { padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px; background: #fafafa; margin-top: 6px; }
        .firma-box img { max-width: 160px; max-height: 48px; display: block; }
        .footer-doc { margin-top: 12px; padding-top: 8px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 7px; color: #64748b; }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>RECIBO DE PAGO</h1>
        <p class="recibo-num">No. {{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</p>
        <p class="recibo-fecha">{{ $pago->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="two-cols">
        <div class="col">
            <div class="block">
                <h3 class="block-title">Paciente</h3>
                <div class="row"><dt>Nombre</dt><dd>{{ $pago->paciente->nombre ?? '' }} {{ $pago->paciente->apellidoPat ?? '' }} {{ $pago->paciente->apellidoMat ?? '' }}</dd></div>
                <div class="row"><dt>No. Registro</dt><dd>{{ $pago->paciente->registro ?? 'N/A' }}</dd></div>
            </div>
            <div class="block">
                <h3 class="block-title">Detalles del pago</h3>
                <div class="row"><dt>Método</dt><dd>{{ ucfirst($pago->metodo_pago ?? '') }}</dd></div>
                @if($pago->referencia)<div class="row"><dt>Referencia</dt><dd>{{ $pago->referencia }}</dd></div>@endif
                @if($pago->concepto)<div class="row"><dt>Concepto</dt><dd>{{ $pago->concepto }}</dd></div>@endif
            </div>
        </div>
        <div class="col">
            <div class="monto-box">
                <p class="monto-label">Monto pagado</p>
                <p class="monto-valor">${{ number_format((float) $pago->monto, 2) }} MXN</p>
                <p class="monto-letras">({{ number_format((float) $pago->monto, 2) }} MXN)</p>
            </div>
            <div class="block">
                <h3 class="block-title">Recibió</h3>
                <p style="margin:0; font-weight: 600; font-size: 9px;">{{ $pago->usuario->nombre ?? $pago->usuario->name ?? 'N/A' }} {{ $pago->usuario->apellidoPat ?? '' }}</p>
                @if($pago->sucursal)<p style="margin: 2px 0 0 0; font-size: 8px; color: #64748b;">{{ $pago->sucursal->nombre }}</p>@endif
            </div>
        </div>
    </div>

    @if($pago->firma_paciente)
    <div class="block">
        <h3 class="block-title">Firma del paciente</h3>
        <div class="firma-box"><img src="{{ $pago->firma_paciente }}" alt="Firma"></div>
    </div>
    @endif

    @if($pago->notas)
    <div class="block">
        <h3 class="block-title">Notas</h3>
        <p style="margin:0; font-size: 8px;">{{ $pago->notas }}</p>
    </div>
    @endif

    <div class="footer-doc">
        <p>Este documento es un comprobante válido de pago. {{ $clinica->nombre ?? 'Clínica' }}</p>
    </div>
</body>
</html>
