<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Cualidades Físicas No Aeróbicas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
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
            margin-bottom: 0.5rem;
            margin-top: 1.5rem;
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
        .tabla-signos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        .tabla-signos td {
            padding: 2px 5px;
            border: 1px solid #000;
        }
        .signature {
            margin-top: 1.5rem;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 0 auto 5px;
            margin-top: 1.5rem;
        }
        .signature-text {
            font-size: 9px;
        }
        .page-break {
            page-break-after: always;
        }
        .inline-field {
            display: inline-block;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <!-- PÁGINA 1: INFORMACIÓN GENERAL Y SIGNOS VITALES -->
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <p class="f-bold f-15 text-center mb-0 mt-0">FORMATO DE VALORACIÓN</p>
            <p class="f-bold text-center mb-0 mt-0">Cualidades Físicas No Aeróbicas</p>
            <img src="img/logo.png" alt="cercap logo" style="height: 80px" class="">
            <div class="medio">
                <p class="text-sm texto-izquierda mb-0 f-bold f-9">Fecha prueba inicial: {{ $data->fecha_prueba_inicial ? date('d/m/Y', strtotime($data->fecha_prueba_inicial)) : 'N/A' }}</p>
                <span class="ml-5 text-right texto-derecha f-bold f-9">Registro: {{ $paciente->registro }}</span>
            </div>
            <br>
            <p class="f-bold mb-0 f-10">Nombre del paciente: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre }}</span></p>
            <p class="f-bold mb-0 f-10">Fecha de nacimiento: <span class="f-normal">{{ $paciente->fechaNacimiento ? date('d/m/Y', strtotime($paciente->fechaNacimiento)) : 'N/A' }}</span>
            <span class="f-bold ml-3">ID del paciente: <span class="f-normal">{{ $paciente->registro }}</span></span></p>
        </div>
    </header>

    <main class="mt-0">
        <!-- Signos Vitales -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">SIGNOS VITALES</h2>
            <div class="linea"></div>
        </div>
        
        <table class="tabla-signos m-t-0">
            <tr>
                <td colspan="2" class="bck-blue text-center"><strong>Prueba Inicial</strong></td>
                <td colspan="2" class="bck-red text-center"><strong>Prueba Final</strong></td>
            </tr>
            <tr>
                <td width="20%" class="f-bold">Signos vitales prueba inicial:</td>
                <td width="30%"></td>
                <td width="20%" class="f-bold">Signos vitales prueba final:</td>
                <td width="30%"></td>
            </tr>
            <tr>
                <td class="f-bold">TA:</td>
                <td>{{ $data->ta_inicial ?? '_____' }} mmHg</td>
                <td class="f-bold">TA:</td>
                <td>{{ $data->ta_final ?? '_____' }} mmHg</td>
            </tr>
            <tr>
                <td class="f-bold">FC:</td>
                <td>{{ $data->fc_inicial ?? '_____' }} lpm</td>
                <td class="f-bold">FC:</td>
                <td>{{ $data->fc_final ?? '_____' }} lpm</td>
            </tr>
            <tr>
                <td class="f-bold">SatO2:</td>
                <td>{{ $data->sato2_inicial ?? '_____' }} %</td>
                <td class="f-bold">SatO2:</td>
                <td>{{ $data->sato2_final ?? '_____' }} %</td>
            </tr>
            <tr>
                <td class="f-bold">Talla:</td>
                <td>{{ $data->talla_inicial ?? '_____' }} m</td>
                <td class="f-bold">Talla:</td>
                <td>{{ $data->talla_final ?? '_____' }} m</td>
            </tr>
            <tr>
                <td class="f-bold">Peso:</td>
                <td>{{ $data->peso_inicial ?? '_____' }} kg</td>
                <td class="f-bold">Peso:</td>
                <td>{{ $data->peso_final ?? '_____' }} kg</td>
            </tr>
            <tr>
                <td class="f-bold">Perímetro abdominal:</td>
                <td>{{ $data->perimetria_abdominal_inicial ?? '_____' }} cm</td>
                <td class="f-bold">Perímetro abdominal:</td>
                <td>{{ $data->perimetria_abdominal_final ?? '_____' }} cm</td>
            </tr>
        </table>

        <p class="f-bold f-10" style="margin-top: 1rem; margin-bottom: 0.3rem;">Antecedentes musculo esqueléticos (ej. fracturas, cirugía, trauma, entre otros):</p>
        <p class="f-normal f-9" style="margin-bottom: 1rem;">{{ $data->antecedentes_musculo_esqueleticos ?? 'N/A' }}</p>

        <!-- Dinamometría -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">DINAMOMETRÍA</h2>
            <div class="linea"></div>
        </div>

        <table class="tabla m-t-0">
            <thead>
                <tr>
                    <th rowspan="2" width="15%">Mano</th>
                    <th colspan="4" class="bck-blue">Prueba Inicial</th>
                    <th colspan="4" class="bck-red">Prueba Final</th>
                </tr>
                <tr>
                    <th class="bck-blue">Toma 1</th>
                    <th class="bck-blue">Toma 2</th>
                    <th class="bck-blue">Toma 3</th>
                    <th class="bck-blue">Promedio</th>
                    <th class="bck-red">Toma 1</th>
                    <th class="bck-red">Toma 2</th>
                    <th class="bck-red">Toma 3</th>
                    <th class="bck-red">Promedio</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="f-bold">Mano derecha</td>
                    <td>{{ $data->dinamometria_mano_derecha_toma1_inicial ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_derecha_toma2_inicial ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_derecha_toma3_inicial ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_derecha_promedio_inicial ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_derecha_toma1_final ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_derecha_toma2_final ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_derecha_toma3_final ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_derecha_promedio_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Mano izquierda</td>
                    <td>{{ $data->dinamometria_mano_izquierda_toma1_inicial ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_izquierda_toma2_inicial ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_izquierda_toma3_inicial ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_izquierda_promedio_inicial ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_izquierda_toma1_final ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_izquierda_toma2_final ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_izquierda_toma3_final ?? '' }}</td>
                    <td>{{ $data->dinamometria_mano_izquierda_promedio_final ?? '' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Fuerza Global (MRC) -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">FUERZA GLOBAL (MRC)</h2>
            <div class="linea"></div>
        </div>

        <table class="tabla m-t-0">
            <thead>
                <tr>
                    <th rowspan="2" width="25%">Movimiento</th>
                    <th colspan="2" class="bck-blue">Prueba Inicial</th>
                    <th colspan="2" class="bck-red">Prueba Final</th>
                </tr>
                <tr>
                    <th class="bck-blue">Derecha</th>
                    <th class="bck-blue">Izquierda</th>
                    <th class="bck-red">Derecha</th>
                    <th class="bck-red">Izquierda</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="f-bold">ABD de hombro</td>
                    <td>{{ $data->abd_hombro_derecho_inicial ?? '' }}</td>
                    <td>{{ $data->abd_hombro_izquierdo_inicial ?? '' }}</td>
                    <td>{{ $data->abd_hombro_derecho_final ?? '' }}</td>
                    <td>{{ $data->abd_hombro_izquierdo_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Flexión de codo</td>
                    <td>{{ $data->flexion_codo_derecho_inicial ?? '' }}</td>
                    <td>{{ $data->flexion_codo_izquierdo_inicial ?? '' }}</td>
                    <td>{{ $data->flexion_codo_derecho_final ?? '' }}</td>
                    <td>{{ $data->flexion_codo_izquierdo_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Extensión de muñeca</td>
                    <td>{{ $data->extension_muneca_derecho_inicial ?? '' }}</td>
                    <td>{{ $data->extension_muneca_izquierdo_inicial ?? '' }}</td>
                    <td>{{ $data->extension_muneca_derecho_final ?? '' }}</td>
                    <td>{{ $data->extension_muneca_izquierdo_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Flexión de cadera</td>
                    <td>{{ $data->extension_cadera_derecho_inicial ?? '' }}</td>
                    <td>{{ $data->extension_cadera_izquierdo_inicial ?? '' }}</td>
                    <td>{{ $data->extension_cadera_derecho_final ?? '' }}</td>
                    <td>{{ $data->extension_cadera_izquierdo_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Extensión de rodilla</td>
                    <td>{{ $data->extension_rodilla_derecho_inicial ?? '' }}</td>
                    <td>{{ $data->extension_rodilla_izquierdo_inicial ?? '' }}</td>
                    <td>{{ $data->extension_rodilla_derecho_final ?? '' }}</td>
                    <td>{{ $data->extension_rodilla_izquierdo_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Dorsiflexión</td>
                    <td>{{ $data->dorsiflexion_derecho_inicial ?? '' }}</td>
                    <td>{{ $data->dorsiflexion_izquierdo_inicial ?? '' }}</td>
                    <td>{{ $data->dorsiflexion_derecho_final ?? '' }}</td>
                    <td>{{ $data->dorsiflexion_izquierdo_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-right" colspan="1"><strong>Puntaje final:</strong></td>
                    <td colspan="2" class="text-center">{{ $data->puntaje_final_mrc_inicial ?? '' }}</td>
                    <td colspan="2" class="text-center">{{ $data->puntaje_final_mrc_final ?? '' }}</td>
                </tr>
            </tbody>
        </table>

    </main>

    <!-- SALTO DE PÁGINA -->
    <div class="page-break"></div>

    <!-- PÁGINA 2: PRUEBA DE BALANCE -->
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <img src="img/logo.png" alt="cercap logo" style="height: 60px; display: block; margin: 0 auto 5px;" class="">
            <p class="f-bold f-15 text-center mb-0 mt-0">Cualidades Físicas No Aeróbicas (continuación)</p>
            <p class="f-bold mb-0 f-10">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre }}</span> - Registro: {{ $paciente->registro }}</p>
        </div>
    </header>

    <main class="mt-2">
        <!-- Prueba de Balance -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">PRUEBA DE BALANCE</h2>
            <div class="linea"></div>
        </div>

        <table class="tabla m-t-0">
            <thead>
                <tr>
                    <th rowspan="3" width="15%">Bipedal</th>
                    <th colspan="4" class="bck-blue">Prueba Inicial</th>
                    <th colspan="4" class="bck-red">Prueba Final</th>
                </tr>
                <tr>
                    <th colspan="2" class="bck-blue">Derecha</th>
                    <th colspan="2" class="bck-blue">Izquierda</th>
                    <th colspan="2" class="bck-red">Derecha</th>
                    <th colspan="2" class="bck-red">Izquierda</th>
                </tr>
                <tr>
                    <th class="bck-blue">OA</th>
                    <th class="bck-blue">OC</th>
                    <th class="bck-blue">OA</th>
                    <th class="bck-blue">OC</th>
                    <th class="bck-red">OA</th>
                    <th class="bck-red">OC</th>
                    <th class="bck-red">OA</th>
                    <th class="bck-red">OC</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="f-bold">Semitandem</td>
                    <td>{{ $data->balance_semitandem_derecha_oa_inicial ?? '' }}</td>
                    <td>{{ $data->balance_semitandem_derecha_oc_inicial ?? '' }}</td>
                    <td>{{ $data->balance_semitandem_izquierda_oa_inicial ?? '' }}</td>
                    <td>{{ $data->balance_semitandem_izquierda_oc_inicial ?? '' }}</td>
                    <td>{{ $data->balance_semitandem_derecha_oa_final ?? '' }}</td>
                    <td>{{ $data->balance_semitandem_derecha_oc_final ?? '' }}</td>
                    <td>{{ $data->balance_semitandem_izquierda_oa_final ?? '' }}</td>
                    <td>{{ $data->balance_semitandem_izquierda_oc_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Tandem</td>
                    <td>{{ $data->balance_tandem_derecha_oa_inicial ?? '' }}</td>
                    <td>{{ $data->balance_tandem_derecha_oc_inicial ?? '' }}</td>
                    <td>{{ $data->balance_tandem_izquierda_oa_inicial ?? '' }}</td>
                    <td>{{ $data->balance_tandem_izquierda_oc_inicial ?? '' }}</td>
                    <td>{{ $data->balance_tandem_derecha_oa_final ?? '' }}</td>
                    <td>{{ $data->balance_tandem_derecha_oc_final ?? '' }}</td>
                    <td>{{ $data->balance_tandem_izquierda_oa_final ?? '' }}</td>
                    <td>{{ $data->balance_tandem_izquierda_oc_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Monopedal</td>
                    <td>{{ $data->balance_monopedal_derecha_oa_inicial ?? '' }}</td>
                    <td>{{ $data->balance_monopedal_derecha_oc_inicial ?? '' }}</td>
                    <td>{{ $data->balance_monopedal_izquierda_oa_inicial ?? '' }}</td>
                    <td>{{ $data->balance_monopedal_izquierda_oc_inicial ?? '' }}</td>
                    <td>{{ $data->balance_monopedal_derecha_oa_final ?? '' }}</td>
                    <td>{{ $data->balance_monopedal_derecha_oc_final ?? '' }}</td>
                    <td>{{ $data->balance_monopedal_izquierda_oa_final ?? '' }}</td>
                    <td>{{ $data->balance_monopedal_izquierda_oc_final ?? '' }}</td>
                </tr>
            </tbody>
        </table>
        <p class="f-9 mt-0 mb-2"><strong>OA:</strong> Ojos abiertos. <strong>OC:</strong> Ojos cerrados.</p>

        <!-- Tests Físicos -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">TESTS FÍSICOS</h2>
            <div class="linea"></div>
        </div>

        <!-- Sit to Stand Test -->
        <p class="f-bold f-10 m-t-0 mb-0" style="margin-top: 1rem;">SIT TO STAND TEST 30" O TIEMPO EMPLEADO EN REALIZAR 5 SENTADILLAS.</p>
        <table class="tabla">
            <thead>
                <tr>
                    <th width="20%">Sentadillas realizadas en 30"</th>
                    <th class="bck-blue" colspan="2">Prueba Inicial</th>
                    <th class="bck-red" colspan="2">Prueba Final</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td colspan="2">{{ $data->sentadillas_realizadas_inicial ?? '' }}</td>
                    <td colspan="2">{{ $data->sentadillas_realizadas_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Tiempo empleado en realizar 5 sentadillas</td>
                    <td colspan="2">{{ $data->tiempo_5_sentadillas_inicial ?? '' }}</td>
                    <td colspan="2">{{ $data->tiempo_5_sentadillas_final ?? '' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Test de Lagartijas -->
        <p class="f-bold f-10 mb-0" style="margin-top: 1.5rem;">TEST DE LAGARTIJAS:</p>
        <table class="tabla">
            <thead>
                <tr>
                    <th width="40%">a) Lagartija en respaldo de silla</th>
                    <th class="bck-blue" colspan="3">Prueba Inicial</th>
                    <th class="bck-red" colspan="3">Prueba Final</th>
                </tr>
                <tr>
                    <th>b) Media lagartija</th>
                    <th class="bck-blue">a</th>
                    <th class="bck-blue">b</th>
                    <th class="bck-blue">c</th>
                    <th class="bck-red">a</th>
                    <th class="bck-red">b</th>
                    <th class="bck-red">c</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>c) Lagartija</td>
                    <td>{{ $data->lagartijas_realizadas_a_inicial ?? '' }}</td>
                    <td>{{ $data->lagartijas_realizadas_b_inicial ?? '' }}</td>
                    <td>{{ $data->lagartijas_realizadas_c_inicial ?? '' }}</td>
                    <td>{{ $data->lagartijas_realizadas_a_final ?? '' }}</td>
                    <td>{{ $data->lagartijas_realizadas_b_final ?? '' }}</td>
                    <td>{{ $data->lagartijas_realizadas_c_final ?? '' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Test de Abdominales -->
        <p class="f-bold f-10 mb-0" style="margin-top: 1.5rem;">TEST DE ABDOMINALES:</p>
        <table class="tabla">
            <thead>
                <tr>
                    <th width="40%">Abdominales realizadas</th>
                    <th class="bck-blue">Prueba Inicial</th>
                    <th class="bck-red">Prueba Final</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>{{ $data->abdominales_realizadas_inicial ?? '' }}</td>
                    <td>{{ $data->abdominales_realizadas_final ?? '' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Sit and Reach -->
        <p class="f-bold f-10 mb-0" style="margin-top: 1.5rem;">SIT AND REACH:</p>
        <table class="tabla">
            <thead>
                <tr>
                    <th rowspan="2" width="20%">Resultado</th>
                    <th colspan="3" class="bck-blue">Prueba Inicial</th>
                    <th colspan="3" class="bck-red">Prueba Final</th>
                </tr>
                <tr>
                    <th class="bck-blue">Toma 1</th>
                    <th class="bck-blue">Toma 2</th>
                    <th class="bck-blue">Promedio</th>
                    <th class="bck-red">Toma 1</th>
                    <th class="bck-red">Toma 2</th>
                    <th class="bck-red">Promedio</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>{{ $data->sit_reach_toma1_inicial ?? '' }}</td>
                    <td>{{ $data->sit_reach_toma2_inicial ?? '' }}</td>
                    <td>{{ $data->sit_reach_promedio_inicial ?? '' }}</td>
                    <td>{{ $data->sit_reach_toma1_final ?? '' }}</td>
                    <td>{{ $data->sit_reach_toma2_final ?? '' }}</td>
                    <td>{{ $data->sit_reach_promedio_final ?? '' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Back Scratch -->
        <p class="f-bold f-10 mb-0" style="margin-top: 1.5rem;">BACK SCRATCH O PRUEBA DE RASCADO:</p>
        <table class="tabla">
            <thead>
                <tr>
                    <th width="20%"></th>
                    <th class="bck-blue">Prueba Inicial</th>
                    <th class="bck-red">Prueba Final</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="f-bold">Lado derecho</td>
                    <td>{{ $data->back_scratch_lado_derecho_inicial ?? '' }}</td>
                    <td>{{ $data->back_scratch_lado_derecho_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Lado izquierdo</td>
                    <td>{{ $data->back_scratch_lado_izquierdo_inicial ?? '' }}</td>
                    <td>{{ $data->back_scratch_lado_izquierdo_final ?? '' }}</td>
                </tr>
            </tbody>
        </table>

    </main>

    <!-- SALTO DE PÁGINA -->
    <div class="page-break"></div>

    <!-- PÁGINA 3: ESCALA DE TINETTI -->
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <img src="img/logo.png" alt="cercap logo" style="height: 60px; display: block; margin: 0 auto 5px;" class="">
            <p class="f-bold f-15 text-center mb-0 mt-0">Cualidades Físicas No Aeróbicas (continuación)</p>
        </div>
    </header>

    <main class="mt-2">
        <!-- Escala de Tinetti -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">ESCALA DE TINETTI</h2>
            <div class="linea"></div>
        </div>

        <p class="f-9 m-t-0"><strong>Evaluación de la marcha (observar al paciente)</strong></p>
        <p class="f-9 mt-0"><strong>Instrucciones:</strong> El paciente permanece de pie con el examinador, camina por el pasillo o por la habitación (unos 8 metros/"paso normal") luego regresa a "paso ligero pero seguro".</p>

        <table class="tabla">
            <thead>
                <tr>
                    <th width="70%">ÍTEM</th>
                    <th class="bck-blue">Inicial</th>
                    <th class="bck-red">Final</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">1. Iniciación de la marcha (inmediatamente después de decir que "camine")</td>
                </tr>
                <tr>
                    <td>Alguna vacilación o múltiples intentos para empezar</td>
                    <td>{{ $data->tinetti_iniciacion_vacilaciones_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_iniciacion_vacilaciones_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>No vacila</td>
                    <td>{{ $data->tinetti_iniciacion_no_vacila_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_iniciacion_no_vacila_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">2. Longitud y altura de paso</td>
                </tr>
                <tr>
                    <td>a. El pie derecho no sobrepasa completamente del izquierdo en el paso</td>
                    <td>{{ $data->tinetti_long_der_no_sobrepasa_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_long_der_no_sobrepasa_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>El pie derecho sobrepasa completamente del izquierdo</td>
                    <td>{{ $data->tinetti_long_der_sobrepasa_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_long_der_sobrepasa_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>b. El pie izquierdo no sobrepasa completamente del derecho en el paso</td>
                    <td>{{ $data->tinetti_long_izq_no_sobrepasa_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_long_izq_no_sobrepasa_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>El pie izquierdo sobrepasa completamente del derecho</td>
                    <td>{{ $data->tinetti_long_izq_sobrepasa_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_long_izq_sobrepasa_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>c. El pie derecho no se levanta completamente del suelo</td>
                    <td>{{ $data->tinetti_long_der_no_separa_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_long_der_no_separa_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>El pie derecho se levanta completamente</td>
                    <td>{{ $data->tinetti_long_der_separa_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_long_der_separa_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>d. El pie izquierdo no se levanta completamente del suelo</td>
                    <td>{{ $data->tinetti_long_izq_no_separa_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_long_izq_no_separa_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>El pie izquierdo se levanta completamente</td>
                    <td>{{ $data->tinetti_long_izq_separa_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_long_izq_separa_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">3. Simetría del paso</td>
                </tr>
                <tr>
                    <td>La longitud del paso con los pies derecho e izquierdo no es igual</td>
                    <td>{{ $data->tinetti_simetria_no_igual_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_simetria_no_igual_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>La longitud parece igual</td>
                    <td>{{ $data->tinetti_simetria_igual_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_simetria_igual_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">4. Fluidez del paso</td>
                </tr>
                <tr>
                    <td>Paradas entre los pasos</td>
                    <td>{{ $data->tinetti_fluidez_paradas_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_fluidez_paradas_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Los pasos parecen continuos</td>
                    <td>{{ $data->tinetti_fluidez_continuos_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_fluidez_continuos_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">5. Trayectoria (observar el trazo que realiza uno de los pies durante 3 metros)</td>
                </tr>
                <tr>
                    <td>Desviación grave de la trayectoria</td>
                    <td>{{ $data->tinetti_trayectoria_grave_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_trayectoria_grave_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Leve/moderada desviación o utiliza ayudas para mantener la trayectoria</td>
                    <td>{{ $data->tinetti_trayectoria_leve_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_trayectoria_leve_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Sin desviación o ayudas</td>
                    <td>{{ $data->tinetti_trayectoria_sin_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_trayectoria_sin_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">6. Tronco</td>
                </tr>
                <tr>
                    <td>Balanceo marcado o utiliza ayudas</td>
                    <td>{{ $data->tinetti_tronco_balanceo_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_tronco_balanceo_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>No balanceo, pero hay flexión de rodillas o de espalda o extensión de brazos</td>
                    <td>{{ $data->tinetti_tronco_flexiona_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_tronco_flexiona_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>No balanceo, no flexión, ni utiliza ayudas</td>
                    <td>{{ $data->tinetti_tronco_no_balancea_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_tronco_no_balancea_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">7. Postura al caminar</td>
                </tr>
                <tr>
                    <td>Los talones separados</td>
                    <td>{{ $data->tinetti_postura_separados_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_postura_separados_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Los talones casi se tocan mientras camina</td>
                    <td>{{ $data->tinetti_postura_juntos_inicial ?? '0' }}</td>
                    <td>{{ $data->tinetti_postura_juntos_final ?? '0' }}</td>
                </tr>
                <tr class="bck-gray">
                    <td class="f-bold">TOTAL DE PUNTOS (VALORACIÓN DE MARCHA 12)</td>
                    <td class="f-bold">{{ $data->tinetti_total_marcha_inicial ?? '0' }}</td>
                    <td class="f-bold">{{ $data->tinetti_total_marcha_final ?? '0' }}</td>
                </tr>
            </tbody>
        </table>

        <p class="f-9 mt-2"><strong>Puntaje de escala de Tinetti:</strong></p>
        <p class="f-9 mt-0 mb-0">• <em>Alto riesgo de caídas:</em> Puntuación total menor o igual a 18.</p>
        <p class="f-9 mt-0 mb-0">• <em>Riesgo moderado de caídas:</em> Puntuación entre 19 y 24.</p>
        <p class="f-9 mt-0 mb-0">• <em>Bajo riesgo de caídas:</em> Puntuación de 25 o más.</p>

    </main>

    <!-- SALTO DE PÁGINA -->
    <div class="page-break"></div>

    <!-- PÁGINA 4: EVALUACIÓN DE EQUILIBRIO -->
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <img src="img/logo.png" alt="cercap logo" style="height: 60px; display: block; margin: 0 auto 5px;" class="">
            <p class="f-bold f-15 text-center mb-0 mt-0">Cualidades Físicas No Aeróbicas (continuación)</p>
        </div>
    </header>

    <main class="mt-2">
        <!-- Evaluación de Equilibrio -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">EVALUACIÓN DE EQUILIBRIO</h2>
            <div class="linea"></div>
        </div>

        <p class="f-9 m-t-0"><strong>Instrucciones:</strong> El paciente está sentado en una silla dura sin apoyabrazos. Se realizan las siguientes maniobras:</p>

        <table class="tabla">
            <thead>
                <tr>
                    <th width="70%">ÍTEM</th>
                    <th class="bck-blue">Inicial</th>
                    <th class="bck-red">Final</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">1. Equilibrio sentado</td>
                </tr>
                <tr>
                    <td>Se inclina o se desliza en la silla</td>
                    <td>{{ $data->equilibrio_sentado_inclina_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_sentado_inclina_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Se mantiene seguro</td>
                    <td>{{ $data->equilibrio_sentado_seguro_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_sentado_seguro_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">2. Levantarse</td>
                </tr>
                <tr>
                    <td>Incapaz sin ayuda</td>
                    <td>{{ $data->equilibrio_levantarse_imposible_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_levantarse_imposible_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Capaz pero usa los brazos para ayudarse</td>
                    <td>{{ $data->equilibrio_levantarse_brazos_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_levantarse_brazos_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Capaz sin usar los brazos</td>
                    <td>{{ $data->equilibrio_levantarse_solo_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_levantarse_solo_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">3. Intentos para levantarse</td>
                </tr>
                <tr>
                    <td>Incapaz sin ayuda</td>
                    <td>{{ $data->equilibrio_intentos_incapaz_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_intentos_incapaz_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Capaz pero necesita más de un intento</td>
                    <td>{{ $data->equilibrio_intentos_mas_uno_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_intentos_mas_uno_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Capaz de levantarse de un solo intento</td>
                    <td>{{ $data->equilibrio_intentos_un_solo_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_intentos_un_solo_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">4. Equilibrio en bipedestación inmediata (los primeros 5 segundos)</td>
                </tr>
                <tr>
                    <td>Inestable (se balancea, mueve los pies, marcado balanceo del tronco)</td>
                    <td>{{ $data->equilibrio_bipe_inm_inestable_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_bipe_inm_inestable_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Estable, pero usa andador, bastón y se agarra a otro objeto para mantenerse</td>
                    <td>{{ $data->equilibrio_bipe_inm_andador_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_bipe_inm_andador_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Estable sin andador, bastón u otro soporte</td>
                    <td>{{ $data->equilibrio_bipe_inm_estable_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_bipe_inm_estable_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">5. Equilibrio en bipedestación</td>
                </tr>
                <tr>
                    <td>Inestable</td>
                    <td>{{ $data->equilibrio_bipe_inestable_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_bipe_inestable_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Estable, pero con apoyo amplio (talones separados más de 10 cm) o usa bastón, andador u otro soporte</td>
                    <td>{{ $data->equilibrio_bipe_apoyo_amplio_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_bipe_apoyo_amplio_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Apoyo estrecho sin ningún soporte</td>
                    <td>{{ $data->equilibrio_bipe_apoyo_estrecho_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_bipe_apoyo_estrecho_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">6. Empujar (el paciente en bipedestación con el tronco erecto y los pies tan juntos como sea posible) el examinador empuja suavemente en el esternón del paciente con la palma 3 veces</td>
                </tr>
                <tr>
                    <td>Empieza a caerse</td>
                    <td>{{ $data->equilibrio_empujar_caerse_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_empujar_caerse_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Se tambalea, se agarra, pero se mantiene</td>
                    <td>{{ $data->equilibrio_empujar_tambalea_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_empujar_tambalea_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Estable</td>
                    <td>{{ $data->equilibrio_empujar_estable_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_empujar_estable_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">7. Ojos cerrados (en la posición 6)</td>
                </tr>
                <tr>
                    <td>Inestable</td>
                    <td>{{ $data->equilibrio_ojos_inestable_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_ojos_inestable_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Estable</td>
                    <td>{{ $data->equilibrio_ojos_estable_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_ojos_estable_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">8. Vuelta de 360 grados</td>
                </tr>
                <tr>
                    <td>Pasos discontinuos</td>
                    <td>{{ $data->equilibrio_vuelta_discontinuos_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_vuelta_discontinuos_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Pasos continuos</td>
                    <td>{{ $data->equilibrio_vuelta_continuos_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_vuelta_continuos_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Inestable (se agarra, se tambalea)</td>
                    <td>{{ $data->equilibrio_vuelta_inestable_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_vuelta_inestable_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Estable</td>
                    <td>{{ $data->equilibrio_vuelta_estable_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_vuelta_estable_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="f-bold bck-gray">9. Sentarse</td>
                </tr>
                <tr>
                    <td>Inseguro, calcula mal la distancia, cae en la silla</td>
                    <td>{{ $data->equilibrio_sentarse_inseguro_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_sentarse_inseguro_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Usa los brazos (no se sienta en forma suave)</td>
                    <td>{{ $data->equilibrio_sentarse_brazos_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_sentarse_brazos_final ?? '0' }}</td>
                </tr>
                <tr>
                    <td>Movimiento suave y seguro</td>
                    <td>{{ $data->equilibrio_sentarse_seguro_inicial ?? '0' }}</td>
                    <td>{{ $data->equilibrio_sentarse_seguro_final ?? '0' }}</td>
                </tr>
                <tr class="bck-gray">
                    <td class="f-bold">TOTAL DE PUNTOS (EVALUACIÓN DE EQUILIBRIO 16)</td>
                    <td class="f-bold">{{ $data->eval_eq_total_equilibrio_inicial ?? '0' }}</td>
                    <td class="f-bold">{{ $data->eval_eq_total_equilibrio_final ?? '0' }}</td>
                </tr>
                <tr class="bck-gray">
                    <td class="f-bold">TOTAL DE PUNTOS DE LA ESCALA</td>
                    <td class="f-bold">{{ $data->eval_eq_total_puntos_escala_inicial ?? '0' }}</td>
                    <td class="f-bold">{{ $data->eval_eq_total_puntos_escala_final ?? '0' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Observaciones -->
        <div class="contenedor mt-2">
            <h2 class="h8 titulo">OBSERVACIONES</h2>
            <div class="linea"></div>
        </div>
        <p class="f-9 m-t-0">{{ $data->observaciones ?? 'N/A' }}</p>
    </main>
</body>
</html>
