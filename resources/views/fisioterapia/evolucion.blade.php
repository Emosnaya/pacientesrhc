<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Nota de Evolución - Fisioterapia</title>
    <style>
        /* Estilo para el logo */
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
            <div class="logo-container"><img src="{{ $clinicaLogo }}" alt="logo clínica"></div>
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
        @if($data->diagnostico_fisioterapeutico)
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Diagnóstico Fisioterapéutico</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-normal text-lft">{{ $data->diagnostico_fisioterapeutico }}</td>
                </tr>
            </tbody>
        </table>
        @endif

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Evolución Clínica</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                @if($data->observaciones_subjetivas)
                <tr>
                    <td class="f-bold text-lft" width="30%">Observaciones Subjetivas:</td>
                    <td class="f-normal text-lft">{{ $data->observaciones_subjetivas }}</td>
                </tr>
                @endif
                @if($data->dolor_eva)
                <tr>
                    <td class="f-bold text-lft">Dolor EVA (0-10):</td>
                    <td class="f-normal text-lft"><span class="eva-value">{{ $data->dolor_eva }}</span></td>
                </tr>
                @endif
                @if($data->funcionalidad)
                <tr>
                    <td class="f-bold text-lft">Funcionalidad:</td>
                    <td class="f-normal text-lft">{{ $data->funcionalidad }}</td>
                </tr>
                @endif
                @if($data->observaciones_objetivas)
                <tr>
                    <td class="f-bold text-lft">Observaciones Objetivas:</td>
                    <td class="f-normal text-lft">{{ $data->observaciones_objetivas }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Tratamiento Realizado</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                @if($data->tecnicas_modalidades_aplicadas)
                <tr>
                    <td class="f-bold text-lft" width="30%">Técnicas y Modalidades:</td>
                    <td class="f-normal text-lft">{{ $data->tecnicas_modalidades_aplicadas }}</td>
                </tr>
                @endif
                @if($data->ejercicio_terapeutico)
                <tr>
                    <td class="f-bold text-lft">Ejercicio Terapéutico:</td>
                    <td class="f-normal text-lft">{{ $data->ejercicio_terapeutico }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Respuesta y Plan</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                @if($data->respuesta_tratamiento)
                <tr>
                    <td class="f-bold text-lft" width="30%">Respuesta al Tratamiento:</td>
                    <td class="f-normal text-lft">{{ $data->respuesta_tratamiento }}</td>
                </tr>
                @endif
                @if($data->plan)
                <tr>
                    <td class="f-bold text-lft" width="30%">Plan:</td>
                    <td class="f-normal text-lft">{{ $data->plan }}</td>
                </tr>
                @endif
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
