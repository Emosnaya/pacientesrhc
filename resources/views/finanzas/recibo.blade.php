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
        .clinic-header {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #6B21A8;
        }
        .clinic-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
            text-align: left;
        }
        .clinic-logo img {
            height: 50px;
            width: auto;
            max-width: 80px;
            object-fit: contain;
        }
        .clinic-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 14px;
        }
        .clinic-name {
            font-size: 15px;
            font-weight: 700;
            color: #4c1d95;
            margin: 0 0 4px 0;
            letter-spacing: 0.02em;
        }
        .clinic-details {
            font-size: 9px;
            color: #64748b;
            line-height: 1.4;
            margin: 0;
        }
        .clinic-details strong {
            color: #475569;
        }
        .page-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
        }
        .page-header h1 { font-size: 16px; font-weight: 700; color: #4c1d95; margin: 0 0 2px 0; }
        .recibo-num { font-size: 10px; color: #64748b; margin: 0; }
        .recibo-fecha { font-size: 8px; color: #94a3b8; margin-top: 2px; }
        .fiscal-notice {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            color: #dc2626;
            margin-top: 10px;
            padding: 6px;
            background-color: #fef2f2;
            border: 1px solid #fecaca;
        }
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
    <!-- Header con Logo y Datos de la Clínica -->
    <div class="clinic-header">
        <div class="clinic-logo">
            @if(isset($clinicaLogo) && !empty($clinicaLogo))
                <img src="{{ $clinicaLogo }}" alt="Logo">
            @endif
        </div>
        <div class="clinic-info">
            <p class="clinic-name">{{ $clinica->nombre ?? 'Clínica Médica' }}</p>
            <p class="clinic-details">
                @if(isset($sucursal) && !empty($sucursal))
                    <strong>Sucursal:</strong> {{ $sucursal->nombre }}<br>
                @endif
                @if(!empty($clinica->direccion))
                    {{ $clinica->direccion }}<br>
                @endif
                @if(!empty($clinica->telefono))
                    <strong>Tel:</strong> {{ $clinica->telefono }}
                @endif
                @if(!empty($clinica->email))
                     @if(!empty($clinica->telefono))·@endif <strong>Email:</strong> {{ $clinica->email }}
                @endif
            </p>
        </div>
    </div>

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

    <!-- Aviso Fiscal -->
    <div class="fiscal-notice">
        ⚠️ NO ES COMPROBANTE FISCAL
    </div>

    <div class="footer-doc">
        <p>Este documento es un comprobante válido de pago. {{ $clinica->nombre ?? 'Clínica' }}</p>
        <p style="margin-top: 2px;">Para cualquier aclaración, favor de comunicarse a {{ $clinica->telefono ?? 'N/A' }}</p>
    </div>
</body>
</html>
