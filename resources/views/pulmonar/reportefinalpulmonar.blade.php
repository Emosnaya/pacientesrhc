<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Reporte Final Rehabilitación Pulmonar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #1e293b;
            background: #ffffff;
            padding: 10px 20px;
        }
        /* Estilo para el logo */
        .logo-container {
            height: 36px;
            overflow: hidden;
            display: inline-block;
        }
        .logo-container img {
            height: 36px;
            width: auto;
        }
        .paciente {
            font-size: 10px;
        }
        .f-bold {
            font-weight: bold;
        }
        .f-normal {
            font-weight: normal;
        }
        .f-9 {
            font-size: 8.5px;
        }
        .f-10 {
            font-size: 8.5px;
        }
        .f-15 {
            font-size: 13px;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .text-justify {
            text-align: justify;
        }
        .medio {
            position: relative;
        }
        .texto-izquierda {
            text-align: left;
            position: absolute;
            left: 0;
        }
        .texto-derecha {
            text-align: right;
            position: absolute;
            right: 0;
        }
        .contenedor {
            position: relative;
            text-align: justify;
            margin-bottom: 0.3rem;
            margin-top: 0.8rem;
        }
        .titulo {
            display: inline-block;
            position: relative;
            z-index: 1;
            padding-right: 0.5rem;
            font-size: 12px;
            font-weight: bold;
        }
        .linea {
            position: absolute;
            left: 0;
            right: 0;
            top: 0.5rem;
            border-bottom: 2px solid #0A1628;
            z-index: 0;
        }
        .m-t-0 {
            margin-top: -0.7rem;
        }
        .bck-blue {
            background-color: #1d4ed8;
            color: white;
        }
        .bck-red {
            background-color: #0d9488;
            color: white;
        }
        .bck-gray {
            background-color: #0A1628;
            color: white;
        }
        .tabla {
            font-size: 9px;
            margin-bottom: 1rem;
            width: 100%;
            border-collapse: collapse;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .tabla td, .tabla th {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
        }
        .tabla th {
            font-weight: 700;
            text-align: center;
            font-size: 9.5px;
            letter-spacing: 0.3px;
        }
        .tabla td { color: #334155; }
        .tabla td.text-center { font-weight: 600; }
        .tabla tbody tr:nth-child(even) { background-color: #f8fafc; }
        .tabla tbody tr:hover { background-color: #f1f5f9; }
        .signature {
            margin-top: 3rem;
            text-align: center;
            width: 100%;
        }
        .signature img {
            display: block;
            margin: 0 auto 0.2rem;
            max-width: 150px;
            height: auto;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 0.2rem auto 0.3rem;
        }
        .signature-text {
            font-size: 8px;
            text-align: center;
            margin: 0.2rem 0;
        }
        /* === HEADER MODERNO === */
        .header { width: 100%; background: #0A1628; border-radius: 8px; margin-bottom: 10px; padding: 8px 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-cell { width: 60px; padding-right: 12px !important; }
        .header-logo { width: 45px; height: 45px; background: white; border-radius: 6px; padding: 5px; text-align: center; }
        .header-logo img { max-height: 35px; max-width: 35px; }
        .header-title { font-size: 16px; font-weight: 700; color: white; letter-spacing: -0.5px; }
        .header-subtitle { font-size: 9px; color: #94a3b8; }
        .header-meta-cell { text-align: right; width: 120px; }
        .header-badge { background: rgba(255,255,255,0.15); padding: 5px 10px; border-radius: 5px; display: inline-block; margin-bottom: 4px; }
        .header-badge-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; }
        .header-badge-value { font-size: 12px; font-weight: 700; color: white; }
        .header-date { font-size: 9px; color: #94a3b8; }
        .patient-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 10px; }
        .patient-table { width: 100%; border-collapse: collapse; }
        .patient-table td { padding: 2px 6px; font-size: 10px; }
        .patient-name { font-size: 13px; font-weight: 700; color: #0A1628; margin-bottom: 6px; }
        .patient-label { color: #64748b; font-size: 9px; }
        .patient-value { font-weight: 600; color: #334155; }
        .patient-diagnosis { margin-top: 6px; padding-top: 6px; border-top: 1px solid #e2e8f0; font-size: 10px; }
        .patient-diagnosis-label { font-size: 9px; color: #64748b; font-weight: 600; }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 6px 20px; background: white; border-top: 2px solid #0A1628; font-size: 9px; }
        .page-footer-table { width: 100%; }
        .page-footer .clinic-name { font-weight: 700; color: #ef4444; }
        .page-footer .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 35px; }
    </style>
</head>
<body>
    <!-- PAGE FOOTER (fixed) -->
    <div class="page-footer">
        <table class="page-footer-table">
            <tr>
                <td class="clinic-name">{{ $clinica->nombre ?? '' }}</td>
                <td class="clinic-contact">
                    {{ $clinica->telefono ?? '' }}
                    @if($clinica->email ?? null)
                        | {{ $clinica->email }}
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <div class="content-wrapper">
    <!-- HEADER -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo-cell">
                    <div class="header-logo">
                        @if(isset($clinicaLogo) && $clinicaLogo)
                            <img src="{{ $clinicaLogo }}" alt="Logo">
                        @else
                            <span style="font-size: 24px;">❤️</span>
                        @endif
                    </div>
                </td>
                <td style="padding-left: 10px;">
                    <div class="header-title">Reporte Final - Rehabilitación Pulmonar</div>
                    <div class="header-subtitle">Programa de Rehabilitación Pulmonar</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Registro</div>
                        <div class="header-badge-value">#{{ $paciente->registro }}</div>
                    </div>
                    <div class="header-date">{{ $data->fecha_inicio ? date('d/m/Y', strtotime($data->fecha_inicio)) : 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>
    <!-- PATIENT INFO -->
    <div class="patient-card">
        <div class="patient-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
        <table class="patient-table">
            <tr>
                <td><span class="patient-label">F. Nacimiento:</span> <span class="patient-value">{{ $paciente->fechaNacimiento ? date('d/m/Y', strtotime($paciente->fechaNacimiento)) : 'N/A' }}</span></td>
                <td><span class="patient-label">Edad:</span> <span class="patient-value">{{ $paciente->fechaNacimiento ? \Carbon\Carbon::parse($paciente->fechaNacimiento)->age : 'N/A' }} años</span></td>
                @if($data->fecha_termino)
                <td><span class="patient-label">Fecha término:</span> <span class="patient-value">{{ date('d/m/Y', strtotime($data->fecha_termino)) }}</span></td>
                @endif
            </tr>
        </table>
    </div>

    <main class="mt-2">
        <!-- Diagnóstico -->
        @if($data->diagnostico)
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">DIAGNÓSTICO</h2>
            <div class="linea"></div>
        </div>
        <p class="f-9 text-justify m-t-0">{{ $data->diagnostico }}</p>
        @endif

        <!-- Descripción del Programa -->
        @if($data->descripcion_programa)
        <div class="contenedor">
            <h2 class="h8 titulo">DESCRIPCIÓN DEL PROGRAMA</h2>
            <div class="linea"></div>
        </div>
        <p class="f-9 text-justify m-t-0">{!! nl2br(e($data->descripcion_programa)) !!}</p>
        @endif

        <!-- Tabla Comparativa -->
        <div class="contenedor">
            <h2 class="h8 titulo">RESULTADOS DE PRUEBAS DE ESFUERZO</h2>
            <div class="linea"></div>
        </div>
        
        <table class="tabla m-t-0">
            <thead>
                <tr>
                    <th width="34%" class="bck-gray">Rubro</th>
                    <th width="33%" class="bck-blue">{{ $data->pe_inicial_rubro ?? 'Prueba Inicial' }}</th>
                    <th width="33%" class="bck-red">{{ $data->pe_final_rubro ?? 'Prueba Final' }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="f-bold">FC basal (lpm)</td>
                    <td class="text-center">{{ $data->pe_inicial_fc_basal ?? '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_fc_basal ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">SpO2 inicial (%)</td>
                    <td class="text-center">{{ $data->pe_inicial_spo2 ? $data->pe_inicial_spo2 . '%' : '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_spo2 ? $data->pe_final_spo2 . '%' : '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Litros de O2 ocupados</td>
                    <td class="text-center">{{ $data->pe_inicial_litros_oxigeno ? $data->pe_inicial_litros_oxigeno . ' L' : '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_litros_oxigeno ? $data->pe_final_litros_oxigeno . ' L' : '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Carga máxima (MET)</td>
                    <td class="text-center">{{ $data->pe_inicial_carga_maxima ?? '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_carga_maxima ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">VO2 pico (%)</td>
                    <td class="text-center">{{ $data->pe_inicial_vo2_pico ? $data->pe_inicial_vo2_pico . '%' : '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_vo2_pico ? $data->pe_final_vo2_pico . '%' : '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">VO2 Pico (ml/kg/min)</td>
                    <td class="text-center">{{ $data->pe_inicial_vo2_pico_ml ?? '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_vo2_pico_ml ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">FC pico ejercicio (lpm)</td>
                    <td class="text-center">{{ $data->pe_inicial_fc_pico ?? '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_fc_pico ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">BORG modificado disnea pico</td>
                    <td class="text-center">{{ $data->pe_inicial_borg_disnea ?? '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_borg_disnea ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">BORG modificado fatiga pico</td>
                    <td class="text-center">{{ $data->pe_inicial_borg_fatiga ?? '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_borg_fatiga ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Dinamometría (kg)</td>
                    <td class="text-center">{{ $data->pe_inicial_dinamometria ? $data->pe_inicial_dinamometria . ' kg' : '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_dinamometria ? $data->pe_final_dinamometria . ' kg' : '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Sit to Stand 30 seg (rep)</td>
                    <td class="text-center">{{ $data->pe_inicial_sit_to_stand_30seg ?? '-' }}</td>
                    <td class="text-center">{{ $data->pe_final_sit_to_stand_30seg ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Resultados Clínicos -->
        @if($data->resultados_clinicos)
        <div class="contenedor">
            <h2 class="h8 titulo">RESULTADOS CLÍNICOS</h2>
            <div class="linea"></div>
        </div>
        <p class="f-9 text-justify m-t-0">{!! nl2br(e($data->resultados_clinicos)) !!}</p>
        @endif

        <!-- Plan -->
        @if($data->plan)
        <div class="contenedor">
            <h2 class="h8 titulo">PLAN</h2>
            <div class="linea"></div>
        </div>
        <p class="f-9 text-justify m-t-0">{!! nl2br(e($data->plan)) !!}</p>
        @endif

        <p class="f-9 mt-3">Quedo a sus órdenes para cualquier duda o aclaración.</p>

        @if(isset($firmaBase64) && $firmaBase64)
        <!-- Firma del médico -->
        <div class="signature">
            <img src="{{ $firmaBase64 }}" alt="Firma">
            <div class="signature-line"></div>
            <p class="signature-text f-bold mb-0">{{ $user->nombre_con_titulo }}</p>
            <p class="signature-text mb-0">Médico Especialista en Medicina de Rehabilitación</p>
            <p class="signature-text mb-0">Alta especialidad en Rehabilitación Pulmonar</p>
            @if(!empty($user->cedula))
            <p class="signature-text mb-0">Cédula Profesional: {{ $user->cedula }}</p>
            @endif
        </div>
        @endif
    </main>
    </div><!-- End content-wrapper -->

</body>
</html>
