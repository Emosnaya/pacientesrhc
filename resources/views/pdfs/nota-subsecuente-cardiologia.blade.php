<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Subsecuente</title>
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
        table { border-collapse: collapse; }
        .header {
            width: 100%;
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 10px 14px;
        }
        .header-table { width: 100%; }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-cell { width: 58px; padding-right: 12px !important; }
        .header-logo {
            width: 46px; height: 46px;
            background: white; border-radius: 6px;
            padding: 5px; text-align: center;
        }
        .header-logo img { max-height: 36px; max-width: 36px; }
        .header-title { font-size: 16px; font-weight: 700; color: white; letter-spacing: 0.2px; }
        .header-subtitle { font-size: 9px; color: #cbd5e1; margin-top: 2px; }
        .header-meta-cell { text-align: right; width: 130px; }
        .header-badge {
            background: rgba(255,255,255,0.14);
            padding: 6px 10px; border-radius: 6px;
            display: inline-block; margin-bottom: 4px;
        }
        .header-badge-label { font-size: 7.5px; text-transform: uppercase; color: #94a3b8; }
        .header-badge-value { font-size: 12px; font-weight: 700; color: white; }
        .header-date { font-size: 9px; color: #cbd5e1; }
        .patient-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid {!! $clinica->color_principal ?? '#0A1628' !!};
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }
        .patient-name {
            font-size: 14px;
            font-weight: 700;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            margin-bottom: 6px;
        }
        .patient-table { width: 100%; }
        .patient-table td { padding: 3px 8px 3px 0; font-size: 9.5px; }
        .patient-label { color: #64748b; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.3px; }
        .patient-value { font-weight: 600; color: #334155; }
        .section {
            margin-bottom: 9px;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .section-title {
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            color: white;
            font-size: 9px;
            font-weight: 700;
            padding: 5px 12px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .section-body { padding: 10px 12px; background: #fff; }
        .section-subtitle {
            background: #f1f5f9;
            color: #475569;
            font-size: 8.5px;
            font-weight: 700;
            padding: 4px 8px;
            margin: 0 0 6px 0;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .text-block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 7px 9px;
            font-size: 9.5px;
            min-height: 22px;
            white-space: pre-wrap;
        }
        .full-label {
            font-size: 8.5px;
            color: #64748b;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .two-col { width: 50%; vertical-align: top; padding: 0 5px; }
        .vitals-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .vitals-table td, .vitals-table th {
            border: 1px solid #e2e8f0;
            padding: 5px 4px;
            text-align: center;
            font-size: 9px;
        }
        .vitals-table th {
            background: #f1f5f9;
            color: #64748b;
            font-weight: 700;
            font-size: 7.5px;
            text-transform: uppercase;
        }
        .vitals-table td { font-weight: 600; color: #334155; }
        .page-footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            padding: 6px 20px;
            background: white;
            border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
            font-size: 9px;
        }
        .page-footer-table { width: 100%; }
        .clinic-name { font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; }
        .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 42px; }
        .empty-dash { color: #94a3b8; }
    </style>
</head>
<body>

@php
    $fechaNac = $paciente->fechaNacimiento;
    if ($fechaNac instanceof \Illuminate\Support\Carbon || $fechaNac instanceof \DateTimeInterface) {
        $fechaNacFmt = $fechaNac->format('d/m/Y');
    } elseif (!empty($fechaNac)) {
        try { $fechaNacFmt = \Carbon\Carbon::parse($fechaNac)->format('d/m/Y'); }
        catch (\Exception $e) { $fechaNacFmt = substr((string) $fechaNac, 0, 10); }
    } else {
        $fechaNacFmt = '—';
    }

    $horaFmt = $nota->hora ?? '';
    if ($horaFmt && strlen($horaFmt) > 5) {
        $horaFmt = substr($horaFmt, 0, 5);
    }

    $dash = '—';
    $hasVitals = $nota->ta_sistolica || $nota->ta_diastolica || $nota->fc || $nota->fr || $nota->spo2
        || $nota->temperatura || $nota->peso || $nota->talla || $nota->imc || $nota->perimetro_abdominal;
@endphp

<div class="page-footer">
    <table class="page-footer-table">
        <tr>
            <td class="clinic-name">{{ $clinica->nombre ?? 'Clínica' }}</td>
            <td class="clinic-contact">
                {{ $clinica->telefono ?? '' }}
                @if($clinica->email ?? null) | {{ $clinica->email }} @endif
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;padding-top:4px;font-size:7px;color:#94a3b8;">
                Generado con <strong style="color:{!! $clinica->color_principal ?? '#0A1628' !!};">Lynkamed</strong>
            </td>
        </tr>
    </table>
</div>

<div class="content-wrapper">

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo-cell">
                    <div class="header-logo">
                        @if(isset($clinicaLogo) && $clinicaLogo)
                            <img src="{{ $clinicaLogo }}" alt="Logo">
                        @endif
                    </div>
                </td>
                <td style="padding-left:10px;">
                    <div class="header-title">Nota de Subsecuente</div>
                    <div class="header-subtitle">Consulta de seguimiento cardiológico</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Registro</div>
                        <div class="header-badge-value">#{{ $paciente->registro }}</div>
                    </div>
                    <div class="header-date">Fecha consulta: {{ $nota->fecha_consulta ? $nota->fecha_consulta->format('d/m/Y') : '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="patient-card">
        <div class="patient-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
        <table class="patient-table">
            <tr>
                <td><span class="patient-label">Edad</span><br><span class="patient-value">{{ $paciente->edad }} años</span></td>
                <td><span class="patient-label">Género</span><br><span class="patient-value">{{ $paciente->genero == 1 ? 'Hombre' : 'Mujer' }}</span></td>
                <td><span class="patient-label">F. Nacimiento</span><br><span class="patient-value">{{ $fechaNacFmt }}</span></td>
                <td><span class="patient-label">Teléfono</span><br><span class="patient-value">{{ $paciente->telefono ?: '—' }}</span></td>
                <td><span class="patient-label">Médico</span><br><span class="patient-value">{{ $user->name ?? '—' }}</span></td>
                <td><span class="patient-label">Hora</span><br><span class="patient-value">{{ $horaFmt ?: '—' }}</span></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Motivo de consulta y síntomas</div>
        <div class="section-body">
            <table style="width:100%"><tr>
                <td class="two-col">
                    <div class="full-label">Motivo de consulta</div>
                    <div class="text-block">{{ $nota->motivo_consulta ?: $dash }}</div>
                </td>
                <td class="two-col">
                    <div class="full-label">Síntomas</div>
                    <div class="text-block">{{ $nota->sintomas ?: $dash }}</div>
                </td>
            </tr></table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Exploración física</div>
        <div class="section-body">
            @if($hasVitals)
            <div class="section-subtitle">Signos vitales y somatometría</div>
            <table class="vitals-table">
                <tr>
                    <th>TA Sist.</th>
                    <th>TA Diast.</th>
                    <th>FC</th>
                    <th>FR</th>
                    <th>SpO2</th>
                    <th>Temp</th>
                    <th>Peso</th>
                    <th>Talla</th>
                    <th>IMC</th>
                    <th>Per. Abd.</th>
                </tr>
                <tr>
                    <td>{{ $nota->ta_sistolica ?: $dash }}</td>
                    <td>{{ $nota->ta_diastolica ?: $dash }}</td>
                    <td>{{ $nota->fc ?: $dash }}</td>
                    <td>{{ $nota->fr ?: $dash }}</td>
                    <td>{{ $nota->spo2 ?: $dash }}</td>
                    <td>{{ $nota->temperatura ?: $dash }}</td>
                    <td>{{ $nota->peso ?: $dash }}</td>
                    <td>{{ $nota->talla ?: $dash }}</td>
                    <td>{{ $nota->imc ?: $dash }}</td>
                    <td>{{ $nota->perimetro_abdominal ?: $dash }}</td>
                </tr>
            </table>
            @endif
            <div class="section-subtitle">Hallazgos</div>
            <div class="text-block">{{ $nota->exploracion_fisica ?: $dash }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Diagnóstico</div>
        <div class="section-body">
            <table style="width:100%">
                <tr>
                    <td class="two-col">
                        <div class="full-label">Diagnóstico principal</div>
                        <div class="text-block">{{ $nota->diagnostico_principal ?: $dash }}</div>
                    </td>
                    <td class="two-col">
                        <div class="full-label">CIE-10</div>
                        <div class="text-block">{{ $nota->diagnostico_cie10 ?: $dash }}</div>
                    </td>
                </tr>
                @if($nota->diagnosticos_secundarios)
                <tr>
                    <td colspan="2" style="padding-top:6px;">
                        <div class="full-label">Diagnósticos secundarios</div>
                        <div class="text-block">{{ $nota->diagnosticos_secundarios }}</div>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    @php $lab = $nota->laboratorios ?? []; @endphp
    @php
        $labTieneDatos = false;
        foreach ($lab as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $sv) { if ($sv) { $labTieneDatos = true; break 2; } }
            } elseif ($v) {
                $labTieneDatos = true;
                break;
            }
        }
    @endphp
    @if($labTieneDatos)
    <div class="section">
        <div class="section-title">Laboratorios del paciente</div>
        <div class="section-body">
            <table style="width:100%;font-size:9px;">
                @php
                    $labFilas = [
                        ['hemoglobina' => 'Hemoglobina', 'leucocitos' => 'Leucocitos', 'plaquetas' => 'Plaquetas', 'hematocrito' => 'Hematocrito'],
                        ['glucosa' => 'Glucosa', 'bun' => 'BUN', 'creatinina' => 'Creatinina', 'acido_urico' => 'Ácido Úrico'],
                        ['colesterol_total' => 'Colesterol Total', 'ldl' => 'LDL', 'hdl' => 'HDL', 'trigliceridos' => 'Triglicéridos'],
                        ['hba1c' => 'HbA1c', 'bnp' => 'BNP/NT-proBNP', 'troponinas' => 'Troponinas', 'dimero_d' => 'Dímero D'],
                    ];
                @endphp
                @foreach($labFilas as $fila)
                    @php
                        $valores = [];
                        foreach ($fila as $key => $label) {
                            if ($lab[$key] ?? '') $valores[] = $label . ': ' . $lab[$key];
                        }
                    @endphp
                    @if(count($valores))
                    <tr>
                        <td style="padding:2px 0;">{{ implode(' &nbsp;|&nbsp; ', $valores) }}</td>
                    </tr>
                    @endif
                @endforeach
                @php
                    $electro = [];
                    foreach (['cloro' => 'Cloro', 'potasio' => 'Potasio', 'magnesio' => 'Magnesio', 'calcio' => 'Calcio'] as $k => $l) {
                        if ($lab['electrolitos'][$k] ?? '') $electro[] = $l . ': ' . $lab['electrolitos'][$k];
                    }
                    foreach (['tsh' => 'TSH', 't3' => 'T3', 't4' => 'T4', 't3_libre' => 'T3 libre'] as $k => $l) {
                        if ($lab['perfil_tiroideo'][$k] ?? '') $electro[] = $l . ': ' . $lab['perfil_tiroideo'][$k];
                    }
                @endphp
                @if(count($electro))
                <tr><td style="padding:2px 0;">{{ implode(' &nbsp;|&nbsp; ', $electro) }}</td></tr>
                @endif
                @if($lab['otros'] ?? '')
                <tr><td style="padding:2px 0;"><strong>Otros:</strong> {{ $lab['otros'] }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    @endif

    <table style="width:100%;border-collapse:collapse;margin-bottom:9px;">
        <tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="section" style="margin-bottom:0;">
                    <div class="section-title">Estudios solicitados</div>
                    <div class="section-body">
                        <div class="text-block">{{ $nota->estudios_solicitados ?: $dash }}</div>
                    </div>
                </div>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="section" style="margin-bottom:0;">
                    <div class="section-title">Próxima cita</div>
                    <div class="section-body">
                        <div class="text-block" style="text-align:center;font-size:11px;font-weight:700;color:{!! $clinica->color_principal ?? '#0A1628' !!};">
                            {{ $nota->proxima_cita ? $nota->proxima_cita->format('d/m/Y') : $dash }}
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table style="width:100%;margin-top:28px;">
        <tr>
            <td style="width:25%;"></td>
            <td style="width:50%;text-align:center;padding-top:8px;">
                @if(isset($firmaBase64) && $firmaBase64)
                <img src="{{ $firmaBase64 }}" alt="Firma" style="height:50px;width:auto;"><br>
                @endif
                <div style="border-top:1px solid #334155;width:220px;margin:4px auto 0 auto;padding-top:6px;">
                    <div style="font-size:10px;font-weight:700;color:{!! $clinica->color_principal ?? '#0A1628' !!};">
                        {{ $user->nombre_con_titulo ?? $user->name ?? '' }}
                        @if($user->cedula_especialista ?? null) — {{ $user->cedula_especialista }}@endif
                    </div>
                    <div style="font-size:9px;color:#64748b;margin-top:2px;">Firma del médico</div>
                </div>
            </td>
            <td style="width:25%;"></td>
        </tr>
    </table>

</div>
</body>
</html>
