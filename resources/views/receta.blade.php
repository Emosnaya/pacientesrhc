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
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .page-header {
            display: table;
            width: 100%;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #0ea5e9;
        }
        .logo-cell {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
        }
        .logo-cell img {
            height: 44px;
            width: auto;
            max-width: 70px;
            object-fit: contain;
        }
        .clinica-cell {
            display: table-cell;
            vertical-align: middle;
            padding-left: 12px;
            padding-right: 12px;
            text-align: left;
        }
        .universidad-cell {
            display: table-cell;
            vertical-align: middle;
            padding-right: 12px;
            text-align: right;
        }
        .logo-uni-cell {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
            text-align: right;
        }
        .logo-uni-cell img {
            height: 44px;
            width: auto;
            max-width: 70px;
            object-fit: contain;
        }
        .clinica-name {
            font-size: 16px;
            font-weight: 700;
            color: #0c4a6e;
            margin: 0 0 2px 0;
            letter-spacing: 0.02em;
        }
        .clinica-meta {
            font-size: 9px;
            color: #64748b;
            margin: 0;
        }
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
            color: #0c4a6e;
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
            border-bottom: 2px solid #0ea5e9;
            background: #f0f9ff;
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
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
@php
    $config = $clinica->receta_pdf_config ?? [];
    $ordenSecciones = $config['orden_secciones'] ?? ['header', 'titulo', 'paciente', 'diagnostico', 'medicamentos', 'indicaciones', 'firma'];
@endphp
@foreach($ordenSecciones as $seccion)
    @if($seccion === 'header')
    <div class="page-header">
        <div class="logo-cell">
            @if(!empty($clinicaLogo))
                <img src="{{ $clinicaLogo }}" alt="Logo">
            @endif
        </div>
        <div class="clinica-cell">
            <p class="clinica-name">{{ $clinica->nombre ?? 'Clínica' }}</p>
            @if(!empty($sucursal))
                <p class="clinica-meta" style="font-weight: 600; color: #0c4a6e;">Sucursal: {{ $sucursal->nombre }}</p>
            @endif
            @if(!empty($clinica->telefono) || !empty($clinica->email))
                <p class="clinica-meta">
                    @if(!empty($clinica->telefono)){{ $clinica->telefono }}@endif
                    @if(!empty($clinica->telefono) && !empty($clinica->email)) · @endif
                    @if(!empty($clinica->email)){{ $clinica->email }}@endif
                </p>
            @endif
             @if(!empty($clinica->direccion))
                <p class="clinica-meta">{{ $clinica->direccion }}</p>
            @endif
        </div>
        <div class="universidad-cell">
            @if(!empty($user->cedula) || !empty($user->cedula_especialista) || !empty($user->universidad))
                <p class="clinica-name" style="margin-bottom: 4px;">{{ $user->nombre_con_titulo ?? '' }}</p>
                @if(!empty($user->cedula))
                    <p class="clinica-meta"><strong>Cédula Profesional:</strong> {{ $user->cedula }}</p>
                @endif
                @if(!empty($user->cedula_especialista))
                    <p class="clinica-meta"><strong>Cédula de Especialista:</strong> {{ $user->cedula_especialista }}</p>
                @endif
                @if(!empty($user->universidad))
                    <p class="clinica-meta">{{ $user->universidad }}</p>
                @endif
            @endif
        </div>
        <div class="logo-uni-cell">
            @if(isset($universidadLogo) && $universidadLogo)
                <img src="{{ $universidadLogo }}" alt="Logo Universidad">
            @endif
        </div>
    </div>
    @endif

    @if($seccion === 'titulo')
    <div class="titulo-doc">
        @if(!empty($data->folio))
            <p style="margin: 4px 0 0 0; font-size: 10px; color: #0c4a6e; font-weight: 600;">FOLIO: {{ str_pad($data->folio, 4, '0', STR_PAD_LEFT) }}</p>
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

</body>
</html>
