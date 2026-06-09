<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historia Clínica Nutriológica</title>
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
            line-height: 1.4;
            margin: 20px 25px;
        }
        table { border-collapse: collapse; }
        /* === HEADER === */
        .header {
            width: 100%;
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 8px 12px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-cell { width: 60px; padding-right: 12px !important; }
        .header-logo {
            width: 45px; height: 45px;
            background: white; border-radius: 6px;
            padding: 5px; text-align: center;
        }
        .header-logo img { max-height: 35px; max-width: 35px; }
        .header-title { font-size: 15px; font-weight: 700; color: white; }
        .header-subtitle { font-size: 9px; color: #94a3b8; }
        .header-meta-cell { text-align: right; width: 120px; }
        .header-badge {
            background: rgba(255,255,255,0.15);
            padding: 5px 10px; border-radius: 5px;
            display: inline-block; margin-bottom: 4px;
        }
        .header-badge-label { font-size: 8px; text-transform: uppercase; color: #94a3b8; }
        .header-badge-value { font-size: 12px; font-weight: 700; color: white; }
        .header-date { font-size: 9px; color: #94a3b8; }
        /* === PATIENT CARD === */
        .patient-card {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 10px 12px; margin-bottom: 8px;
        }
        .patient-table { width: 100%; border-collapse: collapse; }
        .patient-table td { padding: 2px 6px; font-size: 10px; }
        .patient-name { font-size: 13px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; margin-bottom: 6px; }
        .patient-label { color: #64748b; font-size: 9px; }
        .patient-value { font-weight: 600; color: #334155; }
        /* === SECTIONS === */
        .section {
            margin-bottom: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .section-title {
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            color: white;
            font-size: 9px;
            font-weight: 700;
            padding: 4px 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-body { padding: 8px 10px; }
        .section-subtitle {
            background: #f1f5f9;
            color: #334155;
            font-size: 9px;
            font-weight: 700;
            padding: 3px 8px;
            margin: 4px -10px;
        }
        .row-table { width: 100%; border-collapse: collapse; }
        .row-table td { padding: 2px 4px; vertical-align: top; font-size: 9.5px; }
        .lbl { color: #64748b; font-size: 9px; white-space: nowrap; }
        .val { font-weight: 600; }
        .check-yes { color: #16a34a; font-weight: 700; }
        .check-no { color: #94a3b8; }
        .text-block {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 4px; padding: 5px 8px;
            font-size: 9.5px; min-height: 20px;
        }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table td, .data-table th {
            border: 1px solid #e2e8f0;
            padding: 3px 6px;
            font-size: 9px;
        }
        .data-table th {
            background: #f1f5f9;
            font-weight: 700;
            color: #334155;
            text-align: center;
        }
        .data-table td { text-align: center; }
        .data-table td.lbl-col { text-align: left; font-weight: 600; color: #334155; }
        /* === PAGE FOOTER === */
        .page-footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            padding: 4px 14px;
            background: white;
            border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
            font-size: 8px;
        }
        .page-footer-table { width: 100%; border-collapse: collapse; }
        .clinic-name { font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; }
        .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 40px; }
        .two-col { width: 50%; vertical-align: top; padding: 0 4px; }
        .mb-4 { margin-bottom: 4px; }
        .mt-4 { margin-top: 4px; }
        .full-label { font-size: 9px; color: #64748b; margin-bottom: 2px; }
        .firma-section { margin-top: 20px; text-align: center; }
        .firma-img { max-height: 60px; }
        .firma-line { border-top: 1px solid #334155; width: 180px; margin: 4px auto 0; }
        .firma-name { font-size: 9px; font-weight: 700; color: #334155; margin-top: 2px; }
        .firma-ced { font-size: 8px; color: #64748b; }
    </style>
</head>
<body>
@php
    $profesionalNombre = trim(($user->nombre_con_titulo ?? '') ?: (($user->nombre ?? '') . ' ' . ($user->apellidoPat ?? '') . ' ' . ($user->apellidoMat ?? '')));
    $profesionalRol = $user?->rol ? config('roles.lista.' . $user->rol, 'Profesional responsable') : 'Profesional responsable';
@endphp

<!-- PAGE FOOTER (fixed) -->
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

<!-- HEADER -->
<div class="header">
    <table class="header-table">
        <tr>
            @if($clinicaLogo)
            <td class="header-logo-cell">
                <div class="header-logo">
                    <img src="{{ $clinicaLogo }}" alt="Logo">
                </div>
            </td>
            @endif
            <td>
                <div class="header-title">{{ $clinica->nombre ?? 'Clínica de Nutrición' }}</div>
                <div class="header-subtitle">Historia Clínica Nutriológica</div>
            </td>
            <td class="header-meta-cell">
                <div class="header-badge">
                    <div class="header-badge-label">Historia Clínica</div>
                    <div class="header-badge-value">{{ $historia->numero_expediente ?? $paciente->registro ?? 'N/A' }}</div>
                </div>
                <div class="header-date">{{ $historia->fecha_elaboracion ? $historia->fecha_elaboracion->format('d/m/Y') : '' }}</div>
            </td>
        </tr>
    </table>
</div>

<!-- PACIENTE -->
<div class="patient-card">
    <div class="patient-name">
        {{ $paciente->nombre ?? '' }} {{ $paciente->apellidoPat ?? '' }} {{ $paciente->apellidoMat ?? '' }}
    </div>
    <table class="patient-table">
        <tr>
            <td><span class="patient-label">Fecha Nac.</span><br><span class="patient-value">{{ $paciente->fecha_nac ?? 'N/A' }}</span></td>
            <td><span class="patient-label">Sexo</span><br><span class="patient-value">{{ $paciente->genero ?? 'N/A' }}</span></td>
            <td><span class="patient-label">Ocupación</span><br><span class="patient-value">{{ $historia->ocupacion ?? 'N/A' }}</span></td>
            <td><span class="patient-label">Tutor/Responsable</span><br><span class="patient-value">{{ $historia->tutor_nombre ?? 'N/A' }}</span></td>
            <td><span class="patient-label">Profesional responsable</span><br><span class="patient-value">
                {{ $profesionalNombre ?: 'N/A' }}
                @if($user->cedula_especialista ?? null) — Céd. {{ $user->cedula_especialista }} @endif
            </span></td>
        </tr>
    </table>
</div>

<!-- MOTIVO DE CONSULTA -->
<div class="section">
    <div class="section-title">Motivo de Consulta</div>
    <div class="section-body">
        <div class="text-block">{{ $historia->motivo_consulta ?? '—' }}</div>
    </div>
</div>

<!-- ANTECEDENTES HEREDOFAMILIARES -->
@php $ahf = $historia->antecedentes_heredofamiliares ?? []; @endphp
<div class="section">
    <div class="section-title">Antecedentes Heredofamiliares</div>
    <div class="section-body">
        <table class="data-table">
            <tr>
                <th style="text-align:left;width:35%">Padecimiento</th>
                <th>Presencia</th>
                <th>Parentesco</th>
                <th>Observaciones</th>
            </tr>
            @foreach([
                'diabetes' => 'Diabetes Mellitus',
                'hipertension' => 'Hipertensión Arterial',
                'cancer' => 'Cáncer',
                'obesidad' => 'Obesidad',
                'cardiopatias' => 'Cardiopatías',
                'dislipidemias' => 'Dislipidemias',
                'enfermedad_renal' => 'Enfermedad Renal',
                'enfermedad_hepatica' => 'Enf. Hepática',
                'endocrino_metabolicas' => 'Endócrino-Metabólicas',
                'otras' => 'Otras',
            ] as $key => $label)
            <tr>
                <td class="lbl-col">{{ $label }}</td>
                <td>
                    @if(($ahf[$key]['presencia'] ?? false))
                        <span class="check-yes">✓ Sí</span>
                    @else
                        <span class="check-no">No</span>
                    @endif
                </td>
                <td>{{ $ahf[$key]['parentesco'] ?? '—' }}</td>
                <td>{{ $ahf[$key]['observaciones'] ?? '—' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</div>

<!-- ANTECEDENTES PERSONALES PATOLÓGICOS -->
@php $app = $historia->antecedentes_personales_patologicos ?? []; @endphp
<div class="section">
    <div class="section-title">Antecedentes Personales Patológicos</div>
    <div class="section-body">
        <table class="row-table">
            <tr>
                <td style="width:50%;vertical-align:top;">
                    <div class="full-label">Padecimientos</div>
                    <div class="text-block">{{ $app['padecimientos'] ?? '—' }}</div>
                </td>
                <td style="width:25%;vertical-align:top;padding-left:8px;">
                    <div class="full-label">Alergias</div>
                    <div class="text-block">{{ $app['alergias'] ?? '—' }}</div>
                </td>
                <td style="width:25%;vertical-align:top;padding-left:8px;">
                    <div class="full-label">Horas de sueño/día</div>
                    <div class="text-block">{{ $app['horas_sueno'] ?? '—' }}</div>
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- SUSTANCIAS BIOACTIVAS -->
@php $sust = $historia->sustancias_bioactivas ?? []; @endphp
<div class="section">
    <div class="section-title">Consumo de Sustancias Bioactivas</div>
    <div class="section-body">
        <table class="data-table">
            <tr>
                <th style="text-align:left;">Sustancia</th>
                <th>Presencia</th>
                <th>Tipo</th>
                <th>Frecuencia</th>
                <th>Cantidad</th>
            </tr>
            @foreach(['alcohol' => 'Alcohol', 'tabaco' => 'Tabaco', 'bebidas_cafeina' => 'Bebidas con Cafeína', 'drogas' => 'Drogas'] as $key => $label)
            <tr>
                <td class="lbl-col">{{ $label }}</td>
                <td>
                    @if(($sust[$key]['presencia'] ?? false))
                        <span class="check-yes">✓ Sí</span>
                    @else
                        <span class="check-no">No</span>
                    @endif
                </td>
                <td>{{ $sust[$key]['tipo'] ?? '—' }}</td>
                <td>{{ $sust[$key]['frecuencia'] ?? '—' }}</td>
                <td>{{ $sust[$key]['cantidad'] ?? '—' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</div>

<!-- ANTECEDENTES GINECO-OBSTÉTRICOS -->
@php $ago = $historia->antecedentes_gineco_obstetricos ?? []; @endphp
<div class="section">
    <div class="section-title">Antecedentes Gineco-Obstétricos</div>
    <div class="section-body">
        <table class="row-table">
            <tr>
                <td><span class="lbl">Menarca</span><br><span class="val">{{ $ago['menarca'] ?? '—' }}</span></td>
                <td><span class="lbl">Ritmo</span><br><span class="val">{{ $ago['ritmo'] ?? '—' }}</span></td>
                <td><span class="lbl">Eumenorrea</span><br><span class="val">{{ ($ago['eumenorrea'] ?? false) ? 'Sí' : 'No' }}</span></td>
                <td><span class="lbl">Dismenorrea</span><br><span class="val">{{ ($ago['dismenorrea'] ?? false) ? 'Sí' : 'No' }}</span></td>
                <td><span class="lbl">MPF</span><br><span class="val">{{ $ago['mpf'] ?? '—' }}</span></td>
                <td><span class="lbl">G</span><br><span class="val">{{ $ago['g'] ?? '—' }}</span></td>
                <td><span class="lbl">P</span><br><span class="val">{{ $ago['p'] ?? '—' }}</span></td>
                <td><span class="lbl">A</span><br><span class="val">{{ $ago['a'] ?? '—' }}</span></td>
                <td><span class="lbl">C</span><br><span class="val">{{ $ago['c'] ?? '—' }}</span></td>
                <td><span class="lbl">FUM</span><br><span class="val">{{ $ago['fum'] ?? '—' }}</span></td>
            </tr>
        </table>
    </div>
</div>

<!-- PADECIMIENTO ACTUAL Y TERAPÉUTICA -->
@php $pat = $historia->padecimiento_terapeutica ?? []; $meds = $historia->uso_medicamentos ?? []; @endphp
<div class="section">
    <div class="section-title">Padecimiento Actual y Terapéutica</div>
    <div class="section-body">
        <table class="data-table">
            <tr>
                <th style="width:8%;text-align:left;">Ítem</th>
                <th>Descripción</th>
                <th style="width:25%;">Terapéutica</th>
            </tr>
            @foreach(['A','B','C','D'] as $letra)
            <tr>
                <td class="lbl-col">{{ $letra }}</td>
                <td>{{ $pat[$letra]['descripcion'] ?? '—' }}</td>
                <td>{{ $pat[$letra]['terapeutica'] ?? '—' }}</td>
            </tr>
            @endforeach
        </table>
        <div class="mt-4">
            <div class="full-label mb-4">Uso de Medicamentos</div>
            <table class="data-table">
                <tr>
                    @foreach(['Suplementos' => 'suplementos','Laxantes' => 'laxantes','Diuréticos' => 'diureticos','Anabolizantes' => 'anabolizantes','Analgésicos' => 'analgesicos','Anticonceptivos' => 'anticonceptivos','Otros' => 'otros'] as $label => $key)
                    <th>{{ $label }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach(['suplementos','laxantes','diureticos','anabolizantes','analgesicos','anticonceptivos','otros'] as $key)
                    <td>{{ ($meds[$key] ?? false) ? '✓' : '—' }}</td>
                    @endforeach
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- VALORACIÓN CARDIOVASCULAR -->
@php $cv = $historia->valoracion_cardiovascular ?? []; @endphp
<div class="section">
    <div class="section-title">Valoración Cardiovascular TA/FC</div>
    <div class="section-body">
        <table class="data-table">
            <tr>
                <th style="text-align:left;">Fecha</th>
                <th>TA</th>
                <th>FC P</th>
                <th>FC P1</th>
                <th>FC P2</th>
                <th>Índice Ruffier</th>
                <th>Diagnóstico</th>
            </tr>
            @foreach($cv as $row)
            <tr>
                <td class="lbl-col">{{ $row['fecha'] ?? '—' }}</td>
                <td>{{ $row['ta'] ?? '—' }}</td>
                <td>{{ $row['fc_p'] ?? '—' }}</td>
                <td>{{ $row['fc_p1'] ?? '—' }}</td>
                <td>{{ $row['fc_p2'] ?? '—' }}</td>
                <td>{{ $row['indice_ruffier'] ?? '—' }}</td>
                <td>{{ $row['diagnostico'] ?? '—' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</div>

<!-- INDICADORES ANTROPOMÉTRICOS -->
<div class="section">
    <div class="section-title">Indicadores Antropométricos</div>
    <div class="section-body">
        <table class="row-table">
            <tr>
                <td><span class="lbl">Fecha Eval.</span><br><span class="val">{{ $historia->fecha_evaluacion_antrop ? \Carbon\Carbon::parse($historia->fecha_evaluacion_antrop)->format('d/m/Y') : '—' }}</span></td>
                <td><span class="lbl">Edad (años)</span><br><span class="val">{{ $historia->edad_anos ?? '—' }}</span></td>
                <td><span class="lbl">Peso actual (kg)</span><br><span class="val">{{ $historia->peso_actual ?? '—' }}</span></td>
                <td><span class="lbl">Talla (cm)</span><br><span class="val">{{ $historia->talla_cm ?? '—' }}</span></td>
                <td><span class="lbl">Peso habitual</span><br><span class="val">{{ $historia->peso_habitual ?? '—' }}</span></td>
                <td><span class="lbl">Peso máximo</span><br><span class="val">{{ $historia->peso_maximo ?? '—' }}</span></td>
                <td><span class="lbl">Peso mínimo</span><br><span class="val">{{ $historia->peso_minimo ?? '—' }}</span></td>
            </tr>
        </table>

        @php $pliegues = $historia->pliegues_cutaneos ?? []; @endphp
        <div class="section-subtitle">Pliegues Cutáneos (mm)</div>
        <table class="data-table mt-4">
            <tr>
                @foreach(['Bicipital','Tricipital','Subescapular','Pectoral','Abdominal','Suprailiaco','Muslo Anterior','Pierna Med.'] as $p)
                <th>{{ $p }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach(['bicipital','tricipital','subescapular','pectoral','abdominal','suprailiaco','muslo_anterior','pierna_medial'] as $k)
                <td>{{ $pliegues[$k] ?? '—' }}</td>
                @endforeach
            </tr>
        </table>

        @php $per = $historia->perimetros ?? []; @endphp
        <div class="section-subtitle">Perímetros (cm)</div>
        <table class="data-table mt-4">
            <tr>
                @foreach(['Cabeza','Cuello','Pecho','Cintura','Abdomen','Cadera','Brazo Rel.','Brazo Cont.','Antebrazo','Muslo','Pierna'] as $p)
                <th>{{ $p }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach(['cabeza','cuello','pecho','cintura','abdomen','cadera','brazo_relajado','brazo_contraido','antebrazo','muslo','pierna'] as $k)
                <td>{{ $per[$k] ?? '—' }}</td>
                @endforeach
            </tr>
        </table>

        @php $diam = $historia->diametros ?? []; $long = $historia->longitudes ?? []; @endphp
        <table class="row-table mt-4">
            <tr>
                <td style="width:50%;vertical-align:top;">
                    <div class="section-subtitle" style="margin:0 0 4px;">Diámetros (cm)</div>
                    <table class="data-table">
                        <tr>
                            <th>Biacromial</th><th>Tórax AP</th><th>Tórax ML</th><th>Bitrocantérico</th><th>Bimaleolar</th>
                        </tr>
                        <tr>
                            <td>{{ $diam['biacromial'] ?? '—' }}</td>
                            <td>{{ $diam['torax_ap'] ?? '—' }}</td>
                            <td>{{ $diam['torax_ml'] ?? '—' }}</td>
                            <td>{{ $diam['bitrocantereo'] ?? '—' }}</td>
                            <td>{{ $diam['bimaleolar'] ?? '—' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width:50%;vertical-align:top;padding-left:8px;">
                    <div class="section-subtitle" style="margin:0 0 4px;">Longitudes (cm)</div>
                    <table class="data-table">
                        <tr>
                            <th>Tronco</th><th>Miembro Sup.</th><th>Miembro Inf.</th>
                        </tr>
                        <tr>
                            <td>{{ $long['tronco'] ?? '—' }}</td>
                            <td>{{ $long['miembro_superior'] ?? '—' }}</td>
                            <td>{{ $long['miembro_inferior'] ?? '—' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        @php $idx = $historia->indices ?? []; @endphp
        <div class="section-subtitle">Índices</div>
        <table class="data-table mt-4">
            <tr>
                <th>IMC</th><th>ICC</th><th>% Grasa</th><th>Masa Grasa (kg)</th><th>Masa Libre Grasa (kg)</th><th>Masa Muscular (kg)</th><th>Masa Ósea (kg)</th><th>Agua Total (%)</th>
            </tr>
            <tr>
                <td>{{ $idx['imc'] ?? '—' }}</td>
                <td>{{ $idx['icc'] ?? '—' }}</td>
                <td>{{ $idx['pct_grasa'] ?? '—' }}</td>
                <td>{{ $idx['masa_grasa'] ?? '—' }}</td>
                <td>{{ $idx['masa_libre_grasa'] ?? '—' }}</td>
                <td>{{ $idx['masa_muscular'] ?? '—' }}</td>
                <td>{{ $idx['masa_osea'] ?? '—' }}</td>
                <td>{{ $idx['agua_total'] ?? '—' }}</td>
            </tr>
        </table>
    </div>
</div>

<!-- ACTIVIDAD FÍSICA -->
@php $act = $historia->actividad_fisica ?? []; @endphp
<div class="section">
    <div class="section-title">Actividad Física o Deporte Actual</div>
    <div class="section-body">
        <table class="data-table">
            <tr>
                <th style="text-align:left;width:8%">Ítem</th>
                <th>Actividad</th>
                <th>Frecuencia/semana</th>
                <th>Duración/sesión (min)</th>
                <th>Intensidad</th>
                <th>Costo energético (kcal)</th>
            </tr>
            @foreach(['A','B','C','D'] as $letra)
            <tr>
                <td class="lbl-col">{{ $letra }}</td>
                <td>{{ $act[$letra]['actividad'] ?? '—' }}</td>
                <td>{{ $act[$letra]['frecuencia'] ?? '—' }}</td>
                <td>{{ $act[$letra]['duracion'] ?? '—' }}</td>
                <td>{{ $act[$letra]['intensidad'] ?? '—' }}</td>
                <td>{{ $act[$letra]['costo_energetico'] ?? '—' }}</td>
            </tr>
            @endforeach
        </table>
        <table class="row-table mt-4">
            <tr>
                <td><span class="lbl">Total min/semana</span><br><span class="val">{{ $historia->total_minutos_semana ?? '—' }}</span></td>
                <td><span class="lbl">Costo energético total (kcal)</span><br><span class="val">{{ $historia->costo_energetico_total_act ?? '—' }}</span></td>
                <td><span class="lbl">¿Cumple ACSM?</span><br>
                    <span class="{{ $historia->cumple_acsm ? 'check-yes' : 'check-no' }}">{{ $historia->cumple_acsm ? '✓ Sí' : 'No' }}</span>
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- RECOMENDACIÓN DE ACTIVIDAD FÍSICA -->
@php $rec = $historia->recomendacion_actividad_fisica ?? []; @endphp
<div class="section">
    <div class="section-title">Recomendación de Actividad Física</div>
    <div class="section-body">
        <table class="data-table">
            <tr>
                <th style="text-align:left;width:8%">Ítem</th>
                <th>Actividad</th>
                <th>Frecuencia/semana</th>
                <th>Duración/sesión (min)</th>
                <th>Intensidad</th>
                <th>FC Karvonen</th>
                <th>Costo energético (kcal)</th>
            </tr>
            @foreach(['A','B','C'] as $letra)
            <tr>
                <td class="lbl-col">{{ $letra }}</td>
                <td>{{ $rec[$letra]['actividad'] ?? '—' }}</td>
                <td>{{ $rec[$letra]['frecuencia'] ?? '—' }}</td>
                <td>{{ $rec[$letra]['duracion'] ?? '—' }}</td>
                <td>{{ $rec[$letra]['intensidad'] ?? '—' }}</td>
                <td>{{ $rec[$letra]['fc_karvonen'] ?? '—' }}</td>
                <td>{{ $rec[$letra]['costo_energetico'] ?? '—' }}</td>
            </tr>
            @endforeach
        </table>
        <table class="row-table mt-4">
            <tr>
                <td><span class="lbl">Total min/semana rec.</span><br><span class="val">{{ $historia->total_minutos_semana_rec ?? '—' }}</span></td>
                <td><span class="lbl">Costo energético total rec. (kcal)</span><br><span class="val">{{ $historia->costo_energetico_total_rec ?? '—' }}</span></td>
            </tr>
        </table>
        @if(!empty($historia->observaciones_actividad))
        <div class="full-label mt-4">Observaciones</div>
        <div class="text-block">{{ $historia->observaciones_actividad }}</div>
        @endif
    </div>
</div>

<!-- FIRMA -->
<div class="firma-section">
    @if($firmaBase64)
    <img src="{{ $firmaBase64 }}" class="firma-img" alt="Firma">
    @endif
    <div class="firma-line"></div>
    <div class="firma-name">
        {{ $profesionalNombre ?: 'Profesional responsable' }}
    </div>
    @if($user->cedula_especialista ?? null)
    <div class="firma-ced">Cédula: {{ $user->cedula_especialista }}</div>
    @endif
    <div class="firma-ced">{{ $profesionalRol }}</div>
</div>

</div><!-- /content-wrapper -->
</body>
</html>
