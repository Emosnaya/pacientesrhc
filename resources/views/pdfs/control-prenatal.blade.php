<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @font-face {
        font-family: 'DejaVu Sans';
        font-style: normal;
        font-weight: normal;
        src: url('{{ storage_path("fonts/DejaVuSans.ttf") }}') format('truetype');
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; background: #fff; margin: 20px 25px; }

    /* Header */
    .header { background: #0A1628; color: #fff; padding: 14px 18px; border-radius: 6px; margin-bottom: 14px; display: table; width: 100%; }
    .header-left { display: table-cell; vertical-align: middle; width: 70%; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 30%; }
    .header .clinica-name { font-size: 11px; font-weight: 700; color: #e2e8f0; }
    .header .clinica-sub { font-size: 9px; color: #94a3b8; }

    /* Paciente info */
    .paciente-bar { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px 12px; margin-bottom: 12px; display: table; width: 100%; }
    .paciente-bar .col { display: table-cell; vertical-align: top; padding-right: 12px; }
    .paciente-bar .label { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; }
    .paciente-bar .value { font-size: 10px; font-weight: 700; color: #0A1628; margin-top: 1px; }

    /* Section */
    .section { margin-bottom: 12px; }
    .section-title { background: #0A1628; color: #fff; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 5px 10px; border-radius: 4px 4px 0 0; }
    .section-body { border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 4px 4px; padding: 10px 12px; }

    /* Grid */
    .grid { display: table; width: 100%; }
    .grid-row { display: table-row; }
    .grid-cell { display: table-cell; vertical-align: top; padding: 3px 8px 3px 0; }
    .grid-label { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; }
    .grid-value { font-size: 10px; color: #0f172a; margin-top: 1px; font-weight: 600; }

    /* Tags */
    .tag { display: inline-block; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; border-radius: 3px; padding: 1px 5px; font-size: 8px; margin: 1px; }
    .tag-warn { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .tag-ok { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
    .tag-red { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }

    /* Table */
    .data-table { width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 6px; }
    .data-table th { background: #1e3a5f; color: #fff; padding: 5px 7px; text-align: left; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
    .data-table td { padding: 5px 7px; border-bottom: 1px solid #f1f5f9; color: #334155; }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tr:nth-child(even) td { background: #f8fafc; }

    /* Divider */
    .divider { border: none; border-top: 1px solid #e2e8f0; margin: 8px 0; }

    /* Text block */
    .text-block { font-size: 10px; color: #334155; line-height: 1.5; }

    /* Alert box */
    .alert-box { border-radius: 4px; padding: 8px 12px; margin-top: 4px; }
    .alert-urgente { background: #fee2e2; border: 1px solid #fca5a5; }
    .alert-referencia { background: #fff7ed; border: 1px solid #fdba74; }
    .alert-ok { background: #f0fdf4; border: 1px solid #86efac; }

    /* Footer */
    .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 8px; color: #94a3b8; text-align: center; padding: 6px 20px; border-top: 1px solid #e2e8f0; background: #fff; }

    /* Risk badges */
    .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
    .badge-alto { background: #fee2e2; color: #b91c1c; }
    .badge-medio { background: #fff7ed; color: #c2410c; }
    .badge-bajo { background: #f0fdf4; color: #166534; }
    .badge-default { background: #f1f5f9; color: #475569; }
</style>
</head>
<body>

<div class="footer">
    Control Prenatal #{{ $control->numero_control ?? '?' }} — {{ $paciente->nombre_completo ?? ($paciente->nombre ?? '') }} — Generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
</div>

{{-- HEADER --}}
<div class="header">
    <div class="header-left">
        @if($clinicaLogo)
        <img src="{{ $clinicaLogo }}" style="height:36px;width:auto;margin-bottom:4px;"><br>
        @endif
        <div class="clinica-name">{{ $clinica->nombre ?? 'Clínica' }}</div>
        @if($clinica->direccion ?? null)
        <div class="clinica-sub">{{ $clinica->direccion }}</div>
        @endif
        @if($clinica->telefono ?? null)
        <div class="clinica-sub">Tel: {{ $clinica->telefono }}</div>
        @endif
    </div>
    <div class="header-right">
        <div style="font-size:15px;font-weight:700;">CONTROL PRENATAL</div>
        <div style="font-size:11px;color:#93c5fd;margin-top:3px;">
            Consulta #{{ $control->numero_control ?? '?' }}
            @if($control->semanas_gestacion ?? null)
             — {{ $control->semanas_gestacion }} SDG
            @endif
            @if($control->trimestre ?? null)
             — {{ $control->trimestre }}° Trimestre
            @endif
        </div>
        @if($control->fecha_control ?? null)
        <div style="font-size:9px;color:#94a3b8;margin-top:3px;">Fecha: {{ \Carbon\Carbon::parse($control->fecha_control)->format('d/m/Y') }}</div>
        @endif
    </div>
</div>

{{-- ALERTAS URGENTES (si las hay, arriba) --}}
@php $alertas = $control->alertas ?? []; @endphp
@if(!empty($alertas))
@if(($alertas['urgente'] ?? false) || ($alertas['referencia_necesaria'] ?? false))
<div class="alert-box {{ ($alertas['urgente'] ?? false) ? 'alert-urgente' : 'alert-referencia' }}" style="margin-bottom:12px;">
    <div style="font-size:10px;font-weight:700;color:{{ ($alertas['urgente'] ?? false) ? '#b91c1c' : '#c2410c' }};margin-bottom:3px;">
        {{ ($alertas['urgente'] ?? false) ? '⚠ URGENTE' : '➜ REFERENCIA NECESARIA' }}
    </div>
    @if($alertas['motivo_referencia'] ?? null)
    <div style="font-size:9px;color:#374151;margin-bottom:2px;"><strong>Motivo:</strong> {{ $alertas['motivo_referencia'] }}</div>
    @endif
    @if($alertas['hospital_referencia'] ?? null)
    <div style="font-size:9px;color:#374151;"><strong>Hospital:</strong> {{ $alertas['hospital_referencia'] }}</div>
    @endif
</div>
@endif
@endif

{{-- DATOS DE LA PACIENTE --}}
<div class="paciente-bar">
    <div class="col" style="width:32%">
        <div class="label">Paciente</div>
        <div class="value">{{ $paciente->nombre_completo ?? ($paciente->nombre ?? '—') }}</div>
    </div>
    <div class="col" style="width:14%">
        <div class="label">Edad</div>
        <div class="value">
            @if($paciente->fecha_nacimiento)
                {{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age }} años
            @else —
            @endif
        </div>
    </div>
    <div class="col" style="width:18%">
        <div class="label">Expediente</div>
        <div class="value">{{ $paciente->numero_expediente ?? '—' }}</div>
    </div>
    <div class="col" style="width:18%">
        <div class="label">Semanas Gestación</div>
        <div class="value">{{ $control->semanas_gestacion ?? '—' }} SDG</div>
    </div>
    <div class="col" style="width:18%">
        <div class="label">Trimestre</div>
        <div class="value">{{ $control->trimestre ?? '—' }}</div>
    </div>
</div>

{{-- SIGNOS VITALES --}}
@php $sv = $control->signos_vitales ?? []; @endphp
@if(!empty($sv))
<div class="section">
    <div class="section-title">Signos Vitales</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Peso</div>
                    <div class="grid-value" style="font-size:13px;">{{ isset($sv['peso']) ? $sv['peso'] . ' kg' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Presión Arterial</div>
                    @php
                        $pas = $sv['presion_arterial_sistolica'] ?? null;
                        $pad = $sv['presion_arterial_diastolica'] ?? null;
                    @endphp
                    <div class="grid-value" style="font-size:13px;">
                        {{ ($pas && $pad) ? $pas . '/' . $pad : ($sv['presion_arterial'] ?? '—') }} <span style="font-size:9px;font-weight:normal;">mmHg</span>
                    </div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Frecuencia Cardiaca</div>
                    <div class="grid-value" style="font-size:13px;">{{ isset($sv['frecuencia_cardiaca']) ? $sv['frecuencia_cardiaca'] . ' lpm' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Temperatura</div>
                    <div class="grid-value" style="font-size:13px;">{{ isset($sv['temperatura']) ? $sv['temperatura'] . ' °C' : '—' }}</div>
                </div>
            </div>
        </div>
        @if(isset($sv['ganancia_peso_total']) || isset($sv['ganancia_peso_desde_ultimo']))
        <hr class="divider">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:50%">
                    <div class="grid-label">Ganancia de Peso Total</div>
                    <div class="grid-value">{{ isset($sv['ganancia_peso_total']) ? $sv['ganancia_peso_total'] . ' kg' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:50%">
                    <div class="grid-label">Ganancia desde Última Consulta</div>
                    <div class="grid-value">{{ isset($sv['ganancia_peso_desde_ultimo']) ? $sv['ganancia_peso_desde_ultimo'] . ' kg' : '—' }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- EXPLORACIÓN OBSTÉTRICA --}}
@php $exo = $control->exploracion_obstetrica ?? []; @endphp
@if(!empty($exo))
<div class="section">
    <div class="section-title">Exploración Obstétrica</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Altura Uterina</div>
                    <div class="grid-value">{{ isset($exo['altura_uterina']) ? $exo['altura_uterina'] . ' cm' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">FCF</div>
                    <div class="grid-value">{{ isset($exo['frecuencia_cardiaca_fetal']) ? $exo['frecuencia_cardiaca_fetal'] . ' lpm' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Movimientos Fetales</div>
                    <div class="grid-value">{{ isset($exo['movimientos_fetales']) ? ($exo['movimientos_fetales'] ? 'Presentes' : 'Ausentes') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Presentación</div>
                    <div class="grid-value">{{ $exo['presentacion'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Situación / Posición</div>
                    <div class="grid-value">{{ ($exo['situacion'] ?? '—') }} / {{ ($exo['posicion'] ?? '—') }}</div>
                </div>
            </div>
        </div>
        <hr class="divider">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Edema</div>
                    <div class="grid-value">{{ isset($exo['edema']) ? ($exo['edema'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Várices</div>
                    <div class="grid-value">{{ isset($exo['varices']) ? ($exo['varices'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:50%">
                    <div class="grid-label">Actividad Uterina</div>
                    <div class="grid-value">{{ $exo['actividad_uterina'] ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- LABORATORIOS --}}
@php $lab = $control->laboratorios ?? []; @endphp
@if(!empty($lab))
<div class="section">
    <div class="section-title">Laboratorios</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Hemoglobina</div>
                    <div class="grid-value">{{ isset($lab['hemoglobina']) ? $lab['hemoglobina'] . ' g/dL' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Glucosa</div>
                    <div class="grid-value">{{ isset($lab['glucosa']) ? $lab['glucosa'] . ' mg/dL' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Proteínas en Orina</div>
                    <div class="grid-value">{{ $lab['proteinas_orina'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Glucosa en Orina</div>
                    <div class="grid-value">{{ $lab['glucosa_orina'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        @if(isset($lab['bacterias_orina']))
        <hr class="divider">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:100%">
                    <div class="grid-label">Bacterias en Orina</div>
                    <div class="grid-value">{{ $lab['bacterias_orina'] }}</div>
                </div>
            </div>
        </div>
        @endif
        @if(!empty($lab['otros']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">OTROS</div>
        @foreach($lab['otros'] as $otro)
        <span class="tag">{{ is_array($otro) ? ($otro['nombre'] ?? $otro['descripcion'] ?? json_encode($otro)) : $otro }}</span>
        @endforeach
        @endif
    </div>
</div>
@endif

{{-- ULTRASONIDO --}}
@php $us = $control->ultrasonido ?? []; @endphp
@if(!empty($us))
<div class="section">
    <div class="section-title">Ultrasonido</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Realizado</div>
                    <div class="grid-value">{{ isset($us['realizado']) ? ($us['realizado'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                @if($us['realizado'] ?? false)
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Semanas (Eco)</div>
                    <div class="grid-value">{{ isset($us['semanas_eco']) ? $us['semanas_eco'] . ' SDG' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Peso Fetal</div>
                    <div class="grid-value">{{ isset($us['peso_fetal']) ? $us['peso_fetal'] . ' g' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Líquido Amniótico</div>
                    <div class="grid-value">{{ $us['liquido_amniotico'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Placenta</div>
                    <div class="grid-value">{{ $us['placenta'] ?? '—' }}</div>
                </div>
                @endif
            </div>
        </div>
        @if($us['observaciones'] ?? null)
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">OBSERVACIONES</div>
        <div class="text-block">{{ $us['observaciones'] }}</div>
        @endif
    </div>
</div>
@endif

{{-- VACUNAS Y MEDICAMENTOS --}}
@php $vacunas = $control->vacunas ?? []; $medicamentos = $control->medicamentos ?? []; @endphp
@if(!empty($vacunas) || !empty($medicamentos))
<div class="section">
    <div class="section-title">Vacunas y Medicamentos</div>
    <div class="section-body">
        @if(!empty($vacunas))
        <div class="grid-label" style="margin-bottom:3px;">VACUNAS APLICADAS</div>
        @foreach($vacunas as $vac)
        <span class="tag tag-ok">{{ is_array($vac) ? ($vac['nombre'] ?? json_encode($vac)) : $vac }}</span>
        @endforeach
        @endif
        @if(!empty($medicamentos))
        @if(!empty($vacunas))<hr class="divider">@endif
        <div class="grid-label" style="margin-bottom:4px;">MEDICAMENTOS</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Medicamento</th>
                    <th>Dosis</th>
                    <th>Vía</th>
                    <th>Frecuencia</th>
                    <th>Duración</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicamentos as $med)
                <tr>
                    @if(is_array($med))
                    <td>{{ $med['nombre'] ?? '—' }}</td>
                    <td>{{ $med['dosis'] ?? '—' }}</td>
                    <td>{{ $med['via'] ?? '—' }}</td>
                    <td>{{ $med['frecuencia'] ?? '—' }}</td>
                    <td>{{ $med['duracion'] ?? '—' }}</td>
                    @else
                    <td colspan="5">{{ $med }}</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endif

{{-- SIGNOS DE ALARMA --}}
@php $sa = $control->signos_alarma_revisados ?? []; @endphp
@if(!empty($sa))
<div class="section">
    <div class="section-title">Signos de Alarma Revisados</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Sangrado</div>
                    <div class="grid-value">{{ isset($sa['sangrado']) ? ($sa['sangrado'] ? 'Presente' : 'Ausente') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Cefalea</div>
                    <div class="grid-value">{{ isset($sa['cefalea']) ? ($sa['cefalea'] ? 'Presente' : 'Ausente') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Visión Borrosa</div>
                    <div class="grid-value">{{ isset($sa['vision_borrosa']) ? ($sa['vision_borrosa'] ? 'Presente' : 'Ausente') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Edema Cara / Manos</div>
                    <div class="grid-value">{{ isset($sa['edema_cara_manos']) ? ($sa['edema_cara_manos'] ? 'Presente' : 'Ausente') : '—' }}</div>
                </div>
            </div>
            <div class="grid-row">
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Dolor Abdominal</div>
                    <div class="grid-value">{{ isset($sa['dolor_abdominal']) ? ($sa['dolor_abdominal'] ? 'Presente' : 'Ausente') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Fiebre</div>
                    <div class="grid-value">{{ isset($sa['fiebre']) ? ($sa['fiebre'] ? 'Presente' : 'Ausente') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Pérdida de Líquido</div>
                    <div class="grid-value">{{ isset($sa['perdida_liquido']) ? ($sa['perdida_liquido'] ? 'Presente' : 'Ausente') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Disminución Movimientos</div>
                    <div class="grid-value">{{ isset($sa['disminucion_movimientos']) ? ($sa['disminucion_movimientos'] ? 'Presente' : 'Ausente') : '—' }}</div>
                </div>
            </div>
            <div class="grid-row">
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Contracciones</div>
                    <div class="grid-value">{{ isset($sa['contracciones']) ? ($sa['contracciones'] ? 'Presente' : 'Ausente') : '—' }}</div>
                </div>
            </div>
        </div>
        @if($sa['observaciones'] ?? null)
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">OBSERVACIONES</div>
        <div class="text-block">{{ $sa['observaciones'] }}</div>
        @endif
    </div>
</div>
@endif

{{-- EVALUACIÓN DE RIESGO --}}
@php $er = $control->evaluacion_riesgo ?? []; @endphp
@if(!empty($er))
<div class="section">
    <div class="section-title">Evaluación de Riesgo</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:40%">
                    <div class="grid-label">Clasificación Actual</div>
                    <div style="margin-top:4px;">
                        @php $cls = strtolower($er['clasificacion_actual'] ?? ''); @endphp
                        @if(str_contains($cls, 'alto') || str_contains($cls, 'high'))
                            <span class="badge badge-alto">{{ $er['clasificacion_actual'] }}</span>
                        @elseif(str_contains($cls, 'medio') || str_contains($cls, 'moderate'))
                            <span class="badge badge-medio">{{ $er['clasificacion_actual'] }}</span>
                        @elseif(str_contains($cls, 'bajo') || str_contains($cls, 'low'))
                            <span class="badge badge-bajo">{{ $er['clasificacion_actual'] }}</span>
                        @else
                            <span class="badge badge-default">{{ $er['clasificacion_actual'] ?? '—' }}</span>
                        @endif
                    </div>
                </div>
                @if($er['cambio_desde_ultimo'] ?? null)
                <div class="grid-cell" style="width:60%">
                    <div class="grid-label">Cambio desde Última Consulta</div>
                    <div class="grid-value">{{ $er['cambio_desde_ultimo'] }}</div>
                </div>
                @endif
            </div>
        </div>
        @if(!empty($er['factores_nuevos']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">NUEVOS FACTORES DE RIESGO</div>
        @foreach($er['factores_nuevos'] as $fn)
        <span class="tag tag-warn">{{ $fn }}</span>
        @endforeach
        @endif
    </div>
</div>
@endif

{{-- INDICACIONES / OBSERVACIONES --}}
@if($control->indicaciones || $control->observaciones)
<div class="section">
    <div class="section-title">Indicaciones y Observaciones</div>
    <div class="section-body">
        @if($control->indicaciones)
        <div class="grid-label" style="margin-bottom:3px;">INDICACIONES</div>
        <div class="text-block" style="margin-bottom:8px;">{{ $control->indicaciones }}</div>
        @endif
        @if($control->observaciones)
        <div class="grid-label" style="margin-bottom:3px;">OBSERVACIONES</div>
        <div class="text-block">{{ $control->observaciones }}</div>
        @endif
    </div>
</div>
@endif

{{-- PRÓXIMA CITA --}}
@if($control->fecha_proxima_cita || $control->lugar_proxima_cita)
<div class="section">
    <div class="section-title">Próxima Cita</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                @if($control->fecha_proxima_cita)
                <div class="grid-cell" style="width:50%">
                    <div class="grid-label">Fecha</div>
                    <div class="grid-value" style="font-size:12px;">{{ \Carbon\Carbon::parse($control->fecha_proxima_cita)->format('d/m/Y') }}</div>
                </div>
                @endif
                @if($control->lugar_proxima_cita)
                <div class="grid-cell" style="width:50%">
                    <div class="grid-label">Lugar</div>
                    <div class="grid-value">{{ $control->lugar_proxima_cita }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- FIRMA --}}
<table style="width:100%;margin-top:30px;">
    <tr>
        <td style="width:25%;"></td>
        <td style="width:50%;text-align:center;padding-top:8px;">
            @if(isset($firmaBase64) && $firmaBase64)
            <img src="{{ $firmaBase64 }}" alt="Firma" style="height:50px;width:auto;"><br>
            @endif
            <div style="border-top:1px solid #334155;width:200px;margin:4px auto 0 auto;padding-top:6px;">
                <div style="font-size:10px;font-weight:700;color:#0A1628;">
                    {{ $control->medico_nombre ?? ($user->nombre_con_titulo ?? ($user->name ?? '')) }}
                </div>
                @if($control->medico_cedula ?? null)
                <div style="font-size:9px;color:#64748b;">Cédula: {{ $control->medico_cedula }}</div>
                @elseif(($user->cedula_especialista ?? null))
                <div style="font-size:9px;color:#64748b;">Cédula: {{ $user->cedula_especialista }}</div>
                @endif
                <div style="font-size:9px;color:#64748b;margin-top:2px;">Firma del médico</div>
            </div>
        </td>
        <td style="width:25%;"></td>
    </tr>
</table>

</body>
</html>
