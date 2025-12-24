<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Nota de Evolución - Fisioterapia</title>
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
        .eva-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 8px;
            margin: 10px 0;
            text-align: center;
        }
            margin: 5px 0;
            text-align: center;
        }
        .eva-value {
            font-size: 16px;
            font-weight: bold;
            color: #856404;
        }
    </style>
</head>
<body>
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <p class="f-bold f-15 text-center mb-0 mt-0">Nota de Evolución - Fisioterapia</p>
            <img src="img/logo.png" alt="cercap logo" style="height: 90px" class="">
            <div class="medio">
                <p class="text-sm texto-izquierda mb-0 f-bold">Fecha: {{ date('d/m/Y', strtotime($data->fecha)) }}</p>
                <span class="ml-5 text-right texto-derecha f-bold">Registro: {{ $paciente->registro }}</span>
            </div>
            <br>
            <p class="f-bold mb-0">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre }}</span>
            <span class="f-bold ml-2">Edad: <span class="f-normal">{{ $paciente->edad }} años</span></span>
            <span class="f-bold ml-2">Género: <span class="f-normal">{{ $paciente->genero == 1 ? 'Masculino' : 'Femenino' }}</span></span></p>
            <p class="mb-0 mt-0 f-bold">Hora: <span class="f-normal">{{ date('H:i', strtotime($data->hora)) }}</span>
            @if($data->numero_sesion)
            <span class="f-bold ml-2">Sesión No.: <span class="f-normal">{{ $data->numero_sesion }}</span></span>
            @endif
            </p>
        </div>
    </header>

    <main class="mt-0">
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Evaluación SOAP</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-bold text-lft" width="30%">Subjetivo (S):</td>
                    <td class="f-normal text-lft">{{ $data->subjetivo ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Objetivo (O):</td>
                    <td class="f-normal text-lft">{{ $data->objetivo ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        @if($data->eva_inicial || $data->eva_final)
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Escala EVA del Dolor (0-10)</h2>
            <div class="linea"></div>
        </div>
        <div class="eva-box m-t-0">
            @if($data->eva_inicial)
            <span class="f-bold">EVA Inicial: <span class="eva-value">{{ $data->eva_inicial }}</span></span>
            @endif
            @if($data->eva_inicial && $data->eva_final)
            <span class="f-bold ml-3">→</span>
            @endif
            @if($data->eva_final)
            <span class="f-bold ml-3">EVA Final: <span class="eva-value">{{ $data->eva_final }}</span></span>
            @endif
            @if($data->eva_inicial && $data->eva_final)
            <span class="f-bold ml-3">Variación: <span class="eva-value">{{ $data->eva_final - $data->eva_inicial > 0 ? '+' : '' }}{{ round($data->eva_final - $data->eva_inicial, 1) }}</span></span>
            @endif
        </div>
        @endif

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Tratamiento</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                    <td class="f-bold text-lft" width="30%">Tratamiento realizado:</td>
                    <td class="f-normal text-lft">{{ $data->tratamiento_realizado ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Respuesta al tratamiento:</td>
                    <td class="f-normal text-lft">{{ $data->respuesta_tratamiento ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Plan y Observaciones</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-bold text-lft" width="30%">Observaciones:</td>
                    <td class="f-normal text-lft">{{ $data->observaciones ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="f-bold text-lft">Plan para siguiente sesión:</td>
                    <td class="f-normal text-lft">{{ $data->plan_siguiente_sesion ?? 'N/A' }}</td>
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
