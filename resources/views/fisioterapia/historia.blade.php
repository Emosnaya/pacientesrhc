<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Historia Clínica de Fisioterapia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
        }
        .paciente {
            font-size: 11px;
        }
        .f-bold {
            font-weight: bold;
        }
        .f-normal {
            font-weight: normal;
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
        .text-lft {
            text-align: left;
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
            margin-bottom: 0;
            margin-top: 1.5rem;
        }
        .titulo {
            display: inline-block;
            position: relative;
            z-index: 1;
            padding-right: 0.5rem;
            font-size: 13px;
            font-weight: bold;
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
        .bck-gray {
            background-color: #DDDEE1;
        }
        .tabla {
            font-size: 10px;
            margin-bottom: 0.8rem;
            width: 100%;
        }
        .tabla td {
            padding: 5px 8px;
        }
        .border-t {
            border: 1px solid black;
        }
        .signature {
            margin-top: 2rem;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 0 auto 5px;
            margin-top: 2rem;
        }
        .signature-text {
            font-size: 9px;
        }
        p.f-10 {
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <p class="f-bold f-15 text-center mb-0 mt-0">Historia Clínica de Fisioterapia</p>
            <img src="img/logo.png" alt="cercap logo" style="height: 90px" class="">
            <div class="medio">
                <p class="text-sm texto-izquierda mb-0 f-bold">Fecha: {{ date('d/m/Y', strtotime($data->fecha)) }}</p>
                <span class="ml-5 text-right texto-derecha f-bold">Registro: {{ $paciente->registro }}</span>
            </div>
            <br>
            <p class="f-bold mb-0">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre }}</span>
            <span class="f-bold ml-2">Edad: <span class="f-normal">{{ $paciente->edad }} años</span></span>
            <span class="f-bold ml-2">Género: <span class="f-normal">{{ $paciente->genero == 1 ? 'Masculino' : 'Femenino' }}</span></span>
            <span class="f-bold ml-2">Ocupación: <span class="f-normal">{{ $data->ocupacion ?? 'N/A' }}</span></span></p>
            <p class="mb-0 mt-0 f-bold">Hora: <span class="f-normal">{{ date('H:i', strtotime($data->hora)) }}</span></p>
        </div>
    </header>

    <main class="mt-0">
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Motivo de Consulta y Padecimiento</h2>
            <div class="linea"></div>
        </div>
        <p class="f-bold m-t-0 f-10 mb-1">Motivo de consulta: <span class="f-normal">{{ $data->motivo_consulta ?? 'N/A' }}</span></p>
        <p class="f-bold f-10 mb-1">Padecimiento actual: <span class="f-normal">{{ $data->padecimiento_actual ?? 'N/A' }}</span></p>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Antecedentes</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-bold text-lft" width="30%">Heredofamiliares:</td>
                    <td class="f-normal text-lft">{{ $data->antecedentes_heredofamiliares ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Personales patológicos:</td>
                    <td class="f-normal text-lft">{{ $data->antecedentes_personales_patologicos ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Personales no patológicos:</td>
                    <td class="f-normal text-lft">{{ $data->antecedentes_personales_no_patologicos ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Quirúrgicos/Traumáticos:</td>
                    <td class="f-normal text-lft">{{ $data->antecedentes_quirurgicos_traumaticos ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Exploración Física</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                    <td class="f-bold text-lft" width="30%">Signos vitales:</td>
                    <td class="f-normal text-lft">{{ $data->signos_vitales ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Inspección:</td>
                    <td class="f-normal text-lft">{{ $data->inspeccion ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Palpación:</td>
                    <td class="f-normal text-lft">{{ $data->palpacion ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Rango de movimiento:</td>
                    <td class="f-normal text-lft">{{ $data->rango_movimiento ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Fuerza muscular:</td>
                    <td class="f-normal text-lft">{{ $data->fuerza_muscular ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Pruebas especiales:</td>
                    <td class="f-normal text-lft">{{ $data->pruebas_especiales ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Impresión Diagnóstica</h2>
            <div class="linea"></div>
        </div>
        <p class="f-bold m-t-0 f-10 mb-1">Diagnóstico médico: <span class="f-normal">{{ $data->diagnostico_medico ?? 'N/A' }}</span></p>
        <p class="f-bold f-10 mb-1">Diagnóstico fisioterapéutico: <span class="f-normal">{{ $data->diagnostico_fisioterapeutico ?? 'N/A' }}</span></p>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Plan Terapéutico</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-bold text-lft" width="30%">Objetivos del tratamiento:</td>
                    <td class="f-normal text-lft">{{ $data->objetivos_tratamiento ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Modalidades terapéuticas:</td>
                    <td class="f-normal text-lft">{{ $data->modalidades_terapeuticas ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Educación al paciente:</td>
                    <td class="f-normal text-lft">{{ $data->educacion_paciente ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Pronóstico:</td>
                    <td class="f-normal text-lft">{{ $data->pronostico ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature">
            @if($firmaBase64)
                <img src="{{ $firmaBase64 }}" alt="Firma" style="max-width: 150px;">
            @endif
            <div class="signature-line"></div>
            <p class="signature-text"><strong>{{ $user->nombre }} {{ $user->apellidoPat }}</strong></p>
            <p class="signature-text">Fisioterapeuta</p>
        </div>
    </main>
</body>
</html>
