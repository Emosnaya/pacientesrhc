<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Odontograma</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
        }
        .logo-container {
            height: 60px;
            overflow: hidden;
            display: inline-block;
        }
        .logo-container img {
            height: 60px;
            width: auto;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            margin: 0 25px 15px 15px;
            padding: 10px;
        }
        .medio {
            position: relative;
            margin-bottom: 6px;
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
        .f-bold {
            font-weight: bold;
        }
        .f-normal {
            font-weight: normal;
        }
        .f-10 {
            font-size: 9px;
        }
        .f-12 {
            font-size: 10px;
        }
        .f-15 {
            font-size: 12px;
        }
        .text-center {
            text-align: center;
        }
        .mb-0 {
            margin-bottom: 0;
        }
        .mt-0 {
            margin-top: 0;
        }
        .section-title {
            background-color: #e8f4f8;
            padding: 3px 5px;
            margin-top: 5px;
            margin-bottom: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .odontograma-container {
            margin: 8px 0;
            padding: 6px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .odontograma-row {
            text-align: center;
            margin: 3px 0;
            line-height: 0;
        }
        .diente {
            display: inline-block;
            width: 24px;
            height: 36px;
            border: 2px solid #333;
            margin: 0 1px;
            text-align: center;
            padding: 3px 1px;
            vertical-align: top;
            background-color: #f0f8ff;
            position: relative;
        }
        .diente-numero {
            font-weight: bold;
            font-size: 8px;
            line-height: 1.2;
            color: #000;
        }
        .diente-multiple {
            position: absolute;
            bottom: 2px;
            right: 2px;
            font-size: 7px;
            color: #3b82f6;
            font-weight: bold;
        }
        .diente-estado {
            font-size: 7px;
            line-height: 1.1;
        }
        .diente-caras {
            font-size: 6px;
            line-height: 1.1;
            color: #dc2626;
        }
        .diente.caries {
            background-color: #ffcccc;
            border-color: #cc0000;
        }
        .diente.obturado {
            background-color: #b3d9ff;
            border-color: #0066cc;
        }
        .diente.ausente {
            background-color: #999999;
            border-color: #333333;
            color: white;
        }
        .diente.corona {
            background-color: #e6ccff;
            border-color: #9933cc;
        }
        .diente.fracturado {
            background-color: #ffcc99;
            border-color: #ff6600;
        }
        .diente.implante {
            background-color: #99e6cc;
            border-color: #009966;
        }
        .diente.extraccion_indicada {
            background-color: #ff9999;
            border: 2px solid #cc0000;
        }
        .diente.calculo_supragingival {
            background-color: #e6f7cc;
            border-color: #84cc16;
        }
        .diente.calculo_infragingival {
            background-color: #d4ebcc;
            border-color: #65a30d;
        }
        .diente.movilidad_dental {
            background-color: #fef3c7;
            border-color: #eab308;
        }
        .diente.bolsas_periodontales {
            background-color: #fed7aa;
            border-color: #f97316;
        }
        .diente.pseudobolsas {
            background-color: #fed7aa;
            border-color: #fb923c;
        }
        .diente.indice_placa {
            background-color: #fef3c7;
            border-color: #fbbf24;
        }
        .diente.endo_defectuosa {
            background-color: #fce7f3;
            border-color: #ec4899;
        }
        .diente.necrosis_pulpar {
            background-color: #fce7f3;
            border-color: #db2777;
        }
        .diente.pulpitis_irreversible {
            background-color: #fae8ff;
            border-color: #c026d3;
        }
        .diente.lesiones_periapicales {
            background-color: #f3e8ff;
            border-color: #a855f7;
        }
        .leyenda {
            margin: 5px 0;
            font-size: 6px;
            text-align: center;
            line-height: 0;
        }
        .leyenda-item {
            display: inline-block;
            margin: 0 5px 2px 0;
            padding: 2px 5px;
            border: 1px solid #333;
            line-height: 1.2;
            font-weight: bold;
            font-size: 7px;
        }
        .leyenda-item.sano {
            background-color: #f0f8ff;
            border-color: #333;
        }
        .leyenda-item.caries {
            background-color: #ffcccc;
            border-color: #cc0000;
        }
        .leyenda-item.obturado {
            background-color: #b3d9ff;
            border-color: #0066cc;
        }
        .leyenda-item.ausente {
            background-color: #999999;
            border-color: #333333;
            color: white;
        }
        .leyenda-item.corona {
            background-color: #e6ccff;
            border-color: #9933cc;
        }
        .leyenda-item.fracturado {
            background-color: #ffcc99;
            border-color: #ff6600;
        }
        .leyenda-item.implante {
            background-color: #99e6cc;
            border-color: #009966;
        }
        .leyenda-item.extraccion {
            background-color: #ff9999;
            border-color: #cc0000;
        }
        .arcada-label {
            font-size: 8px;
            font-weight: bold;
            margin: 3px 0;
            text-align: center;
        }
        .analisis-box {
            border: 1px solid #ddd;
            padding: 5px;
            margin: 5px 0;
            background-color: #fafafa;
        }
        .analisis-box h4 {
            font-size: 10px;
            font-weight: bold;
            margin: 0 0 5px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #ddd;
        }
        .analisis-item {
            font-size: 8px;
            margin: 3px 0;
            line-height: 1.3;
        }
        .firma-section {
            margin-top: 15px;
            padding-top: 8px;
            text-align: center;
            page-break-inside: avoid;
        }
        .firma-image {
            max-width: 150px;
            max-height: 50px;
        }
        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 3px auto;
            width: 250px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <header class="mb-0">
            <div class="paciente mt-0 mb-0">
                <p class="f-bold f-15 text-center mb-0 mt-0">Odontograma</p>
                <div class="logo-container"><img src="{{ $clinicaLogo }}" alt="logo clínica"></div>
                <div class="medio">
                    <p class="texto-izquierda mb-0 f-bold">Fecha: {{ date('d/m/Y', strtotime($data->fecha)) }}</p>
                    <p class="texto-derecha mb-0 f-bold">Registro: {{ $paciente->registro ?? 'N/A' }}</p>
                </div>
            </div>
        </header>

        <!-- Datos del Paciente -->
        <div class="section-title">DATOS DEL PACIENTE</div>
        <table class="no-border">
            <tr>
                <td colspan="3"><span class="f-bold">Nombre:</span> {{ $paciente->nombre ?? 'N/A' }} {{ $paciente->apellidoPat ?? '' }} {{ $paciente->apellidoMat ?? '' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">Edad:</span> {{ $paciente->edad ?? 'N/A' }}</td>
                <td><span class="f-bold">Género:</span> {{ isset($paciente->genero) ? ($paciente->genero == 1 ? 'Masculino' : 'Femenino') : 'N/A' }}</td>
                <td><span class="f-bold">Teléfono:</span> {{ $paciente->telefono ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Odontograma Visual -->
        <div class="section-title">ODONTOGRAMA (NUMERACIÓN FDI)</div>
        <div class="odontograma-container">
            <!-- Arcada Superior -->
            <div class="arcada-label">ARCADA SUPERIOR</div>
            <div class="odontograma-row">
                @foreach([18, 17, 16, 15, 14, 13, 12, 11] as $numero)
                    @php
                        $diente = collect($dientes)->firstWhere('numero', $numero);
                        // Soporte para formato nuevo (estados array) y viejo (estado string)
                        $estados = $diente['estados'] ?? (isset($diente['estado']) ? [$diente['estado']] : ['sano']);
                        $estadoPrincipal = in_array('ausente', $estados) ? 'ausente' : ($estados[0] ?? 'sano');
                        $caras = $diente['caras_afectadas'] ?? [];
                        $notas = $diente['notas'] ?? '';
                        $estadoTexto = [
                            'sano' => '',
                            'caries' => 'C',
                            'obturado' => 'O',
                            'ausente' => 'A',
                            'corona' => 'Co',
                            'fracturado' => 'F',
                            'implante' => 'I',
                            'extraccion_indicada' => 'E',
                            'calculo_supragingival' => 'CS',
                            'calculo_infragingival' => 'CI',
                            'movilidad_dental' => 'M',
                            'bolsas_periodontales' => 'BP',
                            'pseudobolsas' => 'PB',
                            'indice_placa' => 'IP',
                            'endo_defectuosa' => 'ED',
                            'necrosis_pulpar' => 'NP',
                            'pulpitis_irreversible' => 'PI',
                            'lesiones_periapicales' => 'LP'
                        ];
                        // Crear texto de múltiples diagnósticos
                        $textoEstados = [];
                        foreach($estados as $est) {
                            if($est !== 'sano' && isset($estadoTexto[$est])) {
                                $textoEstados[] = $estadoTexto[$est];
                            }
                        }
                        $totalEstados = count($textoEstados);
                        $estadosMostrar = array_slice($textoEstados, 0, 2); // Máximo 2 diagnósticos
                    @endphp
                    <div class="diente {{ $estadoPrincipal }}">
                        <div class="diente-numero">{{ $numero }}</div>
                        @if(count($estadosMostrar) > 0)
                            <div class="diente-estado">{{ implode(' ', $estadosMostrar) }}</div>
                        @endif
                        @if(count($caras) > 0)
                            <div class="diente-caras">{{ implode(',', array_map(function($c) { return substr($c, 0, 1); }, $caras)) }}</div>
                        @endif
                    </div>
                @endforeach
                <span style="display: inline-block; width: 10px;"></span>
                @foreach([21, 22, 23, 24, 25, 26, 27, 28] as $numero)
                    @php
                        $diente = collect($dientes)->firstWhere('numero', $numero);
                        $estados = $diente['estados'] ?? (isset($diente['estado']) ? [$diente['estado']] : ['sano']);
                        $estadoPrincipal = in_array('ausente', $estados) ? 'ausente' : ($estados[0] ?? 'sano');
                        $caras = $diente['caras_afectadas'] ?? [];
                        $estadoTexto = [
                            'sano' => '', 'caries' => 'C', 'obturado' => 'O', 'ausente' => 'A',
                            'corona' => 'Co', 'fracturado' => 'F', 'implante' => 'I', 'extraccion_indicada' => 'E',
                            'calculo_supragingival' => 'CS', 'calculo_infragingival' => 'CI',
                            'movilidad_dental' => 'M', 'bolsas_periodontales' => 'BP',
                            'pseudobolsas' => 'PB', 'indice_placa' => 'IP',
                            'endo_defectuosa' => 'ED', 'necrosis_pulpar' => 'NP',
                            'pulpitis_irreversible' => 'PI', 'lesiones_periapicales' => 'LP'
                        ];
                        $textoEstados = [];
                        foreach($estados as $est) {
                            if($est !== 'sano' && isset($estadoTexto[$est])) {
                                $textoEstados[] = $estadoTexto[$est];
                            }
                        }
                        $totalEstados = count($textoEstados);
                        $estadosMostrar = array_slice($textoEstados, 0, 2);
                    @endphp
                    <div class="diente {{ $estadoPrincipal }}">
                        <div class="diente-numero">{{ $numero }}</div>
                        @if(count($estadosMostrar) > 0)
                            <div class="diente-estado">{{ implode(' ', $estadosMostrar) }}</div>
                        @endif
                        @if(count($caras) > 0)
                            <div class="diente-caras">{{ implode(',', array_map(function($c) { return substr($c, 0, 1); }, $caras)) }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Deciduos Superiores -->
            <div class="odontograma-row" style="margin-top: 3px;">
                <span style="display: inline-block; width: 0px;"></span>
                @foreach([55, 54, 53, 52, 51] as $numero)
                    @php
                        $diente = collect($dientes)->firstWhere('numero', $numero);
                        $estados = $diente['estados'] ?? (isset($diente['estado']) ? [$diente['estado']] : ['sano']);
                        $estadoPrincipal = in_array('ausente', $estados) ? 'ausente' : ($estados[0] ?? 'sano');
                        $caras = $diente['caras_afectadas'] ?? [];
                        $estadoTexto = [
                            'sano' => '', 'caries' => 'C', 'obturado' => 'O', 'ausente' => 'A',
                            'corona' => 'Co', 'fracturado' => 'F', 'implante' => 'I', 'extraccion_indicada' => 'E',
                            'calculo_supragingival' => 'CS', 'calculo_infragingival' => 'CI',
                            'movilidad_dental' => 'M', 'bolsas_periodontales' => 'BP',
                            'pseudobolsas' => 'PB', 'indice_placa' => 'IP',
                            'endo_defectuosa' => 'ED', 'necrosis_pulpar' => 'NP',
                            'pulpitis_irreversible' => 'PI', 'lesiones_periapicales' => 'LP'
                        ];
                        $textoEstados = [];
                        foreach($estados as $est) {
                            if($est !== 'sano' && isset($estadoTexto[$est])) {
                                $textoEstados[] = $estadoTexto[$est];
                            }
                        }
                        $totalEstados = count($textoEstados);
                        $estadosMostrar = array_slice($textoEstados, 0, 2);
                    @endphp
                    <div class="diente {{ $estadoPrincipal }}">
                        <div class="diente-numero">{{ $numero }}</div>
                        @if(count($estadosMostrar) > 0)
                            <div class="diente-estado">{{ implode(' ', $estadosMostrar) }}</div>
                        @endif
                        @if(count($caras) > 0)
                            <div class="diente-caras">{{ implode(',', array_map(function($c) { return substr($c, 0, 1); }, $caras)) }}</div>
                        @endif
                    </div>
                @endforeach
                <span style="display: inline-block; width: 10px;"></span>
                @foreach([61, 62, 63, 64, 65] as $numero)
                    @php
                        $diente = collect($dientes)->firstWhere('numero', $numero);
                        $estados = $diente['estados'] ?? (isset($diente['estado']) ? [$diente['estado']] : ['sano']);
                        $estadoPrincipal = in_array('ausente', $estados) ? 'ausente' : ($estados[0] ?? 'sano');
                        $caras = $diente['caras_afectadas'] ?? [];
                        $estadoTexto = [
                            'sano' => '', 'caries' => 'C', 'obturado' => 'O', 'ausente' => 'A',
                            'corona' => 'Co', 'fracturado' => 'F', 'implante' => 'I', 'extraccion_indicada' => 'E',
                            'calculo_supragingival' => 'CS', 'calculo_infragingival' => 'CI',
                            'movilidad_dental' => 'M', 'bolsas_periodontales' => 'BP',
                            'pseudobolsas' => 'PB', 'indice_placa' => 'IP',
                            'endo_defectuosa' => 'ED', 'necrosis_pulpar' => 'NP',
                            'pulpitis_irreversible' => 'PI', 'lesiones_periapicales' => 'LP'
                        ];
                        $textoEstados = [];
                        foreach($estados as $est) {
                            if($est !== 'sano' && isset($estadoTexto[$est])) {
                                $textoEstados[] = $estadoTexto[$est];
                            }
                        }
                        $totalEstados = count($textoEstados);
                        $estadosMostrar = array_slice($textoEstados, 0, 2);
                    @endphp
                    <div class="diente {{ $estadoPrincipal }}">
                        <div class="diente-numero">{{ $numero }}</div>
                        @if(count($estadosMostrar) > 0)
                            <div class="diente-estado">{{ implode(' ', $estadosMostrar) }}</div>
                        @endif
                        @if(count($caras) > 0)
                            <div class="diente-caras">{{ implode(',', array_map(function($c) { return substr($c, 0, 1); }, $caras)) }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Separador -->
            <div style="height: 8px; border-top: 2px solid #666; margin: 8px 40px;"></div>

            <!-- Arcada Inferior -->
            <div class="arcada-label">ARCADA INFERIOR</div>
            
            <!-- Arcada Inferior Permanentes -->
            <div class="odontograma-row">
                @foreach([48, 47, 46, 45, 44, 43, 42, 41] as $numero)
                    @php
                        $diente = collect($dientes)->firstWhere('numero', $numero);
                        $estados = $diente['estados'] ?? (isset($diente['estado']) ? [$diente['estado']] : ['sano']);
                        $estadoPrincipal = in_array('ausente', $estados) ? 'ausente' : ($estados[0] ?? 'sano');
                        $caras = $diente['caras_afectadas'] ?? [];
                        $estadoTexto = [
                            'sano' => '', 'caries' => 'C', 'obturado' => 'O', 'ausente' => 'A',
                            'corona' => 'Co', 'fracturado' => 'F', 'implante' => 'I', 'extraccion_indicada' => 'E',
                            'calculo_supragingival' => 'CS', 'calculo_infragingival' => 'CI',
                            'movilidad_dental' => 'M', 'bolsas_periodontales' => 'BP',
                            'pseudobolsas' => 'PB', 'indice_placa' => 'IP',
                            'endo_defectuosa' => 'ED', 'necrosis_pulpar' => 'NP',
                            'pulpitis_irreversible' => 'PI', 'lesiones_periapicales' => 'LP'
                        ];
                        $textoEstados = [];
                        foreach($estados as $est) {
                            if($est !== 'sano' && isset($estadoTexto[$est])) {
                                $textoEstados[] = $estadoTexto[$est];
                            }
                        }
                        $totalEstados = count($textoEstados);
                        $estadosMostrar = array_slice($textoEstados, 0, 2);
                    @endphp
                    <div class="diente {{ $estadoPrincipal }}">
                        <div class="diente-numero">{{ $numero }}</div>
                        @if(count($estadosMostrar) > 0)
                            <div class="diente-estado">{{ implode(' ', $estadosMostrar) }}</div>
                        @endif
                        @if(count($caras) > 0)
                            <div class="diente-caras">{{ implode(',', array_map(function($c) { return substr($c, 0, 1); }, $caras)) }}</div>
                        @endif
                    </div>
                @endforeach
                <span style="display: inline-block; width: 10px;"></span>
                @foreach([31, 32, 33, 34, 35, 36, 37, 38] as $numero)
                    @php
                        $diente = collect($dientes)->firstWhere('numero', $numero);
                        $estados = $diente['estados'] ?? (isset($diente['estado']) ? [$diente['estado']] : ['sano']);
                        $estadoPrincipal = in_array('ausente', $estados) ? 'ausente' : ($estados[0] ?? 'sano');
                        $caras = $diente['caras_afectadas'] ?? [];
                        $estadoTexto = [
                            'sano' => '', 'caries' => 'C', 'obturado' => 'O', 'ausente' => 'A',
                            'corona' => 'Co', 'fracturado' => 'F', 'implante' => 'I', 'extraccion_indicada' => 'E',
                            'calculo_supragingival' => 'CS', 'calculo_infragingival' => 'CI',
                            'movilidad_dental' => 'M', 'bolsas_periodontales' => 'BP',
                            'pseudobolsas' => 'PB', 'indice_placa' => 'IP',
                            'endo_defectuosa' => 'ED', 'necrosis_pulpar' => 'NP',
                            'pulpitis_irreversible' => 'PI', 'lesiones_periapicales' => 'LP'
                        ];
                        $textoEstados = [];
                        foreach($estados as $est) {
                            if($est !== 'sano' && isset($estadoTexto[$est])) {
                                $textoEstados[] = $estadoTexto[$est];
                            }
                        }
                        $totalEstados = count($textoEstados);
                        $estadosMostrar = array_slice($textoEstados, 0, 2);
                    @endphp
                    <div class="diente {{ $estadoPrincipal }}">
                        <div class="diente-numero">{{ $numero }}</div>
                        @if(count($estadosMostrar) > 0)
                            <div class="diente-estado">{{ implode(' ', $estadosMostrar) }}</div>
                        @endif
                        @if(count($caras) > 0)
                            <div class="diente-caras">{{ implode(',', array_map(function($c) { return substr($c, 0, 1); }, $caras)) }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Deciduos Inferiores -->
            <div class="odontograma-row" style="margin-top: 3px;">
                <span style="display: inline-block; width: 0px;"></span>
                @foreach([85, 84, 83, 82, 81] as $numero)
                    @php
                        $diente = collect($dientes)->firstWhere('numero', $numero);
                        $estados = $diente['estados'] ?? (isset($diente['estado']) ? [$diente['estado']] : ['sano']);
                        $estadoPrincipal = in_array('ausente', $estados) ? 'ausente' : ($estados[0] ?? 'sano');
                        $caras = $diente['caras_afectadas'] ?? [];
                        $estadoTexto = [
                            'sano' => '', 'caries' => 'C', 'obturado' => 'O', 'ausente' => 'A',
                            'corona' => 'Co', 'fracturado' => 'F', 'implante' => 'I', 'extraccion_indicada' => 'E',
                            'calculo_supragingival' => 'CS', 'calculo_infragingival' => 'CI',
                            'movilidad_dental' => 'M', 'bolsas_periodontales' => 'BP',
                            'pseudobolsas' => 'PB', 'indice_placa' => 'IP',
                            'endo_defectuosa' => 'ED', 'necrosis_pulpar' => 'NP',
                            'pulpitis_irreversible' => 'PI', 'lesiones_periapicales' => 'LP'
                        ];
                        $textoEstados = [];
                        foreach($estados as $est) {
                            if($est !== 'sano' && isset($estadoTexto[$est])) {
                                $textoEstados[] = $estadoTexto[$est];
                            }
                        }
                        $totalEstados = count($textoEstados);
                        $estadosMostrar = array_slice($textoEstados, 0, 2);
                    @endphp
                    <div class="diente {{ $estadoPrincipal }}">
                        <div class="diente-numero">{{ $numero }}</div>
                        @if(count($estadosMostrar) > 0)
                            <div class="diente-estado">{{ implode(' ', $estadosMostrar) }}</div>
                        @endif
                        @if(count($caras) > 0)
                            <div class="diente-caras">{{ implode(',', array_map(function($c) { return substr($c, 0, 1); }, $caras)) }}</div>
                        @endif
                    </div>
                @endforeach
                <span style="display: inline-block; width: 10px;"></span>
                @foreach([71, 72, 73, 74, 75] as $numero)
                    @php
                        $diente = collect($dientes)->firstWhere('numero', $numero);
                        $estados = $diente['estados'] ?? (isset($diente['estado']) ? [$diente['estado']] : ['sano']);
                        $estadoPrincipal = in_array('ausente', $estados) ? 'ausente' : ($estados[0] ?? 'sano');
                        $caras = $diente['caras_afectadas'] ?? [];
                        $estadoTexto = [
                            'sano' => '', 'caries' => 'C', 'obturado' => 'O', 'ausente' => 'A',
                            'corona' => 'Co', 'fracturado' => 'F', 'implante' => 'I', 'extraccion_indicada' => 'E',
                            'calculo_supragingival' => 'CS', 'calculo_infragingival' => 'CI',
                            'movilidad_dental' => 'M', 'bolsas_periodontales' => 'BP',
                            'pseudobolsas' => 'PB', 'indice_placa' => 'IP',
                            'endo_defectuosa' => 'ED', 'necrosis_pulpar' => 'NP',
                            'pulpitis_irreversible' => 'PI', 'lesiones_periapicales' => 'LP'
                        ];
                        $textoEstados = [];
                        foreach($estados as $est) {
                            if($est !== 'sano' && isset($estadoTexto[$est])) {
                                $textoEstados[] = $estadoTexto[$est];
                            }
                        }
                        $totalEstados = count($textoEstados);
                        $estadosMostrar = array_slice($textoEstados, 0, 2);
                    @endphp
                    <div class="diente {{ $estadoPrincipal }}">
                        <div class="diente-numero">{{ $numero }}</div>
                        @if(count($estadosMostrar) > 0)
                            <div class="diente-estado">{{ implode(' ', $estadosMostrar) }}</div>
                        @endif
                        @if(count($caras) > 0)
                            <div class="diente-caras">{{ implode(',', array_map(function($c) { return substr($c, 0, 1); }, $caras)) }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Leyenda -->
            <div class="leyenda">
                <div style="font-size: 8px; font-weight: bold; margin-bottom: 3px; text-align: left;">Estados Básicos:</div>
                <span class="leyenda-item caries">C=Caries</span>
                <span class="leyenda-item obturado">O=Obturado</span>
                <span class="leyenda-item ausente">A=Ausente</span>
                <span class="leyenda-item corona">Co=Corona</span>
                <span class="leyenda-item fracturado">F=Fracturado</span>
                <span class="leyenda-item implante">I=Implante</span>
                <span class="leyenda-item extraccion">E=Extracción</span>
            </div>
            <div class="leyenda" style="margin-top: 3px;">
                <div style="font-size: 8px; font-weight: bold; margin-bottom: 3px; text-align: left;">Análisis Periodontal:</div>
                <span class="leyenda-item" style="background-color: #e6f7cc; border-color: #84cc16;">CS=Cálc.Suprag.</span>
                <span class="leyenda-item" style="background-color: #d4ebcc; border-color: #65a30d;">CI=Cálc.Infrag.</span>
                <span class="leyenda-item" style="background-color: #fef3c7; border-color: #eab308;">M=Movilidad</span>
                <span class="leyenda-item" style="background-color: #fed7aa; border-color: #f97316;">BP=Bolsas Per.</span>
                <span class="leyenda-item" style="background-color: #fed7aa; border-color: #fb923c;">PB=Pseudobolsas</span>
                <span class="leyenda-item" style="background-color: #fef3c7; border-color: #fbbf24;">IP=Índ.Placa</span>
            </div>
            <div class="leyenda" style="margin-top: 3px;">
                <div style="font-size: 8px; font-weight: bold; margin-bottom: 3px; text-align: left;">Análisis Endodóntico:</div>
                <span class="leyenda-item" style="background-color: #fce7f3; border-color: #ec4899;">ED=Endo Defect.</span>
                <span class="leyenda-item" style="background-color: #fce7f3; border-color: #db2777;">NP=Necros.Pulp.</span>
                <span class="leyenda-item" style="background-color: #fae8ff; border-color: #c026d3;">PI=Pulp.Irrev.</span>
                <span class="leyenda-item" style="background-color: #f3e8ff; border-color: #a855f7;">LP=Les.Periap.</span>
            </div>
        </div>

        <!-- Detalle de Diagnósticos por Diente -->
        @php
            // Recopilar todos los dientes con diagnósticos
            $dientesConDiagnosticos = [];
            $estadoNombres = [
                'caries' => 'Caries',
                'obturado' => 'Obturado',
                'ausente' => 'Ausente',
                'corona' => 'Corona',
                'fracturado' => 'Fracturado',
                'implante' => 'Implante',
                'extraccion_indicada' => 'Extracción Indicada',
                'calculo_supragingival' => 'Cálculo Supragingival',
                'calculo_infragingival' => 'Cálculo Infragingival',
                'movilidad_dental' => 'Movilidad Dental',
                'bolsas_periodontales' => 'Bolsas Periodontales',
                'pseudobolsas' => 'Pseudobolsas',
                'indice_placa' => 'Índice de Placa',
                'endo_defectuosa' => 'Endodoncia Defectuosa',
                'necrosis_pulpar' => 'Necrosis Pulpar',
                'pulpitis_irreversible' => 'Pulpitis Irreversible',
                'lesiones_periapicales' => 'Lesiones Periapicales'
            ];

            foreach($dientes as $diente) {
                $estados = $diente['estados'] ?? (isset($diente['estado']) ? [$diente['estado']] : ['sano']);
                $diagnosticos = [];
                foreach($estados as $est) {
                    if($est !== 'sano' && isset($estadoNombres[$est])) {
                        $diagnosticos[] = $estadoNombres[$est];
                    }
                }
                if(count($diagnosticos) > 0) {
                    $carasAfectadas = $diente['caras_afectadas'] ?? [];
                    $notas = $diente['notas'] ?? '';
                    $dientesConDiagnosticos[] = [
                        'numero' => $diente['numero'],
                        'diagnosticos' => $diagnosticos,
                        'caras' => $carasAfectadas,
                        'notas' => $notas
                    ];
                }
            }
        @endphp

        @if(count($dientesConDiagnosticos) > 0)
        <div class="section-title">DETALLE DE DIAGNÓSTICOS</div>
        <table style="width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 10px;">
            <thead>
                <tr style="background-color: #e8f4f8;">
                    <th style="border: 1px solid #ddd; padding: 4px; text-align: center; font-weight: bold; width: 50px;">Diente</th>
                    <th style="border: 1px solid #ddd; padding: 4px; text-align: left; font-weight: bold;">Diagnóstico(s)</th>
                    <th style="border: 1px solid #ddd; padding: 4px; text-align: center; font-weight: bold; width: 80px;">Caras</th>
                    <th style="border: 1px solid #ddd; padding: 4px; text-align: left; font-weight: bold;">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dientesConDiagnosticos as $d)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 4px; text-align: center; font-weight: bold;">{{ $d['numero'] }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px;">{{ implode(', ', $d['diagnosticos']) }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; text-align: center; font-size: 7px;">
                        {{ count($d['caras']) > 0 ? implode(', ', $d['caras']) : '-' }}
                    </td>
                    <td style="border: 1px solid #ddd; padding: 4px; font-size: 7px;">
                        {{ $d['notas'] ?: '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Análisis Periodontal -->
        @php
            $tienePeriodontal = $data->ap_calculo_supragingival || $data->ap_calculo_infragingival || 
                                $data->ap_movilidad_dental || $data->ap_bolsas_periodontales || 
                                $data->ap_pseudobolsas || $data->ap_indice_placa;
        @endphp
        @if($tienePeriodontal)
        <div class="section-title">ANÁLISIS PERIODONTAL</div>
        <div class="analisis-box">
            @if($data->ap_calculo_supragingival)
                <div class="analisis-item">
                    <span class="f-bold">• Cálculo Supragingival:</span> 
                    {{ $data->ap_calculo_supragingival_dientes ?? 'Presente' }}
                </div>
            @endif
            @if($data->ap_calculo_infragingival)
                <div class="analisis-item">
                    <span class="f-bold">• Cálculo Infragingival:</span> 
                    {{ $data->ap_calculo_infragingival_dientes ?? 'Presente' }}
                </div>
            @endif
            @if($data->ap_movilidad_dental)
                <div class="analisis-item">
                    <span class="f-bold">• Movilidad Dental:</span> 
                    {{ $data->ap_movilidad_dental_dientes ?? 'Presente' }}
                </div>
            @endif
            @if($data->ap_bolsas_periodontales)
                <div class="analisis-item">
                    <span class="f-bold">• Bolsas Periodontales:</span> 
                    {{ $data->ap_bolsas_periodontales_dientes ?? 'Presente' }}
                </div>
            @endif
            @if($data->ap_pseudobolsas)
                <div class="analisis-item">
                    <span class="f-bold">• Pseudobolsas:</span> 
                    {{ $data->ap_pseudobolsas_dientes ?? 'Presente' }}
                </div>
            @endif
            @if($data->ap_indice_placa)
                <div class="analisis-item">
                    <span class="f-bold">• Índice de Placa:</span> 
                    {{ $data->ap_indice_placa_dientes ?? 'Presente' }}
                </div>
            @endif
        </div>
        @endif

        <!-- Análisis Endodóntico -->
        @php
            $tieneEndodontico = $data->ae_endo_defectuosa || $data->ae_necrosis_pulpar || 
                               $data->ae_pulpitis_irreversible || $data->ae_lesiones_periapicales;
        @endphp
        @if($tieneEndodontico)
        <div class="section-title">ANÁLISIS ENDODÓNTICO</div>
        <div class="analisis-box">
            @if($data->ae_endo_defectuosa)
                <div class="analisis-item">
                    <span class="f-bold">• Endodoncia Defectuosa:</span> 
                    {{ $data->ae_endo_defectuosa_dientes ?? 'Presente' }}
                </div>
            @endif
            @if($data->ae_necrosis_pulpar)
                <div class="analisis-item">
                    <span class="f-bold">• Necrosis Pulpar:</span> 
                    {{ $data->ae_necrosis_pulpar_dientes ?? 'Presente' }}
                </div>
            @endif
            @if($data->ae_pulpitis_irreversible)
                <div class="analisis-item">
                    <span class="f-bold">• Pulpitis Irreversible:</span> 
                    {{ $data->ae_pulpitis_irreversible_dientes ?? 'Presente' }}
                </div>
            @endif
            @if($data->ae_lesiones_periapicales)
                <div class="analisis-item">
                    <span class="f-bold">• Lesiones Periapicales:</span> 
                    {{ $data->ae_lesiones_periapicales_dientes ?? 'Presente' }}
                </div>
            @endif
        </div>
        @endif

        <!-- Diagnóstico -->
        @if($data->diagnostico)
        <div class="section-title">DIAGNÓSTICO</div>
        <div style="padding: 5px; font-size: 8px; line-height: 1.4; white-space: pre-wrap;">{{ $data->diagnostico }}</div>
        @endif

        <!-- Pronóstico -->
        @if($data->pronostico)
        <div class="section-title">PRONÓSTICO</div>
        <div style="padding: 5px; font-size: 8px; line-height: 1.4; white-space: pre-wrap;">{{ $data->pronostico }}</div>
        @endif

        <!-- Observaciones -->
        @if($data->observaciones)
        <div class="section-title">OBSERVACIONES</div>
        <div style="padding: 5px; font-size: 8px; line-height: 1.4; white-space: pre-wrap;">{{ $data->observaciones }}</div>
        @endif

        <!-- Firma -->
        <div class="firma-section">
            @if(isset($firmaBase64) && $firmaBase64)
                <img src="{{ $firmaBase64 }}" alt="Firma" class="firma-image">
            @endif
            <hr>
            @if($user)
                <p class="mb-0 f-10">
                    <span class="f-bold">{{ $user->nombre }} {{ $user->apellidoPat }} {{ $user->apellidoMat }}</span><br>
                    @if($user->cedula)
                        Cédula Profesional: {{ $user->cedula }}
                    @endif
                </p>
            @else
                <p class="mb-0 f-10">
                    <span class="f-bold">Médico</span><br>
                    Cédula Profesional: N/A
                </p>
            @endif
        </div>
    </div>
</body>
</html>
