<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Nota de Alta - Fisioterapia</title>
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
            margin-top: 0.3rem;
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
        .motivo-alta {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 10px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            font-size: 12px;
        }
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            font-size: 12px;
        }
        .highlight-box {
            background-color: #e7f3ff;
            border: 2px solid #0066cc;
            padding: 10px;
            text-align: center;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <p class="f-bold f-15 text-center mb-0 mt-0">Nota de Alta - Fisioterapia</p>
            <img src="img/logo.png" alt="cercap logo" style="height: 90px" class="">
            <div class="medio">
                <p class="text-sm texto-izquierda mb-0 f-bold">Fecha de alta: {{ date('d/m/Y', strtotime($data->fecha)) }}</p>
                <span class="ml-5 text-right texto-derecha f-bold">Registro: {{ $paciente->registro }}</span>
            </div>
            <br>
            <p class="f-bold mb-0">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre }}</span>
            <span class="f-bold ml-2">Edad: <span class="f-normal">{{ $paciente->edad }} años</span></span>
            <span class="f-bold ml-2">Género: <span class="f-normal">{{ $paciente->genero == 1 ? 'Masculino' : 'Femenino' }}</span></span></p>
        </div>
    </header>

    @if($data->motivo_alta)
    <div class="motivo-alta">
        MOTIVO DE ALTA: {{ strtoupper($data->motivo_alta) }}
    </div>
    @endif

    <main class="mt-0">
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Periodo del Tratamiento</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-bold text-lft" width="30%">Fecha de inicio:</td>
                    <td class="f-normal text-lft">{{ $data->fecha_inicio_tratamiento ? date('d/m/Y', strtotime($data->fecha_inicio_tratamiento)) : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Fecha de fin:</td>
                    <td class="f-normal text-lft">{{ $data->fecha_fin_tratamiento ? date('d/m/Y', strtotime($data->fecha_fin_tratamiento)) : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Total de sesiones:</td>
                    <td class="f-normal text-lft">{{ $data->numero_sesiones_totales ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Resumen Clínico</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                    <td class="f-bold text-lft" width="30%">Resumen clínico:</td>
                    <td class="f-normal text-lft">{{ $data->resumen_clinico ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Evolución del tratamiento:</td>
                    <td class="f-normal text-lft">{{ $data->evolucion_tratamiento ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Estado Funcional</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-bold text-lft" width="30%">Estado funcional inicial:</td>
                    <td class="f-normal text-lft">{{ $data->estado_funcional_inicial ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Estado funcional final:</td>
                    <td class="f-normal text-lft">{{ $data->estado_funcional_final ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Objetivos Alcanzados</h2>
            <div class="linea"></div>
        </div>
        <p class="f-bold m-t-0 f-10 mb-1">{{ $data->objetivos_alcanzados ?? 'N/A' }}</p>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Recomendaciones</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                    <td class="f-bold text-lft" width="30%">Recomendaciones generales:</td>
                    <td class="f-normal text-lft">{{ $data->recomendaciones ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Plan de ejercicios en domicilio:</td>
                    <td class="f-normal text-lft">{{ $data->plan_ejercicios_domicilio ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        @if($data->observaciones_finales)
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Observaciones Finales</h2>
            <div class="linea"></div>
        </div>
        <p class="f-bold m-t-0 f-10 mb-1">{{ $data->observaciones_finales }}</p>
        @endif

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
