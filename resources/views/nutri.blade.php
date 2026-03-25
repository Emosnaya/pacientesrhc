<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Valoración Nutricional</title>
    <style>
        /* === RESET & BASE === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            /* DejaVu Sans viene con DomPDF; fuentes core no renderizan ✔ */
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #1e293b;
            background: #ffffff;
            padding: 10px 20px;
        }

        /* === COLORS === */
        :root {
            --primary: #0A1628;
            --primary-light: #1e3a5f;
            --accent: #3b82f6;
            --accent-light: #60a5fa;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-500: #64748b;
            --gray-700: #334155;
            --gray-900: #0f172a;
        }

        /* === HEADER === */
        .header {
            width: 100%;
            background: #0A1628;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 8px 12px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .header-logo-cell {
            width: 60px;
            padding-right: 12px !important;
        }

        .header-logo {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 6px;
            padding: 5px;
            text-align: center;
        }

        .header-logo img {
            max-height: 35px;
            max-width: 35px;
        }

        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            font-size: 10px;
            color: #94a3b8;
        }

        .header-meta-cell {
            text-align: right;
            width: 120px;
        }

        .header-badge {
            background: rgba(255,255,255,0.15);
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 4px;
        }

        .header-badge-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
        }

        .header-badge-value {
            font-size: 12px;
            font-weight: 700;
            color: white;
        }

        .header-date {
            font-size: 9px;
            color: #94a3b8;
        }

        /* === PATIENT INFO === */
        .patient-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .patient-table {
            width: 100%;
            border-collapse: collapse;
        }

        .patient-table td {
            padding: 3px 8px;
            font-size: 10px;
        }

        .patient-name {
            font-size: 14px;
            font-weight: 700;
            color: #0A1628;
            margin-bottom: 8px;
        }

        .patient-label {
            color: #64748b;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }

        .patient-value {
            font-weight: 600;
            color: #334155;
        }

        .patient-diagnosis {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }

        .patient-diagnosis-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .patient-diagnosis-value {
            font-size: 10px;
            color: #334155;
        }

        /* === METRICS TABLE === */
        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .metrics-table td {
            width: 16.66%;
            text-align: center;
            padding: 10px 5px;
            border: 1px solid #e2e8f0;
            background: white;
        }

        .metrics-table td.highlight {
            background: #0A1628;
            color: white;
            border-color: #0A1628;
        }

        .metric-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .metrics-table td.highlight .metric-label {
            color: #94a3b8;
        }

        .metric-value {
            font-size: 16px;
            font-weight: 700;
            color: #0A1628;
        }

        .metrics-table td.highlight .metric-value {
            color: white;
        }

        .metric-unit {
            font-size: 9px;
            font-weight: 400;
            color: #64748b;
        }

        .metrics-table td.highlight .metric-unit {
            color: #94a3b8;
        }

        /* === SECTIONS === */
        .section {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #0A1628;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 2px solid #0A1628;
        }

        .section-content {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
        }

        /* === DATA TABLE === */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .data-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
        }

        .data-row {
            padding: 5px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 10px;
        }

        .data-row:last-child {
            border-bottom: none;
        }

        .data-label {
            color: #64748b;
            font-size: 9px;
        }

        .data-value {
            font-weight: 600;
            color: #334155;
        }

        /* === DIAGNOSIS BOX === */
        .diagnosis-box {
            background: #0A1628;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            color: white;
            page-break-inside: avoid;
        }

        .diagnosis-title {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .diagnosis-table {
            width: 100%;
            border-collapse: collapse;
        }

        .diagnosis-table td {
            padding: 4px 0;
            font-size: 9px;
            vertical-align: top;
        }

        .diagnosis-check {
            width: 14px;
            height: 14px;
            border: 1px solid #64748b;
            border-radius: 3px;
            text-align: center;
            line-height: 12px;
            font-size: 9px;
            display: inline-block;
            margin-right: 6px;
        }

        .diagnosis-check.active {
            background: #10b981;
            border-color: #10b981;
        }

        /* === OBSERVATIONS === */
        .observations-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #3b82f6;
            border-radius: 0 6px 6px 0;
            padding: 10px 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .observations-title {
            font-size: 11px;
            font-weight: 700;
            color: #0A1628;
            margin-bottom: 5px;
        }

        .observations-text {
            color: #334155;
            font-size: 10px;
            line-height: 1.4;
        }

        /* === RECOMMENDATIONS === */
        .recommendations-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .recommendations-header {
            background: #10b981;
            color: white;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .recommendations-list {
            padding: 8px 12px;
        }

        .recommendation-item {
            padding: 5px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 9px;
        }

        .recommendation-item:last-child {
            border-bottom: none;
        }

        .recommendation-check {
            width: 12px;
            height: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 2px;
            display: inline-block;
            text-align: center;
            line-height: 10px;
            font-size: 8px;
            color: white;
            margin-right: 6px;
        }

        .recommendation-check.active {
            background: #10b981;
            border-color: #10b981;
        }

        /* === DIET SECTION === */
        .diet-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .diet-header {
            background: #f59e0b;
            color: white;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
        }

        .diet-table {
            width: 100%;
            border-collapse: collapse;
        }

        .diet-table td {
            width: 50%;
            padding: 8px 10px;
            vertical-align: top;
            border: 1px solid #e2e8f0;
            background: white;
        }

        .diet-icon {
            width: 24px;
            height: 24px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 6px;
        }

        .diet-icon img {
            width: 20px;
            height: 20px;
        }

        .diet-name {
            font-weight: 700;
            color: #0A1628;
            font-size: 10px;
        }

        .diet-desc {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        /* === FOOTER === */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #0A1628;
        }

        .professional-info {
            margin-bottom: 10px;
        }

        .professional-name {
            font-size: 12px;
            font-weight: 700;
            color: #0A1628;
        }

        .professional-cedula {
            font-size: 9px;
            color: #64748b;
        }

        /* === PAGE FOOTER === */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 25px;
            background: white;
            border-top: 2px solid #0A1628;
            font-size: 9px;
        }

        .page-footer-table {
            width: 100%;
        }

        .page-footer .clinic-name {
            font-weight: 700;
            color: #3b82f6;
        }

        .page-footer .clinic-contact {
            text-align: right;
            color: #64748b;
        }

        .page-footer .email {
            color: #ef4444;
        }

        /* Space for fixed footer */
        .content-wrapper {
            padding-bottom: 40px;
        }

        /* === PRINT STYLES === */
        @media print {
            body {
                padding: 10px;
            }
            
            .page-break {
                page-break-before: always;
            }

            .section, .diagnosis-box, .observations-box, .recommendations-box, .diet-box {
                page-break-inside: avoid;
            }
        }

        /* === INDICATOR TABLE === */
        .indicator-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .indicator-table td {
            width: 33.33%;
            text-align: center;
            padding: 8px 5px;
            border: 1px solid #e2e8f0;
            background: white;
        }

        .indicator-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .indicator-value {
            font-size: 14px;
            font-weight: 700;
            color: #0A1628;
        }

        .indicator-value small {
            font-size: 8px;
            color: #64748b;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <!-- PAGE FOOTER (fixed) -->
    <div class="page-footer">
        <table class="page-footer-table">
            <tr>
                <td class="clinic-name">{{ $clinica->nombre ?? 'Clínica' }}</td>
                <td class="clinic-contact">
                    {{ $clinica->telefono ?? '' }}
                    @if($clinica->email ?? null)
                        | <span class="email">{{ $clinica->email }}</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="content-wrapper">
    <!-- HEADER -->
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
                <td style="padding-left: 10px;">
                    <div class="header-title">Valoración Nutricional</div>
                    <div class="header-subtitle">Evaluación integral del estado nutricional</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Registro</div>
                        <div class="header-badge-value">#{{ $paciente->registro }}</div>
                    </div>
                    <div class="header-date">{{ date('d/m/Y', strtotime($data->created_at)) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- PATIENT INFO -->
    <div class="patient-card">
        <div class="patient-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
        <table class="patient-table">
            <tr>
                <td><span class="patient-label">Edad:</span> <span class="patient-value">{{ $paciente->edad }} años</span></td>
                <td><span class="patient-label">Género:</span> <span class="patient-value">{{ $paciente->genero == 1 ? 'Masculino' : 'Femenino' }}</span></td>
                <td><span class="patient-label">Teléfono:</span> <span class="patient-value">{{ $paciente->telefono }}</span></td>
                <td><span class="patient-label">Email:</span> <span class="patient-value">{{ $paciente->email }}</span></td>
            </tr>
        </table>
        @if($paciente->diagnostico)
        <div class="patient-diagnosis">
            <div class="patient-diagnosis-label">Diagnóstico</div>
            <div class="patient-diagnosis-value">{{ $paciente->diagnostico }}</div>
        </div>
        @endif
    </div>

    <!-- METRICS -->
    <table class="metrics-table">
        <tr>
            <td class="highlight">
                <div class="metric-label">Peso</div>
                <div class="metric-value">{{ $paciente->peso }} <span class="metric-unit">kg</span></div>
            </td>
            <td class="highlight">
                <div class="metric-label">Talla</div>
                <div class="metric-value">{{ $paciente->talla }} <span class="metric-unit">cm</span></div>
            </td>
            <td class="highlight">
                <div class="metric-label">IMC</div>
                <div class="metric-value">{{ round($paciente->imc, 2) }}</div>
            </td>
            <td>
                <div class="metric-label">Presión</div>
                <div class="metric-value">{{ $data->sistolica }}/{{ $data->diastolica }}</div>
            </td>
            <td>
                <div class="metric-label">Cintura</div>
                <div class="metric-value">{{ $paciente->cintura }} <span class="metric-unit">cm</span></div>
            </td>
            <td>
                <div class="metric-label">Estado</div>
                <div class="metric-value" style="font-size: 11px;">{{ $data->estado ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <!-- TWO COLUMNS - TABLE -->
    <table class="data-table">
        <tr>
            <!-- ACTIVIDAD FÍSICA -->
            <td>
                <div class="section">
                    <div class="section-title">Actividad Física</div>
                    <div class="section-content">
                        <div class="data-row">
                            <span class="data-label">Realiza actividad:</span>
                            <span class="data-value">{{ $data->actividad ?? 'No' }}</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Días por semana:</span>
                            <span class="data-value">{{ $data->actividadDias ?? 0 }} días</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Minutos al día:</span>
                            <span class="data-value">{{ $data->minutosDia ?? 0 }} min</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Fórmula:</span>
                            <span class="data-value">{{ $data->formula ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </td>
            <!-- CONTROL DE MEDICAMENTOS -->
            <td>
                <div class="section">
                    <div class="section-title">Control de Medicamentos</div>
                    <div class="section-content">
                        <div class="data-row">
                            <span class="data-label">Control glucosa:</span>
                            <span class="data-value">@if ($data->Controlglucosa === "1" || $data->Controlglucosa === "true") Sí @else No @endif</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Control lípidos:</span>
                            <span class="data-value">@if ($data->lipidos === "1" || $data->lipidos === "true") Sí @else No @endif</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Control peso:</span>
                            <span class="data-value">@if ($data->controlPeso === "1" || $data->controlPeso === "true") Sí @else No @endif</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Control presión:</span>
                            <span class="data-value">@if ($data->controlPresion === "1" || $data->controlPresion === "true") Sí @else No @endif</span>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- INDICADORES BIOQUÍMICOS -->
    <div class="section">
        <div class="section-title">Indicadores Bioquímicos</div>
        <table class="indicator-table">
            <tr>
                <td>
                    <div class="indicator-label">Glucosa</div>
                    <div class="indicator-value">{{ $data->glucosa ?? 0 }} <small>mg/dL</small></div>
                </td>
                <td>
                    <div class="indicator-label">Triglicéridos</div>
                    <div class="indicator-value">{{ $data->trigliceridos ?? 0 }} <small>mg/dL</small></div>
                </td>
                <td>
                    <div class="indicator-label">HDL</div>
                    <div class="indicator-value">{{ $data->hdl ?? 0 }} <small>mg/dL</small></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="indicator-label">Colesterol</div>
                    <div class="indicator-value">{{ $data->colesterol ?? 0 }} <small>mg/dL</small></div>
                </td>
                <td>
                    <div class="indicator-label">LDL</div>
                    <div class="indicator-value">{{ $data->ldl ?? 0 }} <small>mg/dL</small></div>
                </td>
                <td>
                    <div class="indicator-label">Otro</div>
                    <div class="indicator-value" style="font-size: 11px;">{{ $data->otro ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- RECORDATORIO 24H -->
    @if($data->recomendacion)
    <div class="observations-box">
        <div class="observations-title">Recordatorio de 24 horas</div>
        <div class="observations-text">{{ $data->recomendacion }}</div>
    </div>
    @endif

    <!-- DIAGNÓSTICO NUTRICIONAL -->
    <div class="diagnosis-box">
        <div class="diagnosis-title">Diagnóstico Nutricional</div>
        <table class="diagnosis-table">
            <tr>
                <td><span class="diagnosis-check @if($data->diagnostico === "1") active @endif">@if($data->diagnostico === "1")✔@endif</span> Paciente en Obesidad que cumple criterios para Síndrome Metabólico.</td>
                <td><span class="diagnosis-check @if($data->diagnostico === "2") active @endif">@if($data->diagnostico === "2")✔@endif</span> Paciente en Sobrepeso que cumple criterios para Síndrome Metabólico.</td>
            </tr>
            <tr>
                <td><span class="diagnosis-check @if($data->diagnostico === "3") active @endif">@if($data->diagnostico === "3")✔@endif</span> Paciente en Sobrepeso sin Síndrome Metabólico.</td>
                <td><span class="diagnosis-check @if($data->diagnostico === "4") active @endif">@if($data->diagnostico === "4")✔@endif</span> Paciente en Obesidad sin Síndrome Metabólico.</td>
            </tr>
            <tr>
                <td><span class="diagnosis-check @if($data->diagnostico === "5") active @endif">@if($data->diagnostico === "5")✔@endif</span> Paciente en Normopeso.</td>
                <td><span class="diagnosis-check @if($data->diagnostico === "6") active @endif">@if($data->diagnostico === "6")✔@endif</span> Paciente en Normopeso que cumple criterios para Síndrome Metabólico.</td>
            </tr>
            <tr>
                <td><span class="diagnosis-check @if($data->diagnostico === "7") active @endif">@if($data->diagnostico === "7")✔@endif</span> Paciente en Infrapeso, se recomienda visita con Nutrición.</td>
                <td><span class="diagnosis-check @if($data->diagnostico === "8") active @endif">@if($data->diagnostico === "8")✔@endif</span> Paciente en Obesidad Mórbida.</td>
            </tr>
        </table>
    </div>

    <!-- OBSERVACIONES -->
    <div class="observations-box">
        <div class="observations-title">Observaciones</div>
        <div class="observations-text">{{ $data->observaciones ?? 'Sin observaciones.' }}</div>
    </div>

    <!-- RECOMENDACIONES -->
    @php
        $recomendaciones = json_decode($data->recomendaciones);
    @endphp
    <div class="recommendations-box">
        <div class="recommendations-header">Recomendaciones Específicas</div>
        <div class="recommendations-list">
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[0]) && $recomendaciones[0] === true) active @endif">@if(isset($recomendaciones[0]) && $recomendaciones[0] === true)✔@endif</div>
                <span>Evitar el consumo de azúcares (azúcar de mesa, mascabado, mieles, mermeladas, cajeta, lechera) así como productos con azúcares simples.</span>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[1]) && $recomendaciones[1] === true) active @endif">@if(isset($recomendaciones[1]) && $recomendaciones[1] === true)✔@endif</div>
                <span>Evitar el consumo de bebidas azucaradas e intercambiarlo por agua simple.</span>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[2]) && $recomendaciones[2] === true) active @endif">@if(isset($recomendaciones[2]) && $recomendaciones[2] === true)✔@endif</div>
                <span>Formar horarios para las tres principales comidas del día.</span>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[3]) && $recomendaciones[3] === true) active @endif">@if(isset($recomendaciones[3]) && $recomendaciones[3] === true)✔@endif</div>
                <span>Realizar 3 comidas principales al día.</span>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[4]) && $recomendaciones[4] === true) active @endif">@if(isset($recomendaciones[4]) && $recomendaciones[4] === true)✔@endif</div>
                <span>Aumentar el consumo de agua simple (la sed es la señal más confiable de que necesita agua).</span>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[5]) && $recomendaciones[5] === true) active @endif">@if(isset($recomendaciones[5]) && $recomendaciones[5] === true)✔@endif</div>
                <span>Mantener actividad física.</span>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[6]) && $recomendaciones[6] === true) active @endif">@if(isset($recomendaciones[6]) && $recomendaciones[6] === true)✔@endif</div>
                <span>Iniciar actividad física moderada-ligera (caminar, trotar, bicicleta, natación) por al menos 30 minutos al día 5 días a la semana.</span>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[7]) && $recomendaciones[7] === true) active @endif">@if(isset($recomendaciones[7]) && $recomendaciones[7] === true)✔@endif</div>
                <span>Aumentar actividad física moderada-ligera por al menos 150 minutos a la semana o 75 minutos de actividad vigorosa.</span>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[8]) && $recomendaciones[8] === true) active @endif">@if(isset($recomendaciones[8]) && $recomendaciones[8] === true)✔@endif</div>
                <span>A la hora de la comida elegir una opción entre arroz, frijoles, pasta o papa como acompañamiento.</span>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[9]) && $recomendaciones[9] === true) active @endif">@if(isset($recomendaciones[9]) && $recomendaciones[9] === true)✔@endif</div>
                <span>Disminuir la cantidad de cereales (arroz, tortilla, pan, papa, pasta).</span>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-check @if(isset($recomendaciones[10]) && $recomendaciones[10] === true) active @endif">@if(isset($recomendaciones[10]) && $recomendaciones[10] === true)✔@endif</div>
                <span>Siempre y cuando realice 1 hora o más de ejercicio: consumir una colación antes de la actividad y otra después.</span>
            </div>
        </div>
    </div>

    <!-- DIETA BALANCEADA -->
    <div class="diet-box">
        <div class="diet-header">Una dieta balanceada contiene</div>
        <table class="diet-table">
            <tr>
                <td>
                    <span class="diet-icon" style="background: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-block; text-align: center; line-height: 20px; font-size: 10px; font-weight: bold;">P</span>
                    <span class="diet-name">Proteína</span>
                    <div class="diet-desc">Variedad de carnes, leguminosas, sin freír ni empanizar.</div>
                </td>
                <td>
                    <span class="diet-icon" style="background: #3b82f6; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-block; text-align: center; line-height: 20px; font-size: 10px; font-weight: bold;">L</span>
                    <span class="diet-name">Lácteos</span>
                    <div class="diet-desc">Libres o bajos en grasa.</div>
                </td>
                <td>
                    <span class="diet-icon" style="background: #f59e0b; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-block; text-align: center; line-height: 20px; font-size: 10px; font-weight: bold;">A</span>
                    <span class="diet-name">Aceites</span>
                    <div class="diet-desc">Vegetales, evitando freír.</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="diet-icon" style="background: #8b5cf6; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-block; text-align: center; line-height: 20px; font-size: 10px; font-weight: bold;">C</span>
                    <span class="diet-name">Cereales</span>
                    <div class="diet-desc">Granos y derivados en cantidades moderadas.</div>
                </td>
                <td>
                    <span class="diet-icon" style="background: #ec4899; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-block; text-align: center; line-height: 20px; font-size: 10px; font-weight: bold;">F</span>
                    <span class="diet-name">Frutas</span>
                    <div class="diet-desc">Frescas y variadas en color.</div>
                </td>
                <td>
                    <span class="diet-icon" style="background: #10b981; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-block; text-align: center; line-height: 20px; font-size: 10px; font-weight: bold;">V</span>
                    <span class="diet-name">Vegetales</span>
                    <div class="diet-desc">Frescos en todas sus variedades.</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="professional-info">
            <div class="professional-name">Nutriólogo: {{ $data->nutriologo }}</div>
            <div class="professional-cedula">Cédula Profesional: {{ $data->cedula_nutriologo }}</div>
        </div>
    </div>

    </div><!-- End content-wrapper -->
</body>
</html>
