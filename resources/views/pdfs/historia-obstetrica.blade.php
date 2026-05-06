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
    .header { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: #fff; padding: 14px 18px; border-radius: 6px; margin-bottom: 14px; display: table; width: 100%; }
    .header-left { display: table-cell; vertical-align: middle; width: 70%; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 30%; }
    .header h1 { font-size: 15px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 2px; }
    .header .subtitle { font-size: 9px; color: #94a3b8; }
    .header .clinica-name { font-size: 11px; font-weight: 700; color: #e2e8f0; }
    .header .clinica-sub { font-size: 9px; color: #94a3b8; }

    /* Paciente info */
    .paciente-bar { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px 12px; margin-bottom: 12px; display: table; width: 100%; }
    .paciente-bar .col { display: table-cell; vertical-align: top; padding-right: 12px; }
    .paciente-bar .label { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; }
    .paciente-bar .value { font-size: 10px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; margin-top: 1px; }

    /* Section */
    .section { margin-bottom: 12px; }
    .section-title { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: #fff; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 5px 10px; border-radius: 4px 4px 0 0; }
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

    /* Table */
    .data-table { width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 6px; }
    .data-table th { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: #fff; padding: 5px 7px; text-align: left; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
    .data-table td { padding: 5px 7px; border-bottom: 1px solid #f1f5f9; color: #334155; }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tr:nth-child(even) td { background: #f8fafc; }

    /* Divider */
    .divider { border: none; border-top: 1px solid #e2e8f0; margin: 8px 0; }

    /* Text block */
    .text-block { font-size: 10px; color: #334155; line-height: 1.5; }

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
    Historia Obstétrica — {{ $paciente->nombre_completo ?? ($paciente->nombre ?? '') }} — Generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
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
        <div style="font-size:15px;font-weight:700;">HISTORIA OBSTÉTRICA</div>
        <div class="subtitle">{{ \Carbon\Carbon::now()->format('d \d\e F \d\e Y') }}</div>
        @if($historia->created_at ?? null)
        <div class="subtitle" style="margin-top:3px;">Fecha registro: {{ \Carbon\Carbon::parse($historia->created_at)->format('d/m/Y') }}</div>
        @endif
    </div>
</div>

{{-- DATOS DE LA PACIENTE --}}
<div class="paciente-bar">
    <div class="col" style="width:30%">
        <div class="label">Paciente</div>
        <div class="value">{{ $paciente->nombre_completo ?? ($paciente->nombre ?? '—') }}</div>
    </div>
    <div class="col" style="width:15%">
        <div class="label">Edad</div>
        <div class="value">
            @if($paciente->fecha_nacimiento)
                {{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age }} años
            @else —
            @endif
        </div>
    </div>
    <div class="col" style="width:20%">
        <div class="label">Fecha Nacimiento</div>
        <div class="value">
            @if($paciente->fecha_nacimiento)
                {{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') }}
            @else —
            @endif
        </div>
    </div>
    <div class="col" style="width:20%">
        <div class="label">Expediente</div>
        <div class="value">{{ $paciente->numero_expediente ?? '—' }}</div>
    </div>
    <div class="col" style="width:15%">
        <div class="label">Grupo / RH</div>
        @php $ap = $historia->antecedentes_personales ?? []; @endphp
        <div class="value">{{ ($ap['grupo_sanguineo'] ?? '—') }} {{ ($ap['factor_rh'] ?? '') }}</div>
    </div>
</div>

{{-- MOTIVO DE CONSULTA / PADECIMIENTO --}}
@if($historia->motivo_consulta || $historia->padecimiento_actual)
<div class="section">
    <div class="section-title">Motivo de Consulta y Padecimiento Actual</div>
    <div class="section-body">
        @if($historia->motivo_consulta)
        <div class="grid-label" style="margin-bottom:3px;">MOTIVO DE CONSULTA</div>
        <div class="text-block" style="margin-bottom:8px;">{{ $historia->motivo_consulta }}</div>
        @endif
        @if($historia->padecimiento_actual)
        <div class="grid-label" style="margin-bottom:3px;">PADECIMIENTO ACTUAL</div>
        <div class="text-block">{{ $historia->padecimiento_actual }}</div>
        @endif
    </div>
</div>
@endif

{{-- EMBARAZO ACTUAL --}}
@php $ea = $historia->embarazo_actual ?? []; @endphp
@if(!empty($ea))
<div class="section">
    <div class="section-title">Embarazo Actual</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">FUM</div>
                    <div class="grid-value">{{ $ea['fum'] ? \Carbon\Carbon::parse($ea['fum'])->format('d/m/Y') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">FPP</div>
                    <div class="grid-value">{{ isset($ea['fpp']) ? \Carbon\Carbon::parse($ea['fpp'])->format('d/m/Y') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Semanas de Gestación</div>
                    <div class="grid-value">{{ $ea['semanas_gestacion'] ?? '—' }} SDG</div>
                </div>
                <div class="grid-cell" style="width:15%">
                    <div class="grid-label">Trimestre</div>
                    <div class="grid-value">{{ $ea['trimestre'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Método Cálculo FPP</div>
                    <div class="grid-value">{{ $ea['metodo_calculo_fpp'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        <hr class="divider">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:33%">
                    <div class="grid-label">Embarazo Planeado</div>
                    <div class="grid-value">{{ isset($ea['embarazo_planeado']) ? ($ea['embarazo_planeado'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:33%">
                    <div class="grid-label">Embarazo Deseado</div>
                    <div class="grid-value">{{ isset($ea['embarazo_deseado']) ? ($ea['embarazo_deseado'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:33%">
                    <div class="grid-label">Control Prenatal Previo</div>
                    <div class="grid-value">{{ isset($ea['control_prenatal_previo']) ? ($ea['control_prenatal_previo'] ? 'Sí — ' . ($ea['num_controles_previos'] ?? '?') . ' controles' : 'No') : '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ANTECEDENTES OBSTÉTRICOS --}}
@php $ao = $historia->antecedentes_obstetricos ?? []; @endphp
@if(!empty($ao))
<div class="section">
    <div class="section-title">Antecedentes Obstétricos</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:12.5%">
                    <div class="grid-label">Gestas</div>
                    <div class="grid-value" style="font-size:14px;">{{ $ao['gestas'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:12.5%">
                    <div class="grid-label">Partos</div>
                    <div class="grid-value" style="font-size:14px;">{{ $ao['partos'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:12.5%">
                    <div class="grid-label">Cesáreas</div>
                    <div class="grid-value" style="font-size:14px;">{{ $ao['cesareas'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:12.5%">
                    <div class="grid-label">Abortos</div>
                    <div class="grid-value" style="font-size:14px;">{{ $ao['abortos'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:12.5%">
                    <div class="grid-label">Ectópicos</div>
                    <div class="grid-value" style="font-size:14px;">{{ $ao['ectopicos'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:12.5%">
                    <div class="grid-label">Molas</div>
                    <div class="grid-value" style="font-size:14px;">{{ $ao['molas'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:12.5%">
                    <div class="grid-label">Hijos Vivos</div>
                    <div class="grid-value" style="font-size:14px;">{{ $ao['hijos_vivos'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:12.5%">
                    <div class="grid-label">Hijos Muertos</div>
                    <div class="grid-value" style="font-size:14px;">{{ $ao['hijos_muertos'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        @if(!empty($ao['periodo_intergenesico']))
        <hr class="divider">
        <div class="grid-label">PERÍODO INTERGENÉSICO</div>
        <div class="grid-value" style="margin-top:2px;">{{ $ao['periodo_intergenesico'] }}</div>
        @endif
        @if(!empty($ao['embarazos_previos']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:4px;">EMBARAZOS PREVIOS</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Año</th>
                    <th>SDG</th>
                    <th>Tipo Parto</th>
                    <th>Peso RN</th>
                    <th>Sexo</th>
                    <th>Estado Actual</th>
                    <th>Complicaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ao['embarazos_previos'] as $i => $ep)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $ep['anio'] ?? '—' }}</td>
                    <td>{{ $ep['semanas'] ?? '—' }}</td>
                    <td>{{ $ep['tipo_parto'] ?? '—' }}</td>
                    <td>{{ isset($ep['peso_rn']) ? $ep['peso_rn'] . ' g' : '—' }}</td>
                    <td>{{ $ep['sexo'] ?? '—' }}</td>
                    <td>{{ $ep['estado_actual'] ?? '—' }}</td>
                    <td>{{ $ep['complicaciones'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endif

{{-- ANTECEDENTES PERSONALES --}}
@php $aper = $historia->antecedentes_personales ?? []; @endphp
@if(!empty($aper))
<div class="section">
    <div class="section-title">Antecedentes Personales Patológicos</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Grupo / Factor RH</div>
                    <div class="grid-value">{{ ($aper['grupo_sanguineo'] ?? '—') }} {{ ($aper['factor_rh'] ?? '') }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Transfusiones</div>
                    <div class="grid-value">{{ isset($aper['transfusiones']) ? ($aper['transfusiones'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
            </div>
        </div>
        @if(!empty($aper['enfermedades_cronicas']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">ENFERMEDADES CRÓNICAS</div>
        @foreach($aper['enfermedades_cronicas'] as $ec)
        <span class="tag tag-warn">{{ $ec }}</span>
        @endforeach
        @endif
        @if(!empty($aper['alergias']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">ALERGIAS</div>
        @foreach($aper['alergias'] as $al)
        <span class="tag tag-warn">{{ $al }}</span>
        @endforeach
        @endif
        @if(!empty($aper['cirugias_previas']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">CIRUGÍAS PREVIAS</div>
        @foreach($aper['cirugias_previas'] as $cir)
        <span class="tag">{{ $cir }}</span>
        @endforeach
        @endif
        @if(!empty($aper['medicamentos_habituales']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">MEDICAMENTOS HABITUALES</div>
        @foreach($aper['medicamentos_habituales'] as $med)
        <span class="tag">{{ $med }}</span>
        @endforeach
        @endif
    </div>
</div>
@endif

{{-- ANTECEDENTES FAMILIARES --}}
@php $af = $historia->antecedentes_familiares ?? []; @endphp
@if(!empty($af))
<div class="section">
    <div class="section-title">Antecedentes Familiares</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Diabetes</div>
                    <div class="grid-value">{{ isset($af['diabetes']) ? ($af['diabetes'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Hipertensión</div>
                    <div class="grid-value">{{ isset($af['hipertension']) ? ($af['hipertension'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Preeclampsia</div>
                    <div class="grid-value">{{ isset($af['preeclampsia']) ? ($af['preeclampsia'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Gemelar</div>
                    <div class="grid-value">{{ isset($af['gemelar']) ? ($af['gemelar'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Malformaciones</div>
                    <div class="grid-value">{{ isset($af['malformaciones']) ? ($af['malformaciones'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
            </div>
        </div>
        @if($af['enfermedades_geneticas'] ?? null)
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">ENFERMEDADES GENÉTICAS</div>
        <div class="text-block">{{ $af['enfermedades_geneticas'] }}</div>
        @endif
        @if($af['otros'] ?? null)
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">OTROS</div>
        <div class="text-block">{{ $af['otros'] }}</div>
        @endif
    </div>
</div>
@endif

{{-- SIGNOS VITALES --}}
@php $sv = $historia->signos_vitales ?? []; @endphp
@if(!empty($sv))
<div class="section">
    <div class="section-title">Signos Vitales y Somatometría</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:14%">
                    <div class="grid-label">Peso Actual</div>
                    <div class="grid-value">{{ isset($sv['peso']) ? $sv['peso'] . ' kg' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:14%">
                    <div class="grid-label">Talla</div>
                    <div class="grid-value">{{ isset($sv['talla']) ? $sv['talla'] . ' cm' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:14%">
                    <div class="grid-label">IMC</div>
                    <div class="grid-value">{{ $sv['imc'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Peso Pregestacional</div>
                    <div class="grid-value">{{ isset($sv['peso_pregestacional']) ? $sv['peso_pregestacional'] . ' kg' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Ganancia de Peso</div>
                    <div class="grid-value">{{ isset($sv['ganancia_peso']) ? $sv['ganancia_peso'] . ' kg' : '—' }}</div>
                </div>
            </div>
        </div>
        <hr class="divider">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:33%">
                    <div class="grid-label">Presión Arterial</div>
                    <div class="grid-value" style="font-size:12px;">{{ $sv['presion_arterial'] ?? '—' }} <span style="font-size:9px;font-weight:normal;">mmHg</span></div>
                </div>
                <div class="grid-cell" style="width:33%">
                    <div class="grid-label">Frecuencia Cardiaca</div>
                    <div class="grid-value" style="font-size:12px;">{{ $sv['frecuencia_cardiaca'] ?? '—' }} <span style="font-size:9px;font-weight:normal;">lpm</span></div>
                </div>
                <div class="grid-cell" style="width:33%">
                    <div class="grid-label">Temperatura</div>
                    <div class="grid-value" style="font-size:12px;">{{ $sv['temperatura'] ?? '—' }} <span style="font-size:9px;font-weight:normal;">°C</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- EXPLORACIÓN OBSTÉTRICA --}}
@php $exo = $historia->exploracion_obstetrica ?? []; @endphp
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
                    <div class="grid-label">Presentación</div>
                    <div class="grid-value">{{ $exo['presentacion'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Situación</div>
                    <div class="grid-value">{{ $exo['situacion'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Posición</div>
                    <div class="grid-value">{{ $exo['posicion'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">FCF</div>
                    <div class="grid-value">{{ isset($exo['frecuencia_cardiaca_fetal']) ? $exo['frecuencia_cardiaca_fetal'] . ' lpm' : '—' }}</div>
                </div>
            </div>
        </div>
        <hr class="divider">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Movimientos Fetales</div>
                    <div class="grid-value">{{ isset($exo['movimientos_fetales']) ? ($exo['movimientos_fetales'] ? 'Presentes' : 'Ausentes') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Edema</div>
                    <div class="grid-value">{{ isset($exo['edema']) ? ($exo['edema'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Várices</div>
                    <div class="grid-value">{{ isset($exo['varices']) ? ($exo['varices'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Reflejos Ostotendinosos</div>
                    <div class="grid-value">{{ $exo['reflejos_osteotendinosos'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        @php $mamas = $exo['mamas'] ?? []; @endphp
        @if(!empty($mamas))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">MAMAS</div>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:50%">
                    <div class="grid-label">Preparación para lactancia</div>
                    <div class="grid-value">{{ isset($mamas['preparacion_lactancia']) ? ($mamas['preparacion_lactancia'] ? 'Sí' : 'No') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:50%">
                    <div class="grid-label">Anomalías</div>
                    <div class="grid-value">{{ $mamas['anomalias'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        @endif
        @php $cervix = $exo['cervix'] ?? []; @endphp
        @if(!empty($cervix))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">CÉRVIX</div>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Dilatación</div>
                    <div class="grid-value">{{ isset($cervix['dilatacion']) ? $cervix['dilatacion'] . ' cm' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Borramiento</div>
                    <div class="grid-value">{{ isset($cervix['borramiento']) ? $cervix['borramiento'] . '%' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Consistencia</div>
                    <div class="grid-value">{{ $cervix['consistencia'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Posición</div>
                    <div class="grid-value">{{ $cervix['posicion'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:20%">
                    <div class="grid-label">Altura Presentación</div>
                    <div class="grid-value">{{ $cervix['altura_presentacion'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- LABORATORIOS --}}
@php $lab = $historia->laboratorios ?? []; @endphp
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
                    <div class="grid-label">Hematocrito</div>
                    <div class="grid-value">{{ isset($lab['hematocrito']) ? $lab['hematocrito'] . '%' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Glucosa</div>
                    <div class="grid-value">{{ isset($lab['glucosa']) ? $lab['glucosa'] . ' mg/dL' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Ácido Úrico</div>
                    <div class="grid-value">{{ isset($lab['acido_urico']) ? $lab['acido_urico'] . ' mg/dL' : '—' }}</div>
                </div>
            </div>
            <div class="grid-row">
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Urea</div>
                    <div class="grid-value">{{ isset($lab['urea']) ? $lab['urea'] . ' mg/dL' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Creatinina</div>
                    <div class="grid-value">{{ isset($lab['creatinina']) ? $lab['creatinina'] . ' mg/dL' : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Examen General de Orina</div>
                    <div class="grid-value">{{ $lab['examen_orina'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:25%">
                    <div class="grid-label">Urocultivo</div>
                    <div class="grid-value">{{ $lab['urocultivo'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        <hr class="divider">
        <div style="font-size:9px;color:#475569;font-weight:700;margin-bottom:4px;">SEROLOGÍA</div>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:16.6%">
                    <div class="grid-label">Grupo/RH</div>
                    <div class="grid-value">{{ $lab['grupo_rh'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:16.6%">
                    <div class="grid-label">Coombs Indirecto</div>
                    <div class="grid-value">{{ $lab['coombs_indirecto'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:16.6%">
                    <div class="grid-label">VDRL</div>
                    <div class="grid-value">{{ $lab['vdrl'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:16.6%">
                    <div class="grid-label">VIH</div>
                    <div class="grid-value">{{ $lab['vih'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:16.6%">
                    <div class="grid-label">Hepatitis B</div>
                    <div class="grid-value">{{ $lab['hepatitis_b'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:16.6%">
                    <div class="grid-label">Toxoplasma</div>
                    <div class="grid-value">{{ $lab['toxoplasma'] ?? '—' }}</div>
                </div>
            </div>
            <div class="grid-row">
                <div class="grid-cell" style="width:16.6%">
                    <div class="grid-label">Rubéola</div>
                    <div class="grid-value">{{ $lab['rubeola'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:16.6%">
                    <div class="grid-label">Citomegalovirus</div>
                    <div class="grid-value">{{ $lab['citomegalovirus'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:16.6%">
                    <div class="grid-label">Herpes</div>
                    <div class="grid-value">{{ $lab['herpes'] ?? '—' }}</div>
                </div>
                <div class="grid-cell" style="width:16.6%">
                    <div class="grid-label">Perfil Tiroideo</div>
                    <div class="grid-value">{{ $lab['perfil_tiroideo'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        @if(!empty($lab['otros']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">OTROS LABORATORIOS</div>
        @foreach($lab['otros'] as $otro)
        <span class="tag">{{ is_array($otro) ? ($otro['nombre'] ?? $otro['descripcion'] ?? json_encode($otro)) : $otro }}</span>
        @endforeach
        @endif
    </div>
</div>
@endif

{{-- RIESGO OBSTÉTRICO --}}
@php $ro = $historia->riesgo_obstetrico ?? []; @endphp
@if(!empty($ro))
<div class="section">
    <div class="section-title">Riesgo Obstétrico</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:40%">
                    <div class="grid-label">Clasificación de Riesgo</div>
                    <div style="margin-top:4px;">
                        @php $clasificacion = strtolower($ro['clasificacion'] ?? ''); @endphp
                        @if(str_contains($clasificacion, 'alto') || str_contains($clasificacion, 'high'))
                            <span class="badge badge-alto">{{ $ro['clasificacion'] }}</span>
                        @elseif(str_contains($clasificacion, 'medio') || str_contains($clasificacion, 'moderate'))
                            <span class="badge badge-medio">{{ $ro['clasificacion'] }}</span>
                        @elseif(str_contains($clasificacion, 'bajo') || str_contains($clasificacion, 'low'))
                            <span class="badge badge-bajo">{{ $ro['clasificacion'] }}</span>
                        @else
                            <span class="badge badge-default">{{ $ro['clasificacion'] ?? '—' }}</span>
                        @endif
                    </div>
                </div>
                @if(isset($ro['puntuacion']))
                <div class="grid-cell" style="width:30%">
                    <div class="grid-label">Puntuación</div>
                    <div class="grid-value" style="font-size:14px;">{{ $ro['puntuacion'] }}</div>
                </div>
                @endif
            </div>
        </div>
        @if(!empty($ro['factores']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">FACTORES DE RIESGO</div>
        @foreach($ro['factores'] as $factor)
        <span class="tag tag-warn">{{ $factor }}</span>
        @endforeach
        @endif
    </div>
</div>
@endif

{{-- PLAN DE MANEJO --}}
@php $pm = $historia->plan_manejo ?? []; @endphp
@if(!empty($pm))
<div class="section">
    <div class="section-title">Plan de Manejo</div>
    <div class="section-body">
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width:50%">
                    <div class="grid-label">Próxima Cita</div>
                    <div class="grid-value">{{ isset($pm['fecha_proxima_cita']) ? \Carbon\Carbon::parse($pm['fecha_proxima_cita'])->format('d/m/Y') : '—' }}</div>
                </div>
                <div class="grid-cell" style="width:50%">
                    <div class="grid-label">Lugar de Atención del Parto</div>
                    <div class="grid-value">{{ $pm['lugar_atencion_parto'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        @if(!empty($pm['suplementos']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">SUPLEMENTOS</div>
        @foreach($pm['suplementos'] as $sup)
        <span class="tag tag-ok">{{ is_array($sup) ? ($sup['nombre'] ?? json_encode($sup)) : $sup }}</span>
        @endforeach
        @endif
        @if(!empty($pm['vacunas']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">VACUNAS</div>
        @foreach($pm['vacunas'] as $vac)
        <span class="tag">{{ is_array($vac) ? ($vac['nombre'] ?? json_encode($vac)) : $vac }}</span>
        @endforeach
        @endif
        @if(!empty($pm['recomendaciones']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">RECOMENDACIONES</div>
        @foreach($pm['recomendaciones'] as $rec)
        <div style="padding:2px 0;font-size:10px;color:#334155;">• {{ $rec }}</div>
        @endforeach
        @endif
        @if(!empty($pm['signos_alarma']))
        <hr class="divider">
        <div class="grid-label" style="margin-bottom:3px;">SIGNOS DE ALARMA A VIGILAR</div>
        @foreach($pm['signos_alarma'] as $sa)
        <span class="tag tag-warn">{{ $sa }}</span>
        @endforeach
        @endif
    </div>
</div>
@endif

{{-- NOTAS --}}
@if($historia->notas_evolucion || $historia->observaciones)
<div class="section">
    <div class="section-title">Notas y Observaciones</div>
    <div class="section-body">
        @if($historia->notas_evolucion)
        <div class="grid-label" style="margin-bottom:3px;">NOTAS DE EVOLUCIÓN</div>
        <div class="text-block" style="margin-bottom:8px;">{{ $historia->notas_evolucion }}</div>
        @endif
        @if($historia->observaciones)
        <div class="grid-label" style="margin-bottom:3px;">OBSERVACIONES</div>
        <div class="text-block">{{ $historia->observaciones }}</div>
        @endif
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
                <div style="font-size:10px;font-weight:700;color:{!! $clinica->color_principal ?? '#0A1628' !!};">
                    {{ $historia->medico_nombre ?? ($user->nombre_con_titulo ?? ($user->name ?? '')) }}
                </div>
                @if($historia->medico_cedula ?? null)
                <div style="font-size:9px;color:#64748b;">Cédula: {{ $historia->medico_cedula }}</div>
                @elseif(($user->cedula_especialista ?? null))
                <div style="font-size:9px;color:#64748b;">Cédula: {{ $user->cedula_especialista }}</div>
                @endif
                @if($historia->medico_especialidad ?? null)
                <div style="font-size:9px;color:#64748b;">{{ $historia->medico_especialidad }}</div>
                @endif
                <div style="font-size:9px;color:#64748b;margin-top:2px;">Firma del médico</div>
            </div>
        </td>
        <td style="width:25%;"></td>
    </tr>
</table>

</body>
</html>
