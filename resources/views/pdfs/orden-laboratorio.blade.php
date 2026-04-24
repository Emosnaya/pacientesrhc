<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Laboratorio</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}');
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; margin: 20px 25px; }
        table { border-collapse: collapse; }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 6px 20px; background: white; border-top: 2px solid #0A1628; font-size: 9px; }
        .page-footer-table { width: 100%; }
        .clinic-name { font-weight: 700; color: #0A1628; }
        .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 48px; }
        .header { width: 100%; background: #0A1628; border-radius: 8px; margin-bottom: 10px; padding: 8px 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-cell { width: 60px; padding-right: 12px !important; }
        .header-logo { width: 45px; height: 45px; background: white; border-radius: 6px; padding: 5px; text-align: center; }
        .header-logo img { max-height: 35px; max-width: 35px; }
        .header-title { font-size: 15px; font-weight: 700; color: white; }
        .header-subtitle { font-size: 9px; color: #94a3b8; }
        .header-meta-cell { text-align: right; width: 130px; }
        .header-badge { background: rgba(255,255,255,0.15); padding: 5px 10px; border-radius: 5px; display: inline-block; margin-bottom: 4px; }
        .header-badge-label { font-size: 8px; text-transform: uppercase; color: #94a3b8; }
        .header-badge-value { font-size: 16px; font-weight: 700; color: #60a5fa; }
        .header-date { font-size: 9px; color: #94a3b8; }
        .patient-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; }
        .patient-table { width: 100%; border-collapse: collapse; }
        .patient-table td { padding: 2px 6px; font-size: 10px; }
        .patient-name { font-size: 13px; font-weight: 700; color: #0A1628; margin-bottom: 6px; }
        .patient-label { color: #64748b; font-size: 9px; display: block; }
        .patient-value { font-weight: 600; color: #334155; }
        .section { margin-bottom: 8px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .section-title { background: #0A1628; color: white; font-size: 9px; font-weight: 700; padding: 4px 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-body { padding: 8px 10px; }
        .row-table { width: 100%; border-collapse: collapse; }
        .row-table td { padding: 2px 6px; vertical-align: top; font-size: 10px; }
        .lbl { color: #64748b; font-size: 9px; display: block; }
        .val { font-weight: 600; color: #0f172a; }
        .text-block { font-size: 10px; color: #334155; line-height: 1.6; }
        .status-badge { display: inline-block; border-radius: 3px; padding: 2px 8px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-pendiente  { background: #f1f5f9; color: #475569; }
        .status-recibida   { background: #dbeafe; color: #1e40af; }
        .status-en_proceso { background: #fef9c3; color: #92400e; }
        .status-lista      { background: #ede9fe; color: #5b21b6; }
        .status-entregada  { background: #dcfce7; color: #14532d; }
        .status-cancelada  { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<div class="page-footer">
    <table class="page-footer-table">
        <tr>
            <td class="clinic-name">{{ $clinica->nombre ?? 'Clínica' }}</td>
            <td class="clinic-contact">
                {{ $clinica->telefono ?? '' }}
                @if($clinica->email ?? null) &nbsp;|&nbsp; {{ $clinica->email }} @endif
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;padding-top:3px;font-size:7px;color:#94a3b8;">
                Generado con <strong style="color:#0A1628;">LynkaMed</strong>
                &nbsp;·&nbsp; {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>
</div>

<div class="content-wrapper">

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo-cell">
                    <div class="header-logo">
                        @if(isset($clinicaLogo) && $clinicaLogo)
                            <img src="{{ $clinicaLogo }}" alt="Logo">
                        @endif
                    </div>
                </td>
                <td style="padding-left:10px;">
                    <div class="header-title">Orden de Laboratorio</div>
                    <div class="header-subtitle">{{ $clinica->nombre ?? '' }}</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Folio</div>
                        <div class="header-badge-value">#{{ str_pad($orden->folio, 4, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="header-date">{{ \Carbon\Carbon::parse($orden->created_at)->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="patient-card">
        <div class="patient-name">
            {{ $orden->paciente->apellidoPat ?? '' }}
            {{ $orden->paciente->apellidoMat ?? '' }}
            {{ $orden->paciente->nombre ?? '' }}
        </div>
        <table class="patient-table">
            <tr>
                <td>
                    <span class="patient-label">Fecha de nacimiento</span>
                    <span class="patient-value">
                        {{ $orden->paciente->fechaNacimiento
                            ? $orden->paciente->fechaNacimiento->format('d/m/Y')
                            : '—' }}
                    </span>
                </td>
                <td>
                    <span class="patient-label">Solicitado por</span>
                    <span class="patient-value">{{ $user->nombre_con_titulo ?? ($user->nombre ?? '') . ' ' . ($user->apellidoPat ?? '') }}</span>
                </td>
            </tr>
        </table>
    </div>

    @if($orden->laboratorio)
    <div class="section">
        <div class="section-title">Laboratorio</div>
        <div class="section-body">
            <table class="row-table"><tr>
                <td width="40%">
                    <span class="lbl">Nombre</span>
                    <span class="val">{{ $orden->laboratorio->nombre }}</span>
                </td>
                @if($orden->laboratorio->email)
                <td width="35%">
                    <span class="lbl">Email</span>
                    <span class="val">{{ $orden->laboratorio->email }}</span>
                </td>
                @endif
                @if($orden->laboratorio->telefono)
                <td width="25%">
                    <span class="lbl">Teléfono</span>
                    <span class="val">{{ $orden->laboratorio->telefono }}</span>
                </td>
                @endif
            </tr></table>
        </div>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Estudios Solicitados</div>
        <div class="section-body">
            <div class="text-block">{{ $orden->estudios }}</div>
        </div>
    </div>

    @if($orden->diagnostico_clinico)
    <div class="section">
        <div class="section-title">Diagnóstico Clínico</div>
        <div class="section-body">
            <div class="text-block">{{ $orden->diagnostico_clinico }}</div>
        </div>
    </div>
    @endif

    @if($orden->indicaciones)
    <div class="section">
        <div class="section-title">Indicaciones al Laboratorio</div>
        <div class="section-body">
            <div class="text-block">{{ $orden->indicaciones }}</div>
        </div>
    </div>
    @endif

    {{-- Fechas solo para clínicas dentales --}}
    @if($isDental && ($orden->fecha_recoleccion || $orden->fecha_entrega_estimada || $orden->fecha_entrega_real))
    <div class="section">
        <div class="section-title">Fechas de seguimiento</div>
        <div class="section-body">
            <table class="row-table"><tr>
                <td width="33%">
                    <span class="lbl">Recolección</span>
                    <span class="val">{{ $orden->fecha_recoleccion ? \Carbon\Carbon::parse($orden->fecha_recoleccion)->format('d/m/Y') : '—' }}</span>
                </td>
                <td width="33%">
                    <span class="lbl">Entrega estimada</span>
                    <span class="val">{{ $orden->fecha_entrega_estimada ? \Carbon\Carbon::parse($orden->fecha_entrega_estimada)->format('d/m/Y') : '—' }}</span>
                </td>
                <td width="33%">
                    <span class="lbl">Entrega real</span>
                    <span class="val">{{ $orden->fecha_entrega_real ? \Carbon\Carbon::parse($orden->fecha_entrega_real)->format('d/m/Y') : '—' }}</span>
                </td>
            </tr></table>
        </div>
    </div>
    @endif

    @if($orden->notas_laboratorio)
    <div class="section">
        <div class="section-title">Notas del Laboratorio</div>
        <div class="section-body">
            <div class="text-block">{{ $orden->notas_laboratorio }}</div>
        </div>
    </div>
    @endif

    <table style="width:100%;margin-top:30px;">
        <tr>
            <td style="width:25%;"></td>
            <td style="width:50%;text-align:center;">
                @if(isset($firmaBase64) && $firmaBase64)
                    <img src="{{ $firmaBase64 }}" alt="Firma" style="height:55px;width:auto;"><br>
                @else
                    <div style="height:55px;"></div>
                @endif
                <div style="border-top:1px solid #334155;width:200px;margin:4px auto 0 auto;padding-top:6px;">
                    <div style="font-size:10px;font-weight:700;color:#0A1628;">
                        {{ $user->nombre_con_titulo ?? ($user->nombre ?? '') . ' ' . ($user->apellidoPat ?? '') }}
                    </div>
                    @if(isset($user->cedula_especialista) && $user->cedula_especialista)
                        <div style="font-size:9px;color:#64748b;">Cédula prof.: {{ $user->cedula_especialista }}</div>
                    @endif
                    <div style="font-size:9px;color:#64748b;margin-top:2px;">Médico solicitante</div>
                </div>
            </td>
            <td style="width:25%;"></td>
        </tr>
    </table>

</div>
</body>
</html>
