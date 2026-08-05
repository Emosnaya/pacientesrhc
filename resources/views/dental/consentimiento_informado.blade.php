<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $plantilla['titulo'] }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}');
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.45;
            margin: 18px 22px;
        }
        .header {
            width: 100%;
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            border-radius: 8px;
            margin-bottom: 12px;
            padding: 10px 12px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; border: none; padding: 0; }
        .header-title { font-size: 14px; font-weight: 700; color: #fff; }
        .header-sub { font-size: 9px; color: #cbd5e1; margin-top: 2px; }
        .badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        h2 {
            font-size: 11px;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
            margin: 12px 0 6px;
        }
        p { margin-bottom: 6px; text-align: justify; }
        ul { margin: 4px 0 8px 16px; }
        li { margin-bottom: 3px; }
        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .meta td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .meta tr:last-child td { border-bottom: none; }
        .label { width: 28%; color: #64748b; font-size: 9px; }
        .value { font-weight: 600; }
        .box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 8px;
            background: #fff;
        }
        .decl {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 8px 10px;
            margin-top: 10px;
        }
        .firmas {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        .firmas td {
            width: 50%;
            vertical-align: top;
            padding: 8px 12px;
            text-align: center;
        }
        .firma-img {
            max-height: 70px;
            max-width: 220px;
            margin: 6px auto;
            display: block;
        }
        .linea {
            border-top: 1px solid #94a3b8;
            margin-top: 8px;
            padding-top: 4px;
            font-size: 9px;
            color: #475569;
        }
        .foot {
            margin-top: 14px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
        }
        .note {
            font-size: 9px;
            color: #64748b;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:58px;">
                    @if(!empty($clinicaLogo))
                        <div style="width:48px;height:48px;background:#fff;border-radius:6px;padding:4px;text-align:center;">
                            <img src="{{ $clinicaLogo }}" style="max-height:40px;max-width:40px;" alt="Logo">
                        </div>
                    @endif
                </td>
                <td>
                    <div class="header-title">{{ $clinica->nombre ?? 'Clínica dental' }}</div>
                    <div class="header-sub">Carta de consentimiento informado odontológico</div>
                    @if(!empty($clinica->direccion))
                        <div class="header-sub">{{ $clinica->direccion }}</div>
                    @endif
                </td>
                <td style="text-align:right;width:130px;">
                    <span class="badge">NOM-013 / NOM-004</span>
                    <div class="header-sub" style="margin-top:6px;">{{ $fecha->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <h2 style="margin-top:0;">{{ $plantilla['titulo'] }}</h2>

    <table class="meta">
        <tr>
            <td class="label">Paciente</td>
            <td class="value">{{ $nombrePaciente }}</td>
        </tr>
        <tr>
            <td class="label">Estomatólogo / Odontólogo</td>
            <td class="value">
                {{ $user->nombre_con_titulo ?? trim(($user->nombre ?? '').' '.($user->apellidoPat ?? '')) }}
                @if(!empty($user->cedula))
                    · Céd. {{ $user->cedula }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Institución</td>
            <td class="value">{{ $clinica->nombre ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Lugar y fecha</td>
            <td class="value">{{ $lugar }} · {{ $fecha->format('d/m/Y') }}</td>
        </tr>
        @if(!empty($diagnostico))
        <tr>
            <td class="label">Diagnóstico / indicación</td>
            <td class="value">{{ $diagnostico }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Procedimiento autorizado</td>
            <td class="value">{{ $procedimiento }}</td>
        </tr>
    </table>

    <div class="box">
        <p>
            Yo, <strong>{{ $nombreFirmante }}</strong>
            @if($esTutor)
                (tutor / representante legal@if(!empty($parentescoFirmante)), parentesco: {{ $parentescoFirmante }}@endif)
            @endif
            , manifiesto que he sido informado(a) de manera clara y en lenguaje comprensible sobre el estado de salud bucal, el procedimiento propuesto, sus objetivos, riesgos, beneficios, alternativas y las consecuencias de no realizarlo, conforme a la normativa mexicana aplicable al expediente clínico y a la atención estomatológica.
        </p>
    </div>

    <h2>1. Descripción e objetivos</h2>
    <p>{{ $plantilla['objetivos'] }}</p>
    <p><strong>Procedimiento específico en este caso:</strong> {{ $procedimiento }}</p>

    <h2>2. Beneficios esperados</h2>
    <p>{{ $plantilla['beneficios'] }}</p>

    <h2>3. Molestias y riesgos más frecuentes o relevantes</h2>
    <ul>
        @foreach($plantilla['riesgos'] as $riesgo)
            <li>{{ $riesgo }}</li>
        @endforeach
    </ul>
    <p class="note">Estos riesgos no son exhaustivos; pueden presentarse complicaciones no previstas propias de cada paciente.</p>

    <h2>4. Alternativas factibles</h2>
    <p>{{ $plantilla['alternativas'] }}</p>

    <h2>5. Curso espontáneo sin tratamiento</h2>
    <p>{{ $plantilla['sin_tratamiento'] }}</p>

    @if(!empty($notas_adicionales))
        <h2>6. Información adicional del caso</h2>
        <p>{{ $notas_adicionales }}</p>
    @endif

    <div class="decl">
        <p><strong>Declaración y autorización</strong></p>
        <p>
            Declaro que he tenido oportunidad de formular preguntas y que éstas me fueron respondidas.
            Comprendo que el consentimiento es <strong>libre, voluntario y revocable</strong> mientras no inicie el procedimiento.
            Autorizo al odontólogo / estomatólogo indicado y al personal auxiliar bajo su supervisión a realizar el procedimiento descrito,
            así como las medidas necesarias ante una urgencia relacionada durante la atención.
            Me comprometo a seguir las indicaciones pre y postoperatorias y a acudir a los controles sugeridos.
        </p>
    </div>

    <table class="firmas">
        <tr>
            <td>
                @if(!empty($firmaPaciente))
                    <img class="firma-img" src="{{ $firmaPaciente }}" alt="Firma paciente">
                @endif
                <div class="linea">
                    <strong>{{ $nombreFirmante }}</strong><br>
                    Firma del {{ $esTutor ? 'tutor / representante' : 'paciente' }}
                </div>
            </td>
            <td>
                @if(!empty($firmaMedico))
                    <img class="firma-img" src="{{ $firmaMedico }}" alt="Firma odontólogo">
                @else
                    <div style="height:70px;"></div>
                @endif
                <div class="linea">
                    <strong>{{ $user->nombre_con_titulo ?? trim(($user->nombre ?? '').' '.($user->apellidoPat ?? '')) }}</strong><br>
                    Firma del odontólogo / estomatólogo
                    @if(!empty($user->cedula))
                        <br>Céd. {{ $user->cedula }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <p class="foot">
        Documento generado digitalmente en LynkaMed · Conservar en el expediente clínico ·
        Basado en elementos mínimos de consentimiento informado (NOM-004-SSA3-2012 y NOM-013-SSA2-2015).
        Este formato no sustituye el juicio clínico ni la explicación verbal del profesional.
    </p>
</body>
</html>
