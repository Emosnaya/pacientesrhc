<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Nota de Seguimiento Pulmonar</title>
    <style>
        .logo-container { height: 60px; overflow: hidden; display: inline-block; }
        .logo-container img { height: 60px; width: auto; }
        body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.3; }
        .paciente { font-size: 11px; }
        .f-bold { font-weight: bold; }
        .f-normal { font-weight: normal; }
        .f-10 { font-size: 10px; }
        .f-15 { font-size: 14px; }
        .text-center { text-align: center; }
        .text-lft { text-align: left; }
        .medio { position: relative; }
        .texto-izquierda { text-align: left; position: absolute; left: 0; }
        .texto-derecha { text-align: right; position: absolute; right: 0; }
        .contenedor { position: relative; text-align: justify; margin-bottom: 0; margin-top: 1.5rem; }
        .titulo { display: inline-block; position: relative; z-index: 1; padding-right: 0.5rem; font-size: 13px; font-weight: bold; }
        .linea { position: absolute; left: 0; right: 0; top: 0.6rem; border-bottom: 2px solid black; z-index: 0; }
        .m-t-0 { margin-top: 0.3rem; }
        .bck-gray { background-color: #DDDEE1; }
        .tabla { font-size: 10px; margin-bottom: 0.8rem; width: 100%; }
        .tabla td { padding: 5px 8px; }
        .border-t { border: 1px solid black; }
        .signature { margin-top: 2rem; text-align: center; }
        .signature-line { border-top: 1px solid #000; width: 250px; margin: 0 auto 5px; margin-top: 2rem; }
        .signature-text { font-size: 9px; }
    </style>
</head>
<body>
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <p class="f-bold f-15 text-center mb-0 mt-0">Nota de Seguimiento Pulmonar</p>
            @if($clinicaLogo)
            <div class="logo-container"><img src="{{ $clinicaLogo }}" alt="logo clínica"></div>
            @endif
            <div class="medio">
                <p class="text-sm texto-izquierda mb-0 f-bold">Fecha: {{ $data->fecha_consulta ? $data->fecha_consulta->format('d/m/Y') : '' }}</p>
                <span class="ml-5 text-right texto-derecha f-bold">Hora: {{ $data->hora_consulta ? \Carbon\Carbon::parse($data->hora_consulta)->format('H:i') : '' }}</span>
            </div>
            <br>
            <p class="f-bold mb-0">Paciente: <span class="f-normal">{{ $paciente->apellidoPat ?? '' }} {{ $paciente->apellidoMat ?? '' }} {{ $paciente->nombre ?? '' }}</span>
            <span class="f-bold ml-2">Registro: <span class="f-normal">{{ $paciente->registro ?? '' }}</span></span>
            <span class="f-bold ml-2">Edad: <span class="f-normal">{{ $paciente->edad ?? '' }} años</span></span></p>
        </div>
    </header>

    <main class="mt-0">
        @if($data->ficha_identificacion)
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Ficha de identificación del paciente</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-normal text-lft">{{ $data->ficha_identificacion }}</td>
                </tr>
            </tbody>
        </table>
        @endif

        @if($data->diagnosticos)
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Diagnósticos</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-normal text-lft">{{ $data->diagnosticos }}</td>
                </tr>
            </tbody>
        </table>
        @endif

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">S - Subjetivo</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                    <td class="f-normal text-lft">{{ $data->s_subjetivo ?: '—' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">O - Objetivo</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-normal text-lft">{{ $data->o_objetivo ?: '—' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">A - Apreciación</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                    <td class="f-normal text-lft">{{ $data->a_apreciacion ?: '—' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="contenedor mt-1">
            <h2 class="h8 titulo">P - Plan</h2>
            <div class="linea"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                    <td class="f-normal text-lft">{{ $data->p_plan ?: '—' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature">
            @if(isset($firmaBase64) && $firmaBase64)
                <img src="{{ $firmaBase64 }}" alt="Firma" style="max-width: 150px;">
            @endif
            <div class="signature-line"></div>
            <p class="signature-text"><strong>{{ $user->nombre ?? '' }} {{ $user->apellidoPat ?? '' }}</strong></p>
            @if($user->cedula ?? null)
            <p class="signature-text">Cédula: {{ $user->cedula }}</p>
            @endif
        </div>
    </main>
</body>
</html>
