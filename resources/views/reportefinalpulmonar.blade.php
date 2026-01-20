<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Reporte Final Rehabilitación Pulmonar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
                        margin: 0;
            line-height: 1.2;
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
            font-size: 9px;
        }
        .f-10 {
            font-size: 10px;
        }
        .f-15 {
            font-size: 14px;
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
            background-color: white;
        }
        .linea {
            position: absolute;
            left: 0;
            right: 0;
            top: 0.6rem;
            border-bottom: 2px solid black;
            z-index: 0;
        }
        .m-t-0 {
            margin-top: -0.3rem;
        }
        .bck-blue {
            background-color: #4A90E2;
            color: white;
        }
        .bck-red {
            background-color: #E74C3C;
            color: white;
        }
        .bck-gray {
            background-color: #DDDEE1;
        }
        .tabla {
            font-size: 9px;
            margin-bottom: 1rem;
            width: 100%;
            border-collapse: collapse;
        }
        .tabla td, .tabla th {
            padding: 3px 5px;
            border: 1px solid #000;
        }
        .tabla th {
            font-weight: bold;
            text-align: center;
        }
        .signature {
            margin-top: 1.5rem;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 0 auto 5px;
            margin-top: 0.5rem;
        }
        .signature-text {
            font-size: 9px;
        }

        /* Firma */
        .signature-section {
            margin-top: 40px;
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #000;
            width: 300px;
            margin: 5px auto 5px auto;
        }

        .signature-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2px;
        }

        .signature-title {
            font-size: 10pt;
            color: #666;
        }

        .signature-credentials {
            font-size: 9pt;
            color: #888;
        }
    </style>
</head>
<body>
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <p class="f-bold f-15 text-center mb-0 mt-0">REPORTE DE TÉRMINO</p>
            <p class="f-bold text-center mb-0 mt-0">Programa de Rehabilitación Pulmonar</p>
            <img src="img/logo.png" alt="cercap logo" style="height: 80px" class="">
            <div class="medio">
                <p class="text-sm texto-izquierda mb-0 f-bold f-9">Fecha inicio: {{ $data->fecha_inicio ? date('d/m/Y', strtotime($data->fecha_inicio)) : 'N/A' }}</p>
                <span class="ml-5 text-right texto-derecha f-bold f-9">Registro: {{ $paciente->registro }}</span>
            </div>
            <br>
            <p class="f-bold mb-0 f-10">Nombre del paciente: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre }}</span></p>
            <p class="f-bold mb-0 f-10">Fecha de nacimiento: <span class="f-normal">{{ $paciente->fechaNacimiento ? date('d/m/Y', strtotime($paciente->fechaNacimiento)) : 'N/A' }}</span>
            <span class="f-bold ml-3">Edad: <span class="f-normal">{{ $paciente->fechaNacimiento ? \Carbon\Carbon::parse($paciente->fechaNacimiento)->age : 'N/A' }} años</span></span></p>
            @if($data->fecha_termino)
            <p class="f-bold mb-0 f-10">Fecha término: <span class="f-normal">{{ date('d/m/Y', strtotime($data->fecha_termino)) }}</span></p>
            @endif
        </div>
    </header>

    <main class="mt-0">
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
                    <th width="34%">Rubro</th>
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

        <!-- Firma del médico -->
        <div class="signature">
            @if(isset($firmaBase64))
                <img src="{{ $firmaBase64 }}" alt="Firma" style="max-width: 150px; height: auto">
            @endif
            <div class="signature-line"></div>
            <p class="signature-text f-bold mb-0">{{ $user->name }}</p>
            <p class="signature-text mb-0">Médico Especialista en Medicina de Rehabilitación</p>
            <p class="signature-text mb-0">Alta especialidad en Rehabilitación Pulmonar</p>
            @if($user->cedula_profesional)
            <p class="signature-text">Cédula: {{ $user->cedula_profesional }}</p>
            @endif
        </div>
    </main>

    <footer style="margin-top: 0.5rem; padding-top: 0.1rem; border-top: 1px solid #000;">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 50%; vertical-align: top;">
                <p class="f-9 mb-0"><strong>Torre Médica II</strong></p>
                <p class="f-9 mb-0">Real Mayorazgo 130, local 3</p>
                <p class="f-9 mb-0">Col. Xoco, Benito Juárez</p>
                <p class="f-9 mb-0">C.P. 03330 CDMX</p>
            </div>
            <div style="display: table-cell; width: 50%; vertical-align: top; text-align: right;">
                <p class="f-9 mb-0"><strong>Informes y citas:</strong></p>
                <p class="f-9 mb-0">☎ 55 2625 5547 / 55 2625 5548</p>
                <p class="f-9 mb-0">☎ 56 3034 8666</p>
                <p class="f-9 mb-0">✉ cercap.cardiopulmonar@gmail.com</p>
                <p class="f-9 mb-0"><strong>www.cercap.mx</strong></p>
            </div>
        </div>
    </footer>
</body>
</html>
