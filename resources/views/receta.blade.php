<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Receta Médica</title>
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
        /* === HEADER MODERNO === */
        .header { width: 100%; background: #0A1628; border-radius: 8px; margin-bottom: 14px; padding: 10px 14px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; border: none; }
        .doctor-cell { padding-right: 14px; }
        .doctor-logo-wrap { width: 45px; height: 45px; background: white; border-radius: 6px; padding: 5px; text-align: center; display: block; margin: 0 auto; }
        .doctor-logo-wrap img { max-height: 35px; max-width: 35px; display: block; margin: 0 auto; }
        .doctor-info { }
        .doctor-name { font-size: 14px; font-weight: 700; color: white; }
        .doctor-meta { font-size: 8.5px; color: #94a3b8; line-height: 1.7; margin-top: 2px; }
        .clinic-cell { text-align: right; }
        .clinic-info-wrap { display: inline-block; vertical-align: middle; text-align: right; }
        .clinic-name-hdr { font-size: 14px; font-weight: 700; color: white; }
        .clinic-meta-hdr { font-size: 8.5px; color: #94a3b8; line-height: 1.7; }
        .clinic-logo-wrap { width: 45px; height: 45px; background: white; border-radius: 6px; padding: 5px; text-align: center; display: block; margin: 0 auto; }
        .clinic-logo-wrap img { max-height: 35px; max-width: 35px; display: block; margin: 0 auto; }
        /* === FOOTER FIJO === */
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 5px 18px; background: white; border-top: 2px solid #0A1628; font-size: 9px; }
        .page-footer-table { width: 100%; border-collapse: collapse; }
        .page-footer-table td { border: none; padding: 0; vertical-align: middle; }
        .page-footer .clinic-name { font-weight: 700; color: #ef4444; }
        .page-footer .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 38px; }
        .titulo-doc {
            text-align: center;
            margin: 14px 0 16px;
        }
        .titulo-doc h1 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: 0.03em;
        }
        .titulo-doc .fecha {
            font-size: 11px;
            color: #475569;
            margin-top: 4px;
        }
        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 14px;
        }
        .card-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin: 0 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #cbd5e1;
        }
        .info-grid {
            display: table;
            width: 100%;
            font-size: 10px;
        }
        .info-row { display: table-row; }
        .info-label {
            display: table-cell;
            width: 32%;
            font-weight: 600;
            color: #475569;
            padding: 2px 6px 2px 0;
            vertical-align: top;
        }
        .info-value { display: table-cell; padding: 2px 0; }
        .medicamentos-section {
            margin: 16px 0;
        }
        .medicamentos-section .card-title {
            font-size: 10px;
            color: #0A1628;
        }
        table.meds {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 6px;
        }
        table.meds th {
            text-align: left;
            font-weight: 600;
            color: #334155;
            padding: 8px 6px;
            border-bottom: 2px solid #0A1628;
            background: #eef1f5;
        }
        table.meds td {
            padding: 8px 6px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        table.meds tr:nth-child(even) { background: #fafafa; }
        .med-num { width: 28px; text-align: center; font-weight: 600; color: #64748b; }
        .med-nombre { font-weight: 600; color: #1e293b; }
        .indications-text {
            margin: 12px 0 0 0;
            padding: 10px 12px;
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 0 6px 6px 0;
            font-size: 9px;
            color: #78350f;
        }
        .indications-text.empty { display: none; }
        .firma-section {
            margin-top: 28px;
            padding-top: 16px;
            text-align: center;
            page-break-inside: avoid;
        }
        .firma-box {
            display: inline-block;
            text-align: center;
            min-width: 220px;
        }
        .firma-image {
            max-width: 180px;
            max-height: 52px;
            margin-bottom: 6px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .firma-name {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 2px 0;
        }
        .firma-cedula {
            font-size: 9px;
            color: #64748b;
            margin: 0 0 6px 0;
        }
        .firma-line {
            width: 180px;
            height: 0;
            border-top: 1px solid #334155;
            margin: 0 auto 4px;
        }
        .firma-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
        }
        .sello-seguridad {
            margin-top: 30px;
            padding: 15px;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                #f1f5f9 10px,
                #f1f5f9 11px
            );
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            text-align: center;
        }
        .sello-seguridad p {
            font-size: 8px;
            color: #94a3b8;
            margin: 3px 0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .lineas-seguridad {
            margin-top: 20px;
            height: 80px;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 18px,
                #e2e8f0 18px,
                #e2e8f0 19px
            );
        }
        .pie-receta {
            margin-top: 24px;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <!-- FOOTER FIJO -->
    <div class="page-footer">
        <table class="page-footer-table">
            <tr>
                <td class="clinic-name">{{ $clinica->nombre ?? '' }}</td>
                <td class="clinic-contact">{{ $clinica->telefono ?? '' }}{{ (!empty($clinica->telefono) && !empty($clinica->email)) ? ' | ' : '' }}{{ $clinica->email ?? '' }}</td>
            </tr>
        </table>
    </div>
    <div class="content-wrapper">
@php
    $config = $clinica->receta_pdf_config ?? [];
    $ordenSecciones = $config['orden_secciones'] ?? ['header', 'titulo', 'paciente', 'diagnostico', 'medicamentos', 'indicaciones', 'firma'];
@endphp
@foreach($ordenSecciones as $seccion)
    @if($seccion === 'header')
    <div class="header">
        <table class="header-table">
            <tr>
                <!-- Logo clínica (extremo izquierdo) -->
                @if(!empty($clinicaLogo))
                <td style="width: 55px; padding-right: 12px; text-align: center;">
                    <div class="clinic-logo-wrap">
                        <img src="{{ $clinicaLogo }}" alt="Logo">
                    </div>
                </td>
                @endif
                <!-- Izquierda: Doctor -->
                <td class="doctor-cell">
                    <div class="doctor-info">
                        <div class="doctor-name">{{ $user->nombre_con_titulo ?? '' }}</div>
                        <div class="doctor-meta">
                            @if(!empty($user->cedula))
                                Cédula Profesional: {{ $user->cedula }}<br>
                            @endif
                            @if(!empty($user->cedula_especialista))
                                Cédula Especialista: {{ $user->cedula_especialista }}<br>
                            @endif
                            @if(!empty($user->universidad))
                                {{ $user->universidad }}
                            @endif
                        </div>
                    </div>
                </td>
                <!-- Derecha: Clínica -->
                <td class="clinic-cell">
                    <div class="clinic-info-wrap">
                        <div class="clinic-name-hdr">{{ $clinica->nombre ?? 'Clínica' }}</div>
                        @if(!empty($sucursal))
                            <div class="clinic-meta-hdr" style="color: #93c5fd;">Sucursal: {{ $sucursal->nombre }}</div>
                        @endif
                        @if(!empty($clinica->telefono) || !empty($clinica->email))
                            <div class="clinic-meta-hdr">{{ $clinica->telefono ?? '' }}{{ (!empty($clinica->telefono) && !empty($clinica->email)) ? ' · ' : '' }}{{ $clinica->email ?? '' }}</div>
                        @endif
                        @if(!empty($clinica->direccion))
                            <div class="clinic-meta-hdr">{{ $clinica->direccion }}</div>
                        @endif
                    </div>
                </td>
                <!-- Logo universidad (extremo derecho) -->
                @if(isset($universidadLogo) && $universidadLogo)
                <td style="width: 55px; padding-left: 12px; text-align: center;">
                    <div class="doctor-logo-wrap">
                        <img src="{{ $universidadLogo }}" alt="Universidad">
                    </div>
                </td>
                @endif
            </tr>
        </table>
    </div>
    @endif

    @if($seccion === 'titulo')
    <div class="titulo-doc">
        @if(!empty($data->folio))
            <p style="margin: 4px 0 0 0; font-size: 10px; color: #0A1628; font-weight: 600;">FOLIO: {{ str_pad($data->folio, 4, '0', STR_PAD_LEFT) }}</p>
        @endif
        <p class="fecha">{{ $data->fecha ? \Carbon\Carbon::parse($data->fecha)->format('d/m/Y') : date('d/m/Y') }}</p>
    </div>
    @endif

    @if($seccion === 'paciente')
    <div class="card">
        <p class="card-title">Datos del paciente</p>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Nombre:</span>
                <span class="info-value">{{ $paciente->nombre ?? '' }} {{ $paciente->apellidoPat ?? '' }} {{ $paciente->apellidoMat ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha de nacimiento:</span>
                <span class="info-value">{{ $paciente->fechaNacimiento ? \Carbon\Carbon::parse($paciente->fechaNacimiento)->format('d/m/Y') : '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Edad:</span>
                <span class="info-value">{{ $paciente->fechaNacimiento ? \Carbon\Carbon::parse($paciente->fechaNacimiento)->age : ($paciente->edad ?? '—') }} años</span>
            </div>
            <div class="info-row">
                <span class="info-label">Género:</span>
                <span class="info-value">{{ ($paciente->genero ?? '') == 1 || ($paciente->genero ?? '') === 'masculino' ? 'Masculino' : 'Femenino' }}</span>
            </div>
        </div>
    </div>
    @endif

    @if($seccion === 'diagnostico' && !empty($data->diagnostico_principal))
    <div class="card">
        <p class="card-title">Diagnóstico</p>
        <p style="margin:0; font-size: 10px;">{{ $data->diagnostico_principal }}</p>
    </div>
    @endif

    @if($seccion === 'medicamentos')
    <div class="medicamentos-section">
        <p class="card-title">Medicamentos prescritos</p>
        <table class="meds">
            <thead>
                <tr>
                    <th class="med-num">#</th>
                    <th>Medicamento</th>
                    <th>Presentación / Dosis</th>
                    <th>Frecuencia</th>
                    <th>Duración</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data->medicamentos ?? [] as $idx => $m)
                <tr>
                    <td class="med-num">{{ $idx + 1 }}</td>
                    <td class="med-nombre">{{ $m->medicamento }}</td>
                    <td>{{ $m->presentacion ?? '—' }} @if($m->dosis) · {{ $m->dosis }} @endif</td>
                    <td>{{ $m->frecuencia ?? '—' }}</td>
                    <td>{{ $m->duracion ?? '—' }}</td>
                </tr>
                @if(!empty($m->indicaciones_especificas))
                <tr>
                    <td class="med-num"></td>
                    <td colspan="4" style="font-size: 8px; color: #64748b; padding-top: 0;">Indicaciones: {{ $m->indicaciones_especificas }}</td>
                </tr>
                @endif
                @empty
                <tr><td colspan="5" style="text-align: center; color: #94a3b8;">Sin medicamentos registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    @if($seccion === 'indicaciones')
    @if(!empty($data->indicaciones_generales))
    <p class="indications-text">{{ $data->indicaciones_generales }}</p>
    @endif
    @endif

    @if($seccion === 'firma' && isset($firmaBase64) && $firmaBase64)
    <div class="firma-section">
        <div class="firma-box">
            <img src="{{ $firmaBase64 }}" alt="Firma" class="firma-image">
            <div class="firma-line"></div>
            <p class="firma-label">Firma del médico</p>
        </div>
    </div>
    
    <!-- Espacio de seguridad después de la firma -->
    <div class="lineas-seguridad"></div>
    
    <div class="sello-seguridad">
        <p>✓ Receta médica validada</p>
        <p>No se acepta si presenta tachaduras o enmendaduras</p>
        <p>{{ $clinica->nombre ?? 'Clínica' }} · {{ date('d/m/Y H:i') }}</p>
    </div>
    @endif
@endforeach

<div class="pie-receta">
    <p>Este documento es válido únicamente con firma y sello del médico tratante · No se aceptan fotocopias</p>
</div>
    </div><!-- end content-wrapper -->

</body>
</html>
