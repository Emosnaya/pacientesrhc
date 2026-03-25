<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Estratificación AACVPR/EAPC</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            /* DejaVu Sans: incluida en DomPDF; Helvetica/core no dibuja ✔ ni muchos Unicode */
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.3;
            background: #ffffff;
            padding: 10px 20px;
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
            font-size: 16px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.5px;
        }
        .header-subtitle {
            font-size: 9px;
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
        
        /* === PATIENT CARD === */
        .patient-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }
        .patient-table {
            width: 100%;
            border-collapse: collapse;
        }
        .patient-table td {
            padding: 2px 6px;
            font-size: 10px;
        }
        .patient-name {
            font-size: 13px;
            font-weight: 700;
            color: #0A1628;
            margin-bottom: 6px;
        }
        .patient-label {
            color: #64748b;
            font-size: 9px;
        }
        .patient-value {
            font-weight: 600;
            color: #334155;
        }
        .patient-diagnosis {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .patient-diagnosis-label {
            font-size: 9px;
            color: #64748b;
            font-weight: 600;
        }
        
        /* Tabla de Estratificación AACVPR/EAPC */
        .risk-section {
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .risk-header {
            padding: 5px 10px;
            font-weight: bold;
            font-size: 11px;
            color: white;
        }
        .risk-header.alto {
            background-color: #dc2626;
        }
        .risk-header.moderado {
            background-color: #f59e0b;
        }
        .risk-header.bajo {
            background-color: #10b981;
        }
        
        .risk-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .risk-table th {
            background-color: #f3f4f6;
            padding: 4px 6px;
            text-align: left;
            border: 1px solid #d1d5db;
            font-weight: 600;
        }
        .risk-table td {
            padding: 3px 6px;
            border: 1px solid #e5e7eb;
        }
        .risk-table .criteria {
            width: 85%;
        }
        .risk-table .check {
            width: 15%;
            text-align: center;
        }
        .risk-subheader td {
            background-color: #e5e7eb;
            font-weight: bold;
            font-size: 9px;
            padding: 5px 6px;
            border: 1px solid #d1d5db;
        }
        .check-yes {
            color: #059669;
            font-weight: bold;
        }
        .check-no {
            color: #9ca3af;
        }
        
        /* Hallazgos clínicos */
        .findings-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .findings-table th {
            background-color: #0A1628;
            color: white;
            padding: 5px;
            text-align: left;
        }
        .findings-table td {
            padding: 4px 6px;
            border: 1px solid #e5e7eb;
        }
        .findings-table .label {
            font-weight: 600;
            background-color: #f9fafb;
            width: 25%;
        }
        
        /* Riesgo Global */
        .global-risk {
            padding: 10px 15px;
            border: 2px solid;
            text-align: center;
            border-radius: 6px;
        }
        .global-risk.bajo {
            border-color: #10b981;
            background-color: #ecfdf5;
        }
        .global-risk.moderado {
            border-color: #f59e0b;
            background-color: #fffbeb;
        }
        .global-risk.alto {
            border-color: #dc2626;
            background-color: #fef2f2;
        }
        .global-risk-label {
            font-size: 10px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 2px;
        }
        .global-risk-value {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .global-risk.bajo .global-risk-value { color: #059669; }
        .global-risk.moderado .global-risk-value { color: #d97706; }
        .global-risk.alto .global-risk-value { color: #dc2626; }
        
        /* Parámetros Iniciales */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0A1628;
            border-bottom: 2px solid #0A1628;
            padding-bottom: 3px;
            margin: 10px 0 5px 0;
        }
        
        .params-container {
            width: 100%;
            margin-bottom: 8px;
        }
        .params-box {
            display: inline-block;
            width: 32%;
            vertical-align: top;
            padding: 5px;
        }
        .params-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            border: 1px solid #d1d5db;
        }
        .params-table th {
            background-color: #f3f4f6;
            padding: 4px;
            text-align: center;
            font-weight: 600;
        }
        .params-table td {
            padding: 3px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .params-table .selected {
            background-color: #dbeafe;
            font-weight: bold;
        }
        
        /* FC Diana y datos */
        .diana-info {
            font-size: 10px;
            margin: 8px 0;
        }
        .diana-info strong {
            color: #374151;
        }
        
        /* Comentarios */
        .comments-section {
            margin-top: 10px;
            padding: 8px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        .comments-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }
        .comments-text {
            font-size: 9px;
            color: #4b5563;
        }
        
        /* === PAGE FOOTER === */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 6px 20px;
            background: white;
            border-top: 2px solid #0A1628;
            font-size: 9px;
        }
        .page-footer-table {
            width: 100%;
        }
        .page-footer .clinic-name {
            font-weight: 700;
            color: #ef4444;
        }
        .page-footer .clinic-contact {
            text-align: right;
            color: #64748b;
        }
        .content-wrapper {
            padding-bottom: 35px;
        }
        
        .check-icon {
            display: inline-block;
            width: 12px;
            height: 12px;
            background-color: #10b981;
            border-radius: 50%;
            color: white;
            font-size: 9px;
            line-height: 12px;
            text-align: center;
        }
        
        /* Firma */
        .signature-wrapper {
            position: fixed;
            bottom: 35px;
            left: 0;
            right: 0;
            text-align: center;
        }
        .signature-wrapper img {
            height: 40px;
            width: auto;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 120px;
            margin: 2px auto 0 auto;
        }
        .signature-name {
            font-size: 8px;
            color: #374151;
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
                        | {{ $clinica->email }}
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
                        @if(!empty($clinicaLogo))
                            <img src="{{ $clinicaLogo }}" alt="Logo">
                        @endif
                    </div>
                </td>
                <td style="padding-left: 10px;">
                    <div class="header-title">Estratificación AACVPR/EAPC</div>
                    <div class="header-subtitle">Evaluación de riesgo cardiovascular según guías AACVPR/EAPC</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Folio</div>
                        <div class="header-badge-value">#{{ str_pad($data->id, 5, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="header-date">{{ date('d/m/Y', strtotime($data->fecha_estratificacion ?? now())) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- PATIENT INFO -->
    <div class="patient-card">
        <div class="patient-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
        <table class="patient-table">
            <tr>
                <td><span class="patient-label">Peso:</span> <span class="patient-value">{{ $paciente->peso }} kg</span></td>
                <td><span class="patient-label">Talla:</span> <span class="patient-value">{{ $paciente->talla }} m</span></td>
                <td><span class="patient-label">Edad:</span> <span class="patient-value">{{ $paciente->edad }} años</span></td>
                <td><span class="patient-label">IMC:</span> <span class="patient-value">{{ number_format($paciente->imc, 2) }}</span></td>
                <td><span class="patient-label">Género:</span> <span class="patient-value">{{ $paciente->genero == 1 ? 'Hombre' : 'Mujer' }}</span></td>
            </tr>
        </table>
        @if($paciente->medicamentos)
        <div class="patient-diagnosis">
            <span class="patient-diagnosis-label">Medicamentos:</span> {{ $paciente->medicamentos }}
        </div>
        @endif
        @if($paciente->diagnostico)
        <div class="patient-diagnosis">
            <span class="patient-diagnosis-label">Diagnóstico:</span> {{ $paciente->diagnostico }}
        </div>
        @endif
    </div>

    @php
        $aacvprAltoGen = [
            ['aacvpr_alto_arritmias_vent_complejas_pe', 'Arritmias ventriculares complejas durante la PE o en la recuperación'],
            ['aacvpr_alto_angina_sintomas_menos_5mets', 'Angina u otros síntomas significativos (disnea, fosfenos o mareo a bajos niveles de ejercicio [&lt; 5 METs] o en la recuperación)'],
            ['aacvpr_alto_isquemia_st_ge_2mm', 'Isquemia silente de alto grado (depresión del ST ≥ 2 mm) durante la PE o recuperación'],
            ['aacvpr_alto_alteraciones_hemodinamicas_pe', 'Alteraciones hemodinámicas durante la PE (incompetencia cronotrópica o plana o respuesta hipotensiva) o recuperación (hipotensión grave postejercicio)'],
            ['aacvpr_alto_capacidad_3_4_mets', 'Capacidad funcional ≤ 3–4 METs'],
        ];
        $aacvprAltoHc = [
            ['aacvpr_alto_hc_fevi_lt_35', 'Disfunción ventricular con FEVI en reposo &lt; 35%'],
            ['aacvpr_alto_hc_choque_cardiogenico', 'Historia de choque cardiogénico'],
            ['aacvpr_alto_hc_arritmias_complejas_reposo', 'Arritmias complejas en reposo'],
            ['aacvpr_alto_hc_im_complicado_revasc_incompleta', 'IM complicado o procedimiento de revascularización incompleta'],
            ['aacvpr_alto_hc_falla_cardiaca_nyha_iii_iv', 'Falla cardiaca avanzada (NYHA clase III–IV) o que requiere soporte mecánico'],
            ['aacvpr_alto_hc_alta_temprana_post_agudo', 'Paciente dado de alta muy temprano posterior al evento agudo (&lt;1–2 semanas), incluso si no complicado, particularmente si es anciano, mujer, frágil u otro factor de progresión de ECV'],
            ['aacvpr_alto_hc_muerte_subita', 'Paciente sobreviviente de muerte súbita'],
            ['aacvpr_alto_hc_trasplante_cardiaco', 'Paciente con trasplante cardiaco reciente'],
            ['aacvpr_alto_hc_isquemia_post_evento', 'Presencia de signos o síntomas de isquemia posterior al evento o procedimiento'],
            ['aacvpr_alto_hc_desfibrilador_implantado', 'Desfibrilador automático o dispositivo implantado'],
            ['aacvpr_alto_hc_complicaciones_hospitalizacion', 'Complicaciones graves durante la hospitalización'],
            ['aacvpr_alto_hc_inestabilidad_post_agudo', 'Inestabilidad clínica, isquemia o arritmias posterior al evento agudo'],
            ['aacvpr_alto_hc_comorbilidades_graves_rcv', 'Enfermedades concomitantes graves con RCV alto (DM, IRC, EPOC)'],
            ['aacvpr_alto_hc_depresion_clinica', 'Presencia de depresión clínica'],
            ['aacvpr_alto_hc_aislamiento_social', 'Aislamiento social'],
            ['aacvpr_alto_hc_bajos_ingresos', 'Bajos ingresos'],
        ];
        $aacvprModGen = [
            ['aacvpr_mod_angina_estable_mas_7mets', 'Angina estable u otros síntomas significativos (disnea inusual, fosfenos o mareo) solo con altos niveles de esfuerzo (&gt; 7 METs)'],
            ['aacvpr_mod_isquemia_st_lt_2mm', 'Isquemia silente ligera o moderada durante la PE o recuperación (depresión del ST &lt; 2 mm)'],
            ['aacvpr_mod_capacidad_lt_5mets', 'Capacidad funcional &lt; 5 METs'],
        ];
        $aacvprModHc = [
            ['aacvpr_mod_hc_fevi_35_49', 'FEVI en reposo = 35%–49%'],
            ['aacvpr_mod_hc_hta_no_controlada', 'Hipertensión arterial no controlada'],
        ];
        $aacvprBajoGen = [
            ['aacvpr_bajo_sin_arritmia_compleja_pe', 'Ausencia de arritmia ventricular compleja durante la PE y recuperación'],
            ['aacvpr_bajo_sin_angina_significativa_pe', 'Ausencia de angina u otro síntoma significativo (disnea inusual, fosfenos o mareo) durante la PE o recuperación'],
            ['aacvpr_bajo_hemodinamica_normal_pe', 'Respuesta hemodinámica normal durante la PE y recuperación (apropiado incremento y decremento en FC, TA con la carga y la recuperación)'],
            ['aacvpr_bajo_capacidad_6_7_mets', 'Capacidad funcional 6–7 METs'],
        ];
        $aacvprBajoHc = [
            ['aacvpr_bajo_hc_fevi_gt_50', 'FEVI en reposo &gt; 50%'],
            ['aacvpr_bajo_hc_im_ok_revasc_completa', 'IM no complicado y/o revascularización completa'],
            ['aacvpr_bajo_hc_sin_arritmias_complejas_reposo', 'Ausencia de arritmias ventriculares complejas en reposo'],
            ['aacvpr_bajo_hc_sin_falla_cardiaca', 'Ausencia de falla cardiaca clínica'],
            ['aacvpr_bajo_hc_sin_isquemia_post', 'Ausencia de signos y síntomas de isquemia posterior al evento o procedimiento'],
            ['aacvpr_bajo_hc_hta_controlada', 'Hipertensión arterial controlada'],
            ['aacvpr_bajo_hc_sin_depresion', 'Ausencia de depresión clínica'],
            ['aacvpr_bajo_hc_sin_comorbilidades', 'Ausencia de comorbilidades'],
            ['aacvpr_bajo_hc_autonomia_sin_riesgo_psicosocial', 'Autonomía sin riesgo psicosocial'],
            ['aacvpr_bajo_hc_sin_dispositivos_implantados', 'Ausencia de dispositivos electrónicos implantados'],
        ];
    @endphp

    <!-- AACVPR/EAPC — criterios detallados (hoja actual) -->
    <div class="risk-section">
        <div class="risk-header alto">RIESGO ALTO</div>
        <table class="risk-table">
            @foreach($aacvprAltoGen as [$field, $label])
            <tr>
                <td class="criteria">{!! $label !!}</td>
                <td class="check">@if(!empty($data->{$field}))<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td>
            </tr>
            @endforeach
            <tr class="risk-subheader"><td colspan="2">Hallazgos clínicos — riesgo alto</td></tr>
            @foreach($aacvprAltoHc as [$field, $label])
            <tr>
                <td class="criteria">{!! $label !!}</td>
                <td class="check">@if(!empty($data->{$field}))<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="risk-section">
        <div class="risk-header moderado">RIESGO MODERADO</div>
        <table class="risk-table">
            @foreach($aacvprModGen as [$field, $label])
            <tr>
                <td class="criteria">{!! $label !!}</td>
                <td class="check">@if(!empty($data->{$field}))<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td>
            </tr>
            @endforeach
            <tr class="risk-subheader"><td colspan="2">Hallazgos clínicos — riesgo moderado</td></tr>
            @foreach($aacvprModHc as [$field, $label])
            <tr>
                <td class="criteria">{!! $label !!}</td>
                <td class="check">@if(!empty($data->{$field}))<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="risk-section">
        <div class="risk-header bajo">RIESGO BAJO</div>
        <table class="risk-table">
            @foreach($aacvprBajoGen as [$field, $label])
            <tr>
                <td class="criteria">{!! $label !!}</td>
                <td class="check">@if(!empty($data->{$field}))<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td>
            </tr>
            @endforeach
            <tr class="risk-subheader"><td colspan="2">Hallazgos clínicos — riesgo bajo</td></tr>
            @foreach($aacvprBajoHc as [$field, $label])
            <tr>
                <td class="criteria">{!! $label !!}</td>
                <td class="check">@if(!empty($data->{$field}))<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td>
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Criterios legacy (registros antiguos sin columnas aacvpr_*) -->
    @if(
        $data->alto_fevi_disminuida || $data->alto_sintomas_reposo || $data->alto_isquemia_baja_intensidad || $data->alto_arritmias_ventriculares
        || $data->alto_im_complicado || $data->alto_capacidad_menor_5mets || $data->alto_hemodinamica_anormal || $data->alto_paro_cardiaco || $data->alto_enfermedad_compleja
        || $data->moderado_fevi_moderada || $data->moderado_sintomas_moderados || $data->moderado_isquemia_moderada || $data->moderado_capacidad_5_7mets || $data->moderado_sin_automonitoreo
        || $data->bajo_fevi_preservada || $data->bajo_sin_sintomas || $data->bajo_sin_isquemia || $data->bajo_capacidad_mayor_7mets || $data->bajo_sin_arritmias
        || $data->bajo_im_no_complicado || $data->bajo_hemodinamica_normal || $data->bajo_automonitoreo_adecuado
    )
    <div class="risk-section">
        <div class="risk-header alto" style="background-color:#64748b;">CRITERIOS ANTERIORES (LEGACY)</div>
        <table class="risk-table">
            <tr><td class="criteria">FEVI severamente disminuida (&lt;40%)</td><td class="check">@if($data->alto_fevi_disminuida)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Síntomas o signos en reposo o a baja carga (≤3METs)</td><td class="check">@if($data->alto_sintomas_reposo)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Respuesta isquémica o angina (baja intensidad)</td><td class="check">@if($data->alto_isquemia_baja_intensidad)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Arritmias ventriculares complejas</td><td class="check">@if($data->alto_arritmias_ventriculares)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">IM o revascularización complicado</td><td class="check">@if($data->alto_im_complicado)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Capacidad funcional &lt;5 METs</td><td class="check">@if($data->alto_capacidad_menor_5mets)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Hemodinámica anormal al ejercicio</td><td class="check">@if($data->alto_hemodinamica_anormal)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Paro cardíaco sobrevivido</td><td class="check">@if($data->alto_paro_cardiaco)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Enfermedad coronaria compleja</td><td class="check">@if($data->alto_enfermedad_compleja)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr class="risk-subheader"><td colspan="2">Moderado (legacy)</td></tr>
            <tr><td class="criteria">FEVI moderadamente disminuida (40–49%)</td><td class="check">@if($data->moderado_fevi_moderada)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Síntomas a intensidad moderada (3–6 METs)</td><td class="check">@if($data->moderado_sintomas_moderados)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Isquemia leve a moderada</td><td class="check">@if($data->moderado_isquemia_moderada)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Capacidad 5–7 METs</td><td class="check">@if($data->moderado_capacidad_5_7mets)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Sin automonitoreo FC/síntomas</td><td class="check">@if($data->moderado_sin_automonitoreo)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr class="risk-subheader"><td colspan="2">Bajo (legacy)</td></tr>
            <tr><td class="criteria">FEVI preservada (≥50%)</td><td class="check">@if($data->bajo_fevi_preservada)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Sin síntomas en reposo o ejercicio</td><td class="check">@if($data->bajo_sin_sintomas)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Sin isquemia en PE</td><td class="check">@if($data->bajo_sin_isquemia)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Capacidad ≥7 METs</td><td class="check">@if($data->bajo_capacidad_mayor_7mets)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Sin arritmias complejas</td><td class="check">@if($data->bajo_sin_arritmias)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">IM o revascularización no complicada</td><td class="check">@if($data->bajo_im_no_complicado)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Hemodinámica normal</td><td class="check">@if($data->bajo_hemodinamica_normal)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
            <tr><td class="criteria">Automonitoreo adecuado</td><td class="check">@if($data->bajo_automonitoreo_adecuado)<span class="check-yes">✔ Sí</span>@else<span class="check-no">No</span>@endif</td></tr>
        </table>
    </div>
    @endif

    <!-- Hallazgos Clínicos -->
    @if($data->hallazgo_fevi || $data->hallazgo_mets || $data->hallazgo_sintomas || $data->hallazgo_isquemia)
    <table class="findings-table">
        <tr>
            <th colspan="4">Hallazgos Clínicos</th>
        </tr>
        <tr>
            <td class="label">FEVI:</td>
            <td>{{ $data->hallazgo_fevi ?? '-' }}%</td>
            <td class="label">METs:</td>
            <td>{{ $data->hallazgo_mets ?? '-' }}</td>
        </tr>
        @if($data->hallazgo_sintomas || $data->hallazgo_isquemia)
        <tr>
            <td class="label">Síntomas:</td>
            <td>{{ $data->hallazgo_sintomas ?? '-' }}</td>
            <td class="label">Isquemia:</td>
            <td>{{ $data->hallazgo_isquemia ?? '-' }}</td>
        </tr>
        @endif
        @if($data->hallazgo_arritmias || $data->hallazgo_hemodinamica)
        <tr>
            <td class="label">Arritmias:</td>
            <td>{{ $data->hallazgo_arritmias ?? '-' }}</td>
            <td class="label">Hemodinámica:</td>
            <td>{{ $data->hallazgo_hemodinamica ?? '-' }}</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- Parámetros Iniciales y Riesgo Global -->
    <div class="section-title">Parámetros Iniciales</div>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
        <tr>
            <td style="width: 23%; vertical-align: top; padding-right: 5px;">
                <table class="params-table">
                    <tr><th colspan="4">Grupo</th></tr>
                    <tr>
                        <td class="{{ $data->grupo === 'a' ? 'selected' : '' }}">A {{ $data->grupo === 'a' ? '✔' : '' }}</td>
                        <td class="{{ $data->grupo === 'b' ? 'selected' : '' }}">B {{ $data->grupo === 'b' ? '✔' : '' }}</td>
                        <td class="{{ $data->grupo === 'c' ? 'selected' : '' }}">C {{ $data->grupo === 'c' ? '✔' : '' }}</td>
                        <td class="{{ $data->grupo === 'd' ? 'selected' : '' }}">D {{ $data->grupo === 'd' ? '✔' : '' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 28%; vertical-align: top; padding: 0 5px;">
                <table class="params-table">
                    <tr><th colspan="5">Semanas</th></tr>
                    <tr>
                        <td class="{{ $data->semanas == 1 ? 'selected' : '' }}">1 {{ $data->semanas == 1 ? '✔' : '' }}</td>
                        <td class="{{ $data->semanas == 2 ? 'selected' : '' }}">2 {{ $data->semanas == 2 ? '✔' : '' }}</td>
                        <td class="{{ $data->semanas == 4 ? 'selected' : '' }}">4 {{ $data->semanas == 4 ? '✔' : '' }}</td>
                        <td class="{{ $data->semanas == 6 ? 'selected' : '' }}">6 {{ $data->semanas == 6 ? '✔' : '' }}</td>
                        <td class="{{ $data->semanas == 8 ? 'selected' : '' }}">8 {{ $data->semanas == 8 ? '✔' : '' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 23%; vertical-align: top; padding: 0 5px;">
                <table class="params-table">
                    <tr><th colspan="4">Borg</th></tr>
                    <tr>
                        <td class="{{ $data->borg == 8 ? 'selected' : '' }}">8 {{ $data->borg == 8 ? '✔' : '' }}</td>
                        <td class="{{ $data->borg == 10 ? 'selected' : '' }}">10 {{ $data->borg == 10 ? '✔' : '' }}</td>
                        <td class="{{ $data->borg == 12 ? 'selected' : '' }}">12 {{ $data->borg == 12 ? '✔' : '' }}</td>
                        <td class="{{ $data->borg == 14 ? 'selected' : '' }}">14 {{ $data->borg == 14 ? '✔' : '' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 26%; vertical-align: middle; padding-left: 5px;">
                <!-- Riesgo Global -->
                <div class="global-risk {{ $data->riesgo_global }}">
                    <div class="global-risk-label">RIESGO GLOBAL</div>
                    <div class="global-risk-value">{{ $data->riesgo_global }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- FC Diana y datos -->
    <div class="diana-info">
        <strong>Fc Diana:</strong> {{ $data->fc_diana ? round($data->fc_diana) : '-' }} lpm &nbsp;&nbsp;
        <strong>Dp Diana:</strong> {{ $data->dp_diana ?? '-' }} mmHg*lpm &nbsp;&nbsp;
        <strong>Carga Inicial:</strong> {{ $data->carga_inicial ?? '-' }} Watts<br>
        <strong>Método:</strong> {{ $data->fc_diana_metodo ?? '-' }} (Borg, Karvonen, Blackburn, Narita)
    </div>

    <!-- Realizó -->
    @if(isset($user) && $user)
    <div class="realizo-info">
        <strong>Realizó:</strong> {{ $user->nombre_con_titulo ?? $user->name ?? '' }}
    </div>
    @endif

    <!-- Comentarios -->
    @if($data->comentarios)
    <div class="comments-section">
        <div class="comments-title">Comentarios:</div>
        <div class="comments-text">{{ $data->comentarios }}</div>
    </div>
    @endif

    <!-- Firma (posición fija abajo) -->
    @if(isset($firmaBase64) && $firmaBase64)
    <div class="signature-wrapper">
        <img src="{{ $firmaBase64 }}" alt="Firma"><br>
        <div class="signature-line"></div>
        <span class="signature-name">{{ $user->nombre_con_titulo ?? $user->name ?? '' }}</span>
    </div>
    @endif

    </div>{{-- /.content-wrapper --}}
  </body>
</html>
