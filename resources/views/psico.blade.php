<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota Psicológica</title>
    <style>
        /* === RESET & BASE === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1e293b;
            background: #ffffff;
            padding: 15px 25px;
            margin: 0;
        }

        /* === HEADER === */
        .header {
            width: 100%;
            background: #0A1628;
            border-radius: 8px;
            margin-bottom: 12px;
            padding: 10px 15px;
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

        .header-title-cell {
            padding-left: 10px;
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
        }

        .header-badge-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.7);
        }

        .header-badge-value {
            font-size: 12px;
            font-weight: 700;
        }

        .header-date {
            font-size: 9px;
            color: rgba(255,255,255,0.7);
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

        .patient-medications {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }

        .patient-medications-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .patient-medications-value {
            font-size: 10px;
            color: #334155;
        }

        .patient-registro-old {
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }

        .patient-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
        }

        .patient-detail {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .patient-detail-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .patient-detail-value {
            font-size: 11px;
            font-weight: 600;
            color: #334155;
        }

        .patient-medications {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }

        .patient-medications-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .patient-medications-value {
            font-size: 11px;
            color: #334155;
        }

        /* === SECTIONS === */
        .section {
            margin-bottom: 10px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .section-icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #0A1628;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-content {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #8b5cf6;
            border-radius: 0 8px 8px 0;
            padding: 8px 12px;
        }

        .section-content p {
            color: #334155;
            font-size: 10px;
            line-height: 1.4;
            text-align: justify;
        }

        /* === CLINICAL GRID (compact layout) === */
        .clinical-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }

        .clinical-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            min-height: 0;
        }

        .clinical-item.full-width {
            grid-column: span 2;
        }

        .clinical-label {
            font-size: 9px;
            font-weight: 700;
            color: #8b5cf6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 2px;
        }

        .clinical-value {
            font-size: 10px;
            color: #334155;
            line-height: 1.3;
            max-height: 45px;
            overflow: hidden;
        }

        .clinical-value.large {
            max-height: 60px;
        }

        /* === TWO COLUMNS === */
        .two-columns {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }

        /* === QUESTIONNAIRE TABLE === */
        .questionnaire-section {
            margin-top: 0;
            margin-bottom: 20px;
            page-break-before: always;
        }

        .questionnaire-title {
            background: linear-gradient(135deg, #0A1628 0%, #1e3a5f 100%);
            color: white;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .questionnaire-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .questionnaire-table thead th {
            background: #f1f5f9;
            color: #0A1628;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        .questionnaire-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .questionnaire-table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .questionnaire-table tbody tr:hover {
            background: #f1f5f9;
        }

        .category-cell {
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
            color: white;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .question-cell {
            color: #334155;
        }

        .answer-cell {
            font-weight: 600;
            color: #0A1628;
        }

        .answer-cell .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
        }

        .badge-yes {
            background: #dcfce7;
            color: #166534;
        }

        .badge-no {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background: #e0e7ff;
            color: #3730a3;
        }

        /* === FOOTER === */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #0A1628;
        }

        .professional-info {
            margin-bottom: 15px;
        }

        .professional-name {
            font-size: 13px;
            font-weight: 700;
            color: #0A1628;
        }

        .professional-cedula {
            font-size: 10px;
            color: #64748b;
        }

        .clinic-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }

        .clinic-name {
            font-size: 12px;
            font-weight: 700;
            color: #3b82f6;
        }

        .clinic-contact {
            font-size: 10px;
            color: #64748b;
        }

        .clinic-contact .email {
            color: #ef4444;
        }

        /* === PRINT STYLES === */
        @media print {
            body {
                padding: 10px;
            }
            
            .page-break {
                page-break-before: always;
            }

            .questionnaire-section {
                page-break-before: always;
            }

            .questionnaire-table tbody tr:hover {
                background: transparent;
            }
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
            color: #8b5cf6;
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
            padding-bottom: 50px;
        }

        /* === HELPER TEXT === */
        .empty-text {
            color: #94a3b8;
            font-style: italic;
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
            <tr>
                <td colspan="2" style="text-align: center; padding-top: 4px; font-size: 7px; color: #94a3b8;">
                    <span>Generado con</span> <strong style="color: #0A1628;">Lynkamed</strong>
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
                    <div class="header-title">Nota Psicológica</div>
                    <div class="header-subtitle">Evaluación y seguimiento psicológico</div>
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
                <td><span class="patient-label">Peso:</span> <span class="patient-value">{{ $paciente->peso }} kg</span></td>
                <td><span class="patient-label">Talla:</span> <span class="patient-value">{{ $paciente->talla }} cm</span></td>
            </tr>
            <tr>
                <td><span class="patient-label">Teléfono:</span> <span class="patient-value">{{ $paciente->telefono }}</span></td>
                <td colspan="3"><span class="patient-label">Email:</span> <span class="patient-value">{{ $paciente->email }}</span></td>
            </tr>
        </table>
        @if($paciente->medicamentos)
        <div class="patient-medications">
            <div class="patient-medications-label">Medicamentos</div>
            <div class="patient-medications-value">{{ $paciente->medicamentos }}</div>
        </div>
        @endif
    </div>

    <!-- CLINICAL SECTIONS (compact grid) -->
    <div class="clinical-grid">
        <div class="clinical-item full-width">
            <div class="clinical-label">Motivo de Consulta</div>
            <div class="clinical-value large">{{ $data->motivo_consulta ?? 'Sin observaciones registradas.' }}</div>
        </div>

        <div class="clinical-item">
            <div class="clinical-label">Antecedentes Médicos</div>
            <div class="clinical-value">{{ $data->antecedentes_medicos ?? 'Sin observaciones registradas.' }}</div>
        </div>

        <div class="clinical-item">
            <div class="clinical-label">Cirugías Previas</div>
            <div class="clinical-value">{{ $data->cirugias_previas ?? 'Sin observaciones registradas.' }}</div>
        </div>

        <div class="clinical-item">
            <div class="clinical-label">Tratamiento Actual</div>
            <div class="clinical-value">{{ $data->tratamiento_actual ?? 'Sin observaciones registradas.' }}</div>
        </div>

        <div class="clinical-item">
            <div class="clinical-label">Antecedentes Familiares</div>
            <div class="clinical-value">{{ $data->antecedentes_familiares ?? 'Sin observaciones registradas.' }}</div>
        </div>

        <div class="clinical-item">
            <div class="clinical-label">Aspectos Sociales</div>
            <div class="clinical-value">{{ $data->aspectos_sociales ?? 'Sin observaciones registradas.' }}</div>
        </div>

        <div class="clinical-item">
            <div class="clinical-label">Escalas Utilizadas</div>
            <div class="clinical-value">{{ $data->escalas_utilizadas ?? 'Sin observaciones registradas.' }}</div>
        </div>

        <div class="clinical-item full-width">
            <div class="clinical-label">Síntomas Actuales</div>
            <div class="clinical-value">{{ $data->sintomas_actuales ?? 'Sin observaciones registradas.' }}</div>
        </div>

        <div class="clinical-item">
            <div class="clinical-label">Plan de Tratamiento</div>
            <div class="clinical-value">{{ $data->plan_tratamiento ?? 'Sin observaciones registradas.' }}</div>
        </div>

        <div class="clinical-item">
            <div class="clinical-label">Seguimiento</div>
            <div class="clinical-value">{{ $data->seguimiento ?? 'Sin observaciones registradas.' }}</div>
        </div>
    </div>

    <!-- QUALITY OF LIFE QUESTIONNAIRE -->
    <div class="questionnaire-section">
        <div class="questionnaire-title">Evaluación de Calidad de Vida</div>
        <table class="questionnaire-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Sección</th>
                    <th style="width: 55%;">Pregunta</th>
                    <th style="width: 25%;">Respuesta</th>
                </tr>
            </thead>
            <tbody>
                <!-- Salud Física -->
                <tr>
                    <td class="category-cell" rowspan="4">Salud Física</td>
                    <td class="question-cell">¿Cómo calificarías tu estado de salud general?</td>
                    <td class="answer-cell">
                        @if($data->calif_salud === "1") <span class="badge badge-info">Excelente</span>
                        @elseif($data->calif_salud === "2") <span class="badge badge-info">Bueno</span>
                        @elseif($data->calif_salud === "3") <span class="badge badge-info">Regular</span>
                        @elseif($data->calif_salud === "4") <span class="badge badge-info">Malo</span>
                        @else <span class="empty-text">N/A</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Realizas ejercicio físico regularmente?</td>
                    <td class="answer-cell">
                        @if($data->realizas_ejercicio === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">Si es así, ¿con qué frecuencia?</td>
                    <td class="answer-cell">
                        @if($data->ejercicio_frecuencia === "1") <span class="badge badge-info">Diariamente</span>
                        @elseif($data->ejercicio_frecuencia === "2") <span class="badge badge-info">3-4 veces/semana</span>
                        @elseif($data->ejercicio_frecuencia === "3") <span class="badge badge-info">1-2 veces/semana</span>
                        @elseif($data->ejercicio_frecuencia === "4") <span class="badge badge-info">Raramente</span>
                        @else <span class="empty-text">N/A</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Tienes alguna condición médica crónica?</td>
                    <td class="answer-cell">
                        @if($data->condicion_medica === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>

                <!-- Alimentación -->
                <tr>
                    <td class="category-cell" rowspan="3">Alimentación</td>
                    <td class="question-cell">¿Cómo describirías tu dieta diaria?</td>
                    <td class="answer-cell">
                        @if($data->dieta_diaria === "1") <span class="badge badge-info">Equilibrada</span>
                        @elseif($data->dieta_diaria === "2") <span class="badge badge-info">Alta en grasas</span>
                        @elseif($data->dieta_diaria === "3") <span class="badge badge-info">Alta en azúcares</span>
                        @elseif($data->dieta_diaria === "4") <span class="badge badge-info">Insuficiente</span>
                        @else <span class="empty-text">N/A</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Comes frutas y verduras diariamente?</td>
                    <td class="answer-cell">
                        @if($data->frutas_verduras === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Con qué frecuencia comes fuera de casa?</td>
                    <td class="answer-cell">
                        @if($data->frecuencia_comida === "1") <span class="badge badge-info">Diariamente</span>
                        @elseif($data->frecuencia_comida === "2") <span class="badge badge-info">Semanalmente</span>
                        @elseif($data->frecuencia_comida === "3") <span class="badge badge-info">Raramente</span>
                        @else <span class="empty-text">N/A</span>
                        @endif
                    </td>
                </tr>

                <!-- Salud Mental y Emocional -->
                <tr>
                    <td class="category-cell" rowspan="3">Salud Mental y Emocional</td>
                    <td class="question-cell">En una escala del 1 al 10, ¿cómo calificarías tu nivel de estrés actual?</td>
                    <td class="answer-cell">
                        @if($data->estres_nivel)
                            <span class="badge badge-info">{{ $data->estres_nivel }}/10</span>
                        @else
                            <span class="empty-text">N/A</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Te sientes feliz la mayor parte del tiempo?</td>
                    <td class="answer-cell">
                        @if($data->feliz === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Tienes apoyo emocional de amigos o familiares?</td>
                    <td class="answer-cell">
                        @if($data->apoyo_emocional === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>

                <!-- Vida Social -->
                <tr>
                    <td class="category-cell" rowspan="3">Vida Social</td>
                    <td class="question-cell">¿Con qué frecuencia te reúnes con amigos o familiares?</td>
                    <td class="answer-cell">
                        @if($data->frecuencia_reuniones === "1") <span class="badge badge-info">Diariamente</span>
                        @elseif($data->frecuencia_reuniones === "2") <span class="badge badge-info">Semanalmente</span>
                        @elseif($data->frecuencia_reuniones === "3") <span class="badge badge-info">Mensualmente</span>
                        @elseif($data->frecuencia_reuniones === "4") <span class="badge badge-info">Raramente</span>
                        @else <span class="empty-text">N/A</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Participas en actividades comunitarias o grupos sociales?</td>
                    <td class="answer-cell">
                        @if($data->actividades_comunitarias === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Te sientes parte de tu comunidad?</td>
                    <td class="answer-cell">
                        @if($data->comunidad === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>

                <!-- Bienestar Financiero -->
                <tr>
                    <td class="category-cell" rowspan="3">Bienestar Financiero</td>
                    <td class="question-cell">¿Cómo calificarías tu situación financiera actual?</td>
                    <td class="answer-cell">
                        @if($data->situa_financiera === "1") <span class="badge badge-info">Excelente</span>
                        @elseif($data->situa_financiera === "2") <span class="badge badge-info">Buena</span>
                        @elseif($data->situa_financiera === "3") <span class="badge badge-info">Regular</span>
                        @elseif($data->situa_financiera === "4") <span class="badge badge-info">Mala</span>
                        @else <span class="empty-text">N/A</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Te sientes seguro económicamente?</td>
                    <td class="answer-cell">
                        @if($data->seguro_economico === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Tienes suficientes ingresos para cubrir tus necesidades básicas?</td>
                    <td class="answer-cell">
                        @if($data->ingresos_suficientes === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>

                <!-- Trabajo y Satisfacción Laboral -->
                <tr>
                    <td class="category-cell" rowspan="3">Trabajo y Satisfacción Laboral</td>
                    <td class="question-cell">¿Estás satisfecho con tu trabajo actual?</td>
                    <td class="answer-cell">
                        @if($data->trabajo_actual === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Consideras que tienes un buen equilibrio entre el trabajo y la vida personal?</td>
                    <td class="answer-cell">
                        @if($data->equilibrio_trabajo === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Recibes reconocimiento por tu trabajo?</td>
                    <td class="answer-cell">
                        @if($data->reconocimiento === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>

                <!-- Consumo de Sustancias -->
                <tr>
                    <td class="category-cell" rowspan="3">Consumo de Sustancias</td>
                    <td class="question-cell">¿Fumas tabaco o utilizas productos de tabaco?</td>
                    <td class="answer-cell">
                        @if($data->tabaco_consumo === 1) <span class="badge badge-yes">Sí</span>
                        @else <span class="badge badge-no">No</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Consumes alcohol y, de ser así, con qué frecuencia y en qué cantidades?</td>
                    <td class="answer-cell">
                        @if($data->alchol_consumo)
                            {{ $data->alchol_consumo }}
                        @else
                            <span class="empty-text">N/A</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="question-cell">¿Utilizas drogas recreativas o tienes un historial de abuso de sustancias?</td>
                    <td class="answer-cell">
                        @if($data->drogas_recreativas)
                            {{ $data->drogas_recreativas }}
                        @else
                            <span class="empty-text">N/A</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- PROFESSIONAL SIGNATURE -->
    <div class="footer">
        <div class="professional-info">
            <div class="professional-name">Psicólogo: {{ $data->psicologo }}</div>
            <div class="professional-cedula">Cédula Profesional: {{ $data->cedula_psicologo }}</div>
        </div>
    </div>
    
    </div><!-- end content-wrapper -->
</body>
</html>
