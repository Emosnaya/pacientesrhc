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
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            color: #1a1a2e;
            line-height: 1.3;
        }
        
        /* Header moderno */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 5px;
        }
        .logo-cell {
            width: 80px;
        }
        .logo-cell img {
            height: 45px;
            width: auto;
        }
        .title-cell {
            text-align: center;
        }
        .main-title {
            font-size: 16px;
            font-weight: bold;
            color: #0A1628;
            margin-bottom: 2px;
        }
        .subtitle {
            font-size: 10px;
            color: #6b7280;
        }
        .date-cell {
            width: 120px;
            text-align: right;
            font-size: 9px;
        }
        
        /* Información del paciente */
        .patient-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .patient-table td {
            padding: 4px 8px;
            font-size: 9px;
        }
        .patient-table .label {
            font-weight: bold;
            color: #374151;
            width: 70px;
        }
        .patient-table .value {
            color: #1f2937;
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
            margin: 10px 0;
            padding: 8px;
            border: 2px solid;
            text-align: center;
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
            font-size: 11px;
            font-weight: bold;
            color: #374151;
        }
        .global-risk-value {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
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
        
        /* Firma */
        .signature-section {
            position: fixed;
            bottom: 60px;
            left: 0;
            right: 0;
            text-align: center;
        }
        .signature-section img {
            height: 35px;
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
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: linear-gradient(135deg, #0A1628 0%, #1e3a5f 100%);
            color: white;
            padding: 8px 15px;
            font-size: 8px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-table td {
            vertical-align: middle;
            color: white;
        }
        .footer-left {
            text-align: left;
        }
        .footer-center {
            text-align: center;
        }
        .footer-right {
            text-align: right;
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
    </style>
  </head>
  <body>
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if(isset($clinicaLogo) && $clinicaLogo)
                <img src="{{ $clinicaLogo }}" alt="Logo">
                @endif
            </td>
            <td class="title-cell">
                <div class="main-title">Estratificación AACVPR / EAPC</div>
                <div class="subtitle">Estratificación de Riesgo para Rehabilitación Cardiovascular</div>
            </td>
            <td class="date-cell">
                <strong>Fecha:</strong> {{ $data->fecha_estratificacion ? date('d/m/Y', strtotime($data->fecha_estratificacion)) : 'N/A' }}<br>
                <strong>Registro:</strong> {{ $paciente->registro ?? 'N/A' }}
            </td>
        </tr>
    </table>

    <!-- Información del Paciente -->
    <table class="patient-table">
        <tr>
            <td class="label">Nombre:</td>
            <td class="value" colspan="3">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</td>
            <td class="label">Edad:</td>
            <td class="value">{{ $paciente->edad }} años</td>
        </tr>
        <tr>
            <td class="label">Peso:</td>
            <td class="value">{{ $paciente->peso }} kg</td>
            <td class="label">Talla:</td>
            <td class="value">{{ $paciente->talla }} m</td>
            <td class="label">IMC:</td>
            <td class="value">{{ number_format($paciente->imc, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Género:</td>
            <td class="value">{{ $paciente->genero == 1 ? 'Masculino' : 'Femenino' }}</td>
            <td class="label" colspan="4">Diagnóstico: <span class="value">{{ $paciente->diagnostico ?? 'N/A' }}</span></td>
        </tr>
    </table>

    <!-- Tabla AACVPR/EAPC - Riesgo Alto -->
    <div class="risk-section">
        <div class="risk-header alto">RIESGO ALTO</div>
        <table class="risk-table">
            <tr>
                <td class="criteria">FEVI severamente disminuida (&lt;40%)</td>
                <td class="check">@if($data->alto_fevi_disminuida) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Síntomas o signos en reposo o a baja carga (≤3METs)</td>
                <td class="check">@if($data->alto_sintomas_reposo) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Respuesta isquémica o angina durante ejercicio de baja intensidad</td>
                <td class="check">@if($data->alto_isquemia_baja_intensidad) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Arritmias ventriculares complejas en reposo o ejercicio</td>
                <td class="check">@if($data->alto_arritmias_ventriculares) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">IM o procedimiento de revascularización complicado</td>
                <td class="check">@if($data->alto_im_complicado) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Capacidad funcional &lt;5 METs</td>
                <td class="check">@if($data->alto_capacidad_menor_5mets) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Respuesta hemodinámica anormal al ejercicio (ICC, cambios isquémicos ST, hipotensión)</td>
                <td class="check">@if($data->alto_hemodinamica_anormal) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Paro cardíaco sobrevivido</td>
                <td class="check">@if($data->alto_paro_cardiaco) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Enfermedad coronaria compleja (tronco, multivaso, etc.)</td>
                <td class="check">@if($data->alto_enfermedad_compleja) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
        </table>
    </div>

    <!-- Tabla AACVPR/EAPC - Riesgo Moderado -->
    <div class="risk-section">
        <div class="risk-header moderado">RIESGO MODERADO</div>
        <table class="risk-table">
            <tr>
                <td class="criteria">FEVI moderadamente disminuida (40-49%)</td>
                <td class="check">@if($data->moderado_fevi_moderada) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Síntomas o signos a intensidad moderada (3-6 METs)</td>
                <td class="check">@if($data->moderado_sintomas_moderados) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Isquemia leve a moderada durante ejercicio</td>
                <td class="check">@if($data->moderado_isquemia_moderada) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Capacidad funcional 5-7 METs</td>
                <td class="check">@if($data->moderado_capacidad_5_7mets) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Imposibilidad de automonitoreo de FC/síntomas</td>
                <td class="check">@if($data->moderado_sin_automonitoreo) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
        </table>
    </div>

    <!-- Tabla AACVPR/EAPC - Riesgo Bajo -->
    <div class="risk-section">
        <div class="risk-header bajo">RIESGO BAJO</div>
        <table class="risk-table">
            <tr>
                <td class="criteria">FEVI preservada (≥50%)</td>
                <td class="check">@if($data->bajo_fevi_preservada) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Sin síntomas ni signos en reposo o ejercicio</td>
                <td class="check">@if($data->bajo_sin_sintomas) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Sin isquemia durante prueba de esfuerzo</td>
                <td class="check">@if($data->bajo_sin_isquemia) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Capacidad funcional ≥7 METs</td>
                <td class="check">@if($data->bajo_capacidad_mayor_7mets) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Sin arritmias complejas</td>
                <td class="check">@if($data->bajo_sin_arritmias) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">IM o revascularización no complicada</td>
                <td class="check">@if($data->bajo_im_no_complicado) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Respuesta hemodinámica normal</td>
                <td class="check">@if($data->bajo_hemodinamica_normal) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
            <tr>
                <td class="criteria">Capacidad de automonitoreo adecuada</td>
                <td class="check">@if($data->bajo_automonitoreo_adecuado) <span class="check-yes">✓ Sí</span> @else <span class="check-no">No</span> @endif</td>
            </tr>
        </table>
    </div>

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

    <!-- Riesgo Global -->
    <div class="global-risk {{ $data->riesgo_global }}">
        <div class="global-risk-label">RIESGO GLOBAL</div>
        <div class="global-risk-value">{{ $data->riesgo_global }}</div>
    </div>

    <!-- Parámetros Iniciales -->
    <div class="section-title">Parámetros Iniciales</div>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
        <tr>
            <td style="width: 33%; vertical-align: top; padding-right: 5px;">
                <table class="params-table">
                    <tr><th colspan="4">Grupo</th></tr>
                    <tr>
                        <td class="{{ $data->grupo === 'a' ? 'selected' : '' }}">A {{ $data->grupo === 'a' ? '✓' : '' }}</td>
                        <td class="{{ $data->grupo === 'b' ? 'selected' : '' }}">B {{ $data->grupo === 'b' ? '✓' : '' }}</td>
                        <td class="{{ $data->grupo === 'c' ? 'selected' : '' }}">C {{ $data->grupo === 'c' ? '✓' : '' }}</td>
                        <td class="{{ $data->grupo === 'd' ? 'selected' : '' }}">D {{ $data->grupo === 'd' ? '✓' : '' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 33%; vertical-align: top; padding: 0 5px;">
                <table class="params-table">
                    <tr><th colspan="5">Semanas</th></tr>
                    <tr>
                        <td class="{{ $data->semanas == 1 ? 'selected' : '' }}">1 {{ $data->semanas == 1 ? '✓' : '' }}</td>
                        <td class="{{ $data->semanas == 2 ? 'selected' : '' }}">2 {{ $data->semanas == 2 ? '✓' : '' }}</td>
                        <td class="{{ $data->semanas == 4 ? 'selected' : '' }}">4 {{ $data->semanas == 4 ? '✓' : '' }}</td>
                        <td class="{{ $data->semanas == 6 ? 'selected' : '' }}">6 {{ $data->semanas == 6 ? '✓' : '' }}</td>
                        <td class="{{ $data->semanas == 8 ? 'selected' : '' }}">8 {{ $data->semanas == 8 ? '✓' : '' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 33%; vertical-align: top; padding-left: 5px;">
                <table class="params-table">
                    <tr><th colspan="4">Borg</th></tr>
                    <tr>
                        <td class="{{ $data->borg == 8 ? 'selected' : '' }}">8 {{ $data->borg == 8 ? '✓' : '' }}</td>
                        <td class="{{ $data->borg == 10 ? 'selected' : '' }}">10 {{ $data->borg == 10 ? '✓' : '' }}</td>
                        <td class="{{ $data->borg == 12 ? 'selected' : '' }}">12 {{ $data->borg == 12 ? '✓' : '' }}</td>
                        <td class="{{ $data->borg == 14 ? 'selected' : '' }}">14 {{ $data->borg == 14 ? '✓' : '' }}</td>
                    </tr>
                </table>
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
    <div style="font-size: 9px; margin-top: 5px;">
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

    <!-- Firma -->
    @if(isset($firmaBase64) && $firmaBase64)
    <div class="signature-section">
        <img src="{{ $firmaBase64 }}" alt="Firma"><br>
        <div class="signature-line"></div>
        <span class="signature-name">{{ $user->nombre_con_titulo ?? $user->name ?? '' }}</span>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left" style="width: 40%;">
                    @if(isset($clinica))
                        <strong>{{ $clinica->nombre ?? '' }}</strong><br>
                        {{ $clinica->direccion ?? '' }}
                    @endif
                </td>
                <td class="footer-center" style="width: 30%;">
                    @if(isset($clinica))
                        {{ $clinica->telefono ?? '' }}<br>
                        {{ $clinica->email ?? '' }}
                    @endif
                </td>
                <td class="footer-right" style="width: 30%;">
                    Estratificación AACVPR/EAPC<br>
                    <small>Generado el {{ date('d/m/Y H:i') }}</small>
                </td>
            </tr>
        </table>
    </div>
  </body>
</html>
