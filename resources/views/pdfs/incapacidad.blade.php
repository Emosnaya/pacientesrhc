<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificado de Incapacidad</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1e293b;
            margin: 0;
            padding: 8px 18px;
        }
        .header { width: 100%; background: {!! $clinica->color_principal ?? '#0A1628' !!}; border-radius: 8px; margin-bottom: 14px; padding: 10px 14px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; border: none; }
        .doctor-cell { padding-right: 14px; }
        .doctor-logo-wrap { width: 45px; height: 45px; background: white; border-radius: 6px; padding: 5px; text-align: center; display: block; margin: 0 auto; }
        .doctor-logo-wrap img { max-height: 35px; max-width: 35px; display: block; margin: 0 auto; }
        .doctor-name { font-size: 14px; font-weight: 700; color: white; }
        .doctor-meta { font-size: 8.5px; color: #94a3b8; line-height: 1.7; margin-top: 2px; }
        .clinic-logo-wrap { width: 45px; height: 45px; background: white; border-radius: 6px; padding: 5px; text-align: center; display: block; margin: 0 auto; }
        .clinic-logo-wrap img { max-height: 35px; max-width: 35px; display: block; margin: 0 auto; }
        .header-folio-fecha { text-align: right; padding-left: 10px; }
        .header-folio { font-size: 9px; font-weight: 700; color: #f59e0b; margin: 0; }
        .header-fecha { font-size: 8px; color: #94a3b8; margin: 2px 0 0 0; }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 8px 18px; background: white; border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!}; font-size: 8px; }
        .page-footer-table { width: 100%; border-collapse: collapse; }
        .page-footer-table td { border: none; padding: 0; vertical-align: middle; }
        .page-footer .clinic-name { font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; font-size: 9px; }
        .page-footer .clinic-contact { color: #64748b; }
        .page-footer .clinic-address { color: #94a3b8; font-size: 7px; }
        .page-footer .sucursal-name { color: #3b82f6; font-size: 8px; }
        .content-wrapper { padding-bottom: 50px; }
        .titulo-doc { text-align: center; margin: 14px 0 16px; }
        .titulo-doc h1 { font-size: 18px; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: 0.03em; }
        .titulo-doc .subtitulo { font-size: 10px; color: #64748b; margin-top: 4px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 14px; }
        .card-title { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; margin: 0 0 8px 0; padding-bottom: 4px; border-bottom: 1px solid #cbd5e1; }
        .info-grid { display: table; width: 100%; font-size: 10px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 34%; font-weight: 600; color: #475569; padding: 3px 6px 3px 0; vertical-align: top; }
        .info-value { display: table-cell; padding: 3px 0; }
        .text-block { margin: 0; font-size: 10px; white-space: pre-wrap; line-height: 1.5; }
        .badge-tipo {
            display: inline-block;
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .dias-box {
            text-align: center;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 10px;
            margin: 12px 0;
        }
        .dias-box .num { font-size: 22px; font-weight: 700; color: #b45309; }
        .dias-box .lbl { font-size: 8px; color: #92400e; text-transform: uppercase; letter-spacing: 0.06em; }
        .firma-section { margin-top: 28px; padding-top: 16px; text-align: center; page-break-inside: avoid; }
        .firma-box { display: inline-block; text-align: center; min-width: 220px; }
        .firma-image { max-width: 180px; max-height: 52px; margin-bottom: 6px; display: block; margin-left: auto; margin-right: auto; }
        .firma-name { font-size: 12px; font-weight: 700; color: #0f172a; margin: 0 0 2px 0; }
        .firma-cedula { font-size: 9px; color: #64748b; margin: 0 0 6px 0; }
        .firma-line { width: 180px; height: 0; border-top: 1px solid #334155; margin: 0 auto 4px; }
        .firma-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; }
    </style>
</head>
<body>
    <div class="page-footer">
        <table class="page-footer-table">
            <tr>
                <td class="clinic-name">{{ $clinica->nombre ?? '' }}</td>
                <td class="clinic-contact" style="text-align: right;">{{ $clinica->telefono ?? '' }}{{ (!empty($clinica->telefono) && !empty($clinica->email)) ? ' | ' : '' }}{{ $clinica->email ?? '' }}</td>
            </tr>
            @if(!empty($sucursal))
            <tr>
                <td class="sucursal-name">Sucursal: {{ $sucursal->nombre }}</td>
                <td class="clinic-address" style="text-align: right;">{{ $sucursal->direccion ?? ($clinica->direccion ?? '') }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="2" style="text-align: center; padding-top: 4px; font-size: 7px; color: #94a3b8;">
                    <span>Generado con</span> <strong style="color: {!! $clinica->color_principal ?? '#0A1628' !!};">Lynkamed</strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="content-wrapper">
        <div class="header">
            <table class="header-table">
                <tr>
                    @if(!empty($clinicaLogo))
                    <td style="width: 55px; padding-right: 12px; text-align: center;">
                        <div class="clinic-logo-wrap">
                            <img src="{{ $clinicaLogo }}" alt="Logo">
                        </div>
                    </td>
                    @endif
                    <td class="doctor-cell">
                        <div class="doctor-name">{{ $user->nombre_con_titulo ?? ($user->nombre ?? '') }}</div>
                        <div class="doctor-meta">
                            @if(!empty($user->cedula))
                                C�dula Profesional: {{ $user->cedula }}<br>
                            @endif
                            @if(!empty($user->cedula_especialista))
                                C�dula Especialista: {{ $user->cedula_especialista }}<br>
                            @endif
                            @if(!empty($user->universidad))
                                {{ $user->universidad }}
                            @endif
                        </div>
                    </td>
                    <td class="header-folio-fecha">
                        @if(!empty($data->folio))
                        <p class="header-folio">FOLIO: {{ str_pad($data->folio, 4, '0', STR_PAD_LEFT) }}</p>
                        @endif
                        <p class="header-fecha">{{ $data->fecha_inicio ? $data->fecha_inicio->format('d/m/Y') : date('d/m/Y') }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="titulo-doc">
            <h1>Certificado de Incapacidad</h1>
            <p class="subtitulo">A quien corresponda</p>
        </div>

        <div class="card">
            <p class="card-title">Datos del paciente</p>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ $paciente->nombre ?? '' }} {{ $paciente->apellidoPat ?? '' }} {{ $paciente->apellidoMat ?? '' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de nacimiento:</span>
                    <span class="info-value">{{ $paciente->fechaNacimiento ? \Carbon\Carbon::parse($paciente->fechaNacimiento)->format('d/m/Y') : '�' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Edad:</span>
                    <span class="info-value">{{ $paciente->fechaNacimiento ? \Carbon\Carbon::parse($paciente->fechaNacimiento)->age : ($paciente->edad ?? '�') }} a�os</span>
                </div>
            </div>
        </div>

        <div class="card">
            <p class="card-title">Tipo de incapacidad</p>
            <span class="badge-tipo">{{ $data->tipo_incapacidad_label }}</span>
        </div>

        <div class="card">
            <p class="card-title">Per�odo de incapacidad</p>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Fecha de inicio:</span>
                    <span class="info-value">{{ $data->fecha_inicio ? $data->fecha_inicio->format('d/m/Y') : '�' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de t�rmino:</span>
                    <span class="info-value">{{ $data->fecha_termino ? $data->fecha_termino->format('d/m/Y') : '�' }}</span>
                </div>
            </div>
            <div class="dias-box">
                <div class="num">{{ $data->dias }}</div>
                <div class="lbl">d�as de incapacidad</div>
            </div>
        </div>

        <div class="card">
            <p class="card-title">Diagn�stico</p>
            <p class="text-block">{{ $data->diagnostico }}</p>
        </div>

        @if(!empty($data->comentarios))
        <div class="card">
            <p class="card-title">Comentarios / Observaciones</p>
            <p class="text-block">{{ $data->comentarios }}</p>
        </div>
        @endif

        <div class="firma-section">
            <div class="firma-box">
                @if(!empty($firmaBase64))
                    <img src="{{ $firmaBase64 }}" alt="Firma" class="firma-image">
                @else
                    <div style="height: 40px;"></div>
                @endif
                <div class="firma-line"></div>
                <p class="firma-name">{{ $user->nombre_con_titulo ?? ($user->nombre ?? '') }}</p>
                @if(!empty($user->cedula))
                    <p class="firma-cedula">C�dula Prof. {{ $user->cedula }}</p>
                @endif
                <p class="firma-label">M�dico tratante</p>
            </div>
        </div>
    </div>
</body>
</html>
