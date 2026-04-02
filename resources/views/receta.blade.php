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
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 8px 18px; background: white; border-top: 2px solid #0A1628; font-size: 8px; }
        .page-footer-table { width: 100%; border-collapse: collapse; }
        .page-footer-table td { border: none; padding: 0; vertical-align: middle; }
        .page-footer .clinic-name { font-weight: 700; color: #0A1628; font-size: 9px; }
        .page-footer .clinic-contact { color: #64748b; }
        .page-footer .clinic-address { color: #94a3b8; font-size: 7px; }
        .page-footer .sucursal-name { color: #3b82f6; font-size: 8px; }
        .content-wrapper { padding-bottom: 50px; }
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
        
        /* Sección de Validación COFEPRIS */
        .validacion-cofepris {
            margin-top: 15px;
            padding: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            page-break-inside: avoid;
        }
        .validacion-header {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
        }
        .validacion-badge {
            background: #16a34a;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 700;
        }
        .validacion-titulo {
            font-size: 9px;
            color: #166534;
            font-weight: 600;
            margin-left: 8px;
        }
        .validacion-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
        }
        .validacion-table td {
            padding: 2px 0;
            vertical-align: top;
            border: none;
        }
        .validacion-table .lbl {
            color: #64748b;
            font-weight: 600;
            width: 22%;
            padding-right: 8px;
        }
        .validacion-table .val {
            color: #1e293b;
        }
        .validacion-table .mono {
            font-family: monospace;
            font-size: 6px;
        }
        .cadena-box {
            background: #f1f5f9;
            padding: 4px 6px;
            border-radius: 3px;
            margin-top: 4px;
            word-break: break-all;
            font-family: monospace;
            font-size: 5px;
            color: #475569;
            max-height: 36px;
            overflow: hidden;
        }
        .validacion-footer {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dashed #e2e8f0;
            font-size: 6px;
            color: #94a3b8;
            text-align: center;
        }
        /* E.firma badge compacto junto a firma */
        .efirma-inline {
            text-align: center;
            margin-top: 6px;
        }
        .efirma-inline-badge {
            display: inline-block;
            background: #16a34a;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: 700;
        }
        /* Folio y fecha en header */
        .header-folio-fecha {
            text-align: right;
            padding-left: 10px;
        }
        .header-folio {
            font-size: 9px;
            font-weight: 700;
            color: #f59e0b;
            margin: 0;
        }
        .header-fecha {
            font-size: 8px;
            color: #94a3b8;
            margin: 2px 0 0 0;
        }
        /* E.firma compacta inline */
        .efirma-compact {
            margin-top: 16px;
            padding: 8px 12px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #86efac;
            border-radius: 6px;
            display: inline-block;
        }
        .efirma-compact-header {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }
        .efirma-compact-badge {
            background: #16a34a;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
        }
        .efirma-compact-title {
            font-size: 8px;
            color: #166534;
            font-weight: 600;
        }
        .efirma-compact-data {
            font-size: 7px;
            color: #374151;
            line-height: 1.5;
        }
        .efirma-compact-data strong {
            color: #1f2937;
        }
        .efirma-sello {
            margin-top: 4px;
            padding: 3px 6px;
            background: #f1f5f9;
            border-radius: 3px;
            font-family: monospace;
            font-size: 5px;
            color: #64748b;
            word-break: break-all;
            max-height: 24px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- FOOTER FIJO -->
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
            @php
                $direccionMostrar = !empty($sucursal) ? ($sucursal->direccion ?? $clinica->direccion ?? '') : ($clinica->direccion ?? '');
            @endphp
            @if(!empty($direccionMostrar) && empty($sucursal))
            <tr>
                <td colspan="2" class="clinic-address">{{ $direccionMostrar }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="2" style="text-align: center; padding-top: 4px; font-size: 7px; color: #94a3b8;">
                    <span>Generado con</span> <strong style="color: #0A1628;">Lynkamed</strong>
                </td>
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
                <!-- Centro: Doctor -->
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
                <!-- Folio y Fecha -->
                <td class="header-folio-fecha">
                    @if(!empty($data->folio))
                    <p class="header-folio">FOLIO: {{ str_pad($data->folio, 4, '0', STR_PAD_LEFT) }}</p>
                    @endif
                    <p class="header-fecha">{{ $data->fecha ? \Carbon\Carbon::parse($data->fecha)->format('d/m/Y') : date('d/m/Y') }}</p>
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
    {{-- Folio y fecha ya están en el header --}}
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

    @if($seccion === 'firma')
    {{-- Siempre mostrar quién elaboró la receta --}}
    @if(isset($autor) && $autor)
    <div style="text-align: center; margin-bottom: 10px;">
        <p style="font-size: 10px; color: #475569; margin: 0;">
            <strong>Elaboró:</strong> {{ $autor->nombre_completo }}
            @if($autor->cedula)
            <br><span style="font-size: 9px; color: #64748b;">Cédula Profesional: {{ $autor->cedula }}</span>
            @endif
        </p>
    </div>
    @endif
    
    {{-- Solo mostrar firma si el usuario actual es el autor --}}
    @if(isset($esAutor) && $esAutor && isset($firmaBase64) && $firmaBase64)
    <div class="firma-section">
        <div class="firma-box">
            <img src="{{ $firmaBase64 }}" alt="Firma" class="firma-image">
            <div class="firma-line"></div>
            <p class="firma-label">Firma del médico</p>
            @if(isset($efirmaData) && $efirmaData)
            <div class="efirma-inline">
                <span class="efirma-inline-badge">✓ FIRMADA ELECTRÓNICAMENTE</span>
            </div>
            @endif
        </div>
    </div>
    
    @if(isset($efirmaData) && $efirmaData)
    <!-- Validación e.firma compacta -->
    <div style="text-align: center; margin-top: 12px;">
        <div class="efirma-compact">
            <div class="efirma-compact-header">
                <span class="efirma-compact-badge">✓ e.firma</span>
                <span class="efirma-compact-title">Receta electrónica válida</span>
            </div>
            <div class="efirma-compact-data">
                <strong>{{ $efirmaData['nombre_titular'] ?? '' }}</strong> · RFC: {{ $efirmaData['rfc'] ?? 'N/A' }}<br>
                Certificado: {{ $efirmaData['numero_serie'] ?? 'N/A' }} · {{ \Carbon\Carbon::parse($efirmaData['firmada_at'])->format('d/m/Y H:i') }}
            </div>
            <div class="efirma-sello">{{ Str::limit($efirmaData['sello_digital'] ?? '', 120) }}</div>
        </div>
    </div>
    <p style="text-align: center; font-size: 6px; color: #94a3b8; margin-top: 6px;">Firmado con e.firma del SAT · NOM-024-SSA3-2012 · Vigencia: 30 días</p>
    @else
    <!-- Espacio de seguridad después de la firma (sin e.firma) -->
    <div class="lineas-seguridad"></div>
    
    <div class="sello-seguridad">
        <p>✓ Receta médica validada</p>
        <p>No se acepta si presenta tachaduras o enmendaduras</p>
    </div>
    @endif
    @endif
    @endif
@endforeach

<div class="pie-receta"><!-- end content-wrapper -->
</body>
</html>
