<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historia Clínica Ginecológica</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}');
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; margin: 20px 25px; }
        table { border-collapse: collapse; }
        .header { width: 100%; background: {!! $clinica->color_principal ?? '#0A1628' !!}; border-radius: 8px; margin-bottom: 10px; padding: 8px 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-cell { width: 60px; padding-right: 12px !important; }
        .header-logo { width: 45px; height: 45px; background: white; border-radius: 6px; padding: 5px; text-align: center; }
        .header-logo img { max-height: 35px; max-width: 35px; }
        .header-title { font-size: 15px; font-weight: 700; color: white; }
        .header-subtitle { font-size: 9px; color: #94a3b8; }
        .header-meta-cell { text-align: right; width: 120px; }
        .header-badge { background: rgba(255,255,255,0.15); padding: 5px 10px; border-radius: 5px; display: inline-block; margin-bottom: 4px; }
        .header-badge-label { font-size: 8px; text-transform: uppercase; color: #94a3b8; }
        .header-badge-value { font-size: 12px; font-weight: 700; color: white; }
        .header-date { font-size: 9px; color: #94a3b8; }
        .patient-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; }
        .patient-table { width: 100%; border-collapse: collapse; }
        .patient-table td { padding: 2px 6px; font-size: 10px; }
        .patient-name { font-size: 13px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; margin-bottom: 6px; }
        .patient-label { color: #64748b; font-size: 9px; }
        .patient-value { font-weight: 600; color: #334155; }
        .section { margin-bottom: 8px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .section-title { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: white; font-size: 9px; font-weight: 700; padding: 4px 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-body { padding: 8px 10px; }
        .row-table { width: 100%; border-collapse: collapse; }
        .row-table td { padding: 2px 4px; vertical-align: top; font-size: 9.5px; }
        .lbl { color: #64748b; font-size: 9px; white-space: nowrap; }
        .check-yes { color: #16a34a; font-weight: 700; }
        .check-no { color: #94a3b8; }
        .text-block { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 8px; font-size: 9.5px; min-height: 20px; }
        .full-label { font-size: 9px; color: #64748b; margin-bottom: 2px; }
        .two-col { width: 50%; vertical-align: top; padding: 0 4px; }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 6px 20px; background: white; border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!}; font-size: 9px; }
        .page-footer-table { width: 100%; }
        .clinic-name { font-weight: 700; color: #ef4444; }
        .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 40px; }
    </style>
</head>
<body>

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
                <td class="header-logo-cell">
                    <div class="header-logo">
                        @if(isset($clinicaLogo) && $clinicaLogo)
                            <img src="{{ $clinicaLogo }}" alt="Logo">
                        @endif
                    </div>
                </td>
                <td style="padding-left:10px;">
                    <div class="header-title">Historia Clínica Ginecológica</div>
                    <div class="header-subtitle">{{ $clinica->nombre ?? '' }}</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Registro</div>
                        <div class="header-badge-value">#{{ $paciente->registro }}</div>
                    </div>
                    <div class="header-date">{{ $historia->fecha_consulta ? $historia->fecha_consulta->format('d/m/Y') : '' }}</div>
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
                <td><span class="patient-label">F. Nacimiento:</span> <span class="patient-value">{{ $paciente->fechaNacimiento }}</span></td>
                <td><span class="patient-label">Estado Civil:</span> <span class="patient-value">{{ $paciente->estadoCivil }}</span></td>
                <td><span class="patient-label">Teléfono:</span> <span class="patient-value">{{ $paciente->telefono }}</span></td>
                <td><span class="patient-label">Hora:</span> <span class="patient-value">{{ $historia->hora ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="2"><span class="patient-label">Médico:</span> <span class="patient-value">{{ $historia->medico_nombre ?? $user->nombre_con_titulo ?? $user->name ?? '' }}</span></td>
                @if($historia->medico_especialidad ?? null)
                <td colspan="2"><span class="patient-label">Especialidad:</span> <span class="patient-value">{{ $historia->medico_especialidad }}</span></td>
                @endif
                @if($historia->medico_cedula ?? null)
                <td><span class="patient-label">Cédula:</span> <span class="patient-value">{{ $historia->medico_cedula }}</span></td>
                @endif
            </tr>
        </table>
    </div>

    <!-- MOTIVO Y PADECIMIENTO -->
    <div class="section">
        <div class="section-title">Motivo de consulta y padecimiento actual</div>
        <div class="section-body">
            <table style="width:100%"><tr>
                <td class="two-col">
                    <div class="full-label">Motivo de consulta</div>
                    <div class="text-block">{{ $historia->motivo_consulta ?? '—' }}</div>
                </td>
                <td class="two-col">
                    <div class="full-label">Padecimiento actual</div>
                    <div class="text-block">{{ $historia->padecimiento_actual ?? '—' }}</div>
                </td>
            </tr></table>
        </div>
    </div>

    <!-- ANTECEDENTES GINECOLÓGICOS + OBSTÉTRICOS -->
    @php $ag = $historia->antecedentes_ginecologicos ?? []; $ao = $historia->antecedentes_obstetricos ?? []; @endphp
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:55%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Antecedentes ginecológicos</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="33%"><span class="lbl">Menarca:</span> {{ $ag['menarca'] ?? '—' }}</td>
                                <td width="33%"><span class="lbl">Ritmo menstrual:</span> {{ $ag['ritmo_menstrual'] ?? '—' }}</td>
                                <td width="33%"><span class="lbl">Duración:</span> {{ $ag['duracion_menstruacion'] ?? '—' }} días</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">FUM:</span> {{ $ag['fum'] ?? '—' }}</td>
                                <td>
                                    <span class="lbl">Ciclos regulares:</span>
                                    @if(isset($ag['ciclos_regulares']))
                                        @if($ag['ciclos_regulares'])<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif
                                    @else —@endif
                                </td>
                                <td>
                                    <span class="lbl">Dismenorrea:</span>
                                    @if(isset($ag['dismenorrea']))
                                        @if($ag['dismenorrea'])<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif
                                    @else —@endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="lbl">Vida sexual activa:</span>
                                    @if(isset($ag['vida_sexual_activa']))
                                        @if($ag['vida_sexual_activa'])<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif
                                    @else —@endif
                                </td>
                                <td><span class="lbl">Edad IVSA:</span> {{ $ag['edad_ivsa'] ?? '—' }}</td>
                                <td><span class="lbl">No. parejas:</span> {{ $ag['num_parejas_sexuales'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td colspan="3"><span class="lbl">Método anticonceptivo:</span> {{ $ag['metodo_anticonceptivo'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Último Pap:</span> {{ $ag['fecha_ultimo_pap'] ?? '—' }}</td>
                                <td colspan="2"><span class="lbl">Resultado:</span> {{ $ag['resultado_pap'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Última mamografía:</span> {{ $ag['fecha_ultima_mamografia'] ?? '—' }}</td>
                                <td colspan="2"><span class="lbl">Resultado:</span> {{ $ag['resultado_mamografia'] ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:45%;vertical-align:top;padding-left:4px;">
                <div class="section">
                    <div class="section-title">Antecedentes obstétricos</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="33%"><span class="lbl">Gestas:</span> {{ $ao['gestas'] ?? '—' }}</td>
                                <td width="33%"><span class="lbl">Partos:</span> {{ $ao['partos'] ?? '—' }}</td>
                                <td width="33%"><span class="lbl">Cesáreas:</span> {{ $ao['cesareas'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Abortos:</span> {{ $ao['abortos'] ?? '—' }}</td>
                                <td><span class="lbl">Ectópicos:</span> {{ $ao['ectopicos'] ?? '—' }}</td>
                                <td><span class="lbl">Molas:</span> {{ $ao['molas'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Último parto:</span> {{ $ao['fecha_ultimo_parto'] ?? '—' }}</td>
                                <td colspan="2"><span class="lbl">Tipo:</span> {{ $ao['tipo_ultimo_parto'] ?? '—' }}</td>
                            </tr>
                            @if($ao['complicaciones_previas'] ?? '')
                            <tr>
                                <td colspan="3"><span class="lbl">Complicaciones previas:</span> {{ $ao['complicaciones_previas'] }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- SIGNOS VITALES -->
    @php $sv = $historia->signos_vitales ?? []; @endphp
    <div class="section">
        <div class="section-title">Signos vitales y somatometría</div>
        <div class="section-body">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:4px 6px;font-size:8.5px;color:#64748b;">Peso (kg)</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:4px 6px;font-size:8.5px;color:#64748b;">Talla (m)</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:4px 6px;font-size:8.5px;color:#64748b;">IMC</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:4px 6px;font-size:8.5px;color:#64748b;">Presión Arterial</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:4px 6px;font-size:8.5px;color:#64748b;">FC (lpm)</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:4px 6px;font-size:8.5px;color:#64748b;">Temperatura (°C)</th>
                </tr>
                <tr>
                    <td style="border:1px solid #e2e8f0;padding:4px 6px;text-align:center;">{{ $sv['peso'] ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0;padding:4px 6px;text-align:center;">{{ $sv['talla'] ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0;padding:4px 6px;text-align:center;">{{ $sv['imc'] ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0;padding:4px 6px;text-align:center;">{{ $sv['presion_arterial'] ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0;padding:4px 6px;text-align:center;">{{ $sv['frecuencia_cardiaca'] ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0;padding:4px 6px;text-align:center;">{{ $sv['temperatura'] ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- EXPLORACIÓN FÍSICA -->
    @php $ef = $historia->exploracion_fisica ?? []; @endphp
    <div class="section">
        <div class="section-title">Exploración física</div>
        <div class="section-body">
            <table style="width:100%">
                <tr>
                    <td class="two-col">
                        <div class="full-label">Mamas — Inspección</div>
                        <div class="text-block">{{ $ef['mamas']['inspeccion'] ?? '—' }}</div>
                        <div class="full-label" style="margin-top:4px;">Mamas — Palpación / Axila</div>
                        <div class="text-block">{{ $ef['mamas']['palpacion'] ?? '—' }} / {{ $ef['mamas']['axila'] ?? '—' }}</div>
                        @if($ef['mamas']['hallazgos'] ?? '')
                        <div class="full-label" style="margin-top:4px;">Hallazgos mamarios</div>
                        <div class="text-block">{{ $ef['mamas']['hallazgos'] }}</div>
                        @endif
                    </td>
                    <td class="two-col">
                        <div class="full-label">Abdomen</div>
                        <div class="text-block">{{ $ef['abdomen']['inspeccion'] ?? '—' }} / {{ $ef['abdomen']['palpacion'] ?? '—' }}</div>
                        @if($ef['abdomen']['hallazgos'] ?? '')
                        <div class="full-label" style="margin-top:4px;">Hallazgos abdominales</div>
                        <div class="text-block">{{ $ef['abdomen']['hallazgos'] }}</div>
                        @endif
                        <div class="full-label" style="margin-top:4px;">Genitales externos</div>
                        <div class="text-block">{{ $ef['genitales_externos']['inspeccion'] ?? '—' }}{{ $ef['genitales_externos']['hallazgos'] ? ' — ' . $ef['genitales_externos']['hallazgos'] : '' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="two-col" style="padding-top:4px;">
                        <div class="full-label">Especuloscopía</div>
                        <table class="row-table">
                            <tr>
                                <td width="50%"><span class="lbl">Cuello:</span> {{ $ef['especuloscopia']['cuello'] ?? '—' }}</td>
                                <td width="50%"><span class="lbl">Vagina:</span> {{ $ef['especuloscopia']['vagina'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Secreción:</span> {{ $ef['especuloscopia']['secrecion'] ?? '—' }}</td>
                                <td>@if($ef['especuloscopia']['hallazgos'] ?? '')<span class="lbl">Hallazgos:</span> {{ $ef['especuloscopia']['hallazgos'] }}@endif</td>
                            </tr>
                        </table>
                    </td>
                    <td class="two-col" style="padding-top:4px;">
                        <div class="full-label">Tacto vaginal</div>
                        <table class="row-table">
                            <tr>
                                <td width="50%"><span class="lbl">Útero — tamaño:</span> {{ $ef['tacto_vaginal']['utero_tamano'] ?? '—' }}</td>
                                <td width="50%"><span class="lbl">Posición:</span> {{ $ef['tacto_vaginal']['utero_posicion'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Anexos:</span> {{ $ef['tacto_vaginal']['anexos'] ?? '—' }}</td>
                                <td><span class="lbl">Fondos de saco:</span> {{ $ef['tacto_vaginal']['fondos_saco'] ?? '—' }}</td>
                            </tr>
                            @if($ef['tacto_vaginal']['hallazgos'] ?? '')
                            <tr><td colspan="2"><span class="lbl">Hallazgos:</span> {{ $ef['tacto_vaginal']['hallazgos'] }}</td></tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- DIAGNÓSTICOS -->
    @php $dx = $historia->diagnosticos ?? []; @endphp
    <div class="section">
        <div class="section-title">Diagnósticos</div>
        <div class="section-body">
            @if(is_array($dx) && count($dx))
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:3px 6px;font-size:8.5px;text-align:left;color:#64748b;">#</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:3px 6px;font-size:8.5px;text-align:left;color:#64748b;">Descripción</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:3px 6px;font-size:8.5px;text-align:left;color:#64748b;">CIE-10</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:3px 6px;font-size:8.5px;text-align:left;color:#64748b;">Tipo</th>
                </tr>
                @foreach($dx as $i => $d)
                <tr>
                    <td style="border:1px solid #e2e8f0;padding:3px 6px;font-size:9px;">{{ $i + 1 }}</td>
                    <td style="border:1px solid #e2e8f0;padding:3px 6px;font-size:9px;">{{ $d['descripcion'] ?? $d }}</td>
                    <td style="border:1px solid #e2e8f0;padding:3px 6px;font-size:9px;">{{ $d['cie10'] ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0;padding:3px 6px;font-size:9px;">{{ $d['tipo'] ?? '—' }}</td>
                </tr>
                @endforeach
            </table>
            @else
            <div class="text-block">—</div>
            @endif
        </div>
    </div>

    <!-- ESTUDIOS SOLICITADOS -->
    @php $es = $historia->estudios_solicitados ?? []; @endphp
    @if(!empty($es['laboratorio']) || !empty($es['gabinete']) || !empty($es['otros']))
    <div class="section">
        <div class="section-title">Estudios solicitados</div>
        <div class="section-body">
            <table style="width:100%"><tr>
                @if(!empty($es['laboratorio']))
                <td class="two-col">
                    <div class="full-label">Laboratorio</div>
                    <div class="text-block">{{ is_array($es['laboratorio']) ? implode(', ', $es['laboratorio']) : $es['laboratorio'] }}</div>
                </td>
                @endif
                @if(!empty($es['gabinete']))
                <td class="two-col">
                    <div class="full-label">Gabinete</div>
                    <div class="text-block">{{ is_array($es['gabinete']) ? implode(', ', $es['gabinete']) : $es['gabinete'] }}</div>
                </td>
                @endif
            </tr>
            @if(!empty($es['otros']))
            <tr>
                <td colspan="2" style="padding-top:4px;">
                    <div class="full-label">Otros</div>
                    <div class="text-block">{{ is_array($es['otros']) ? implode(', ', $es['otros']) : $es['otros'] }}</div>
                </td>
            </tr>
            @endif
            </table>
        </div>
    </div>
    @endif

    <!-- TRATAMIENTO -->
    @php $trat = $historia->tratamiento ?? []; @endphp
    <div class="section">
        <div class="section-title">Tratamiento</div>
        <div class="section-body">
            @if(!empty($trat['medicamentos']))
            <div class="full-label">Medicamentos</div>
            <table style="width:100%;border-collapse:collapse;margin-bottom:6px;">
                <tr>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:3px 6px;font-size:8.5px;text-align:left;color:#64748b;">Medicamento</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:3px 6px;font-size:8.5px;color:#64748b;">Dosis</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:3px 6px;font-size:8.5px;color:#64748b;">Vía</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:3px 6px;font-size:8.5px;color:#64748b;">Frecuencia</th>
                    <th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:3px 6px;font-size:8.5px;color:#64748b;">Duración</th>
                </tr>
                @foreach($trat['medicamentos'] as $med)
                <tr>
                    <td style="border:1px solid #e2e8f0;padding:3px 6px;font-size:9px;">{{ $med['nombre'] ?? $med }}</td>
                    <td style="border:1px solid #e2e8f0;padding:3px 6px;font-size:9px;text-align:center;">{{ $med['dosis'] ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0;padding:3px 6px;font-size:9px;text-align:center;">{{ $med['via'] ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0;padding:3px 6px;font-size:9px;text-align:center;">{{ $med['frecuencia'] ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0;padding:3px 6px;font-size:9px;text-align:center;">{{ $med['duracion'] ?? '—' }}</td>
                </tr>
                @endforeach
            </table>
            @endif
            @if($trat['indicaciones_generales'] ?? '')
            <div class="full-label">Indicaciones generales</div>
            <div class="text-block">{{ $trat['indicaciones_generales'] }}</div>
            @endif
            @if($trat['fecha_proxima_cita'] ?? '')
            <div style="margin-top:4px;"><span class="lbl">Próxima cita:</span> <strong>{{ $trat['fecha_proxima_cita'] }}</strong></div>
            @endif
        </div>
    </div>

    <!-- OBSERVACIONES Y NOTAS -->
    @if(($historia->observaciones ?? '') || ($historia->notas_adicionales ?? ''))
    <div class="section">
        <div class="section-title">Observaciones y notas adicionales</div>
        <div class="section-body">
            <table style="width:100%"><tr>
                @if($historia->observaciones ?? '')
                <td class="two-col">
                    <div class="full-label">Observaciones</div>
                    <div class="text-block">{{ $historia->observaciones }}</div>
                </td>
                @endif
                @if($historia->notas_adicionales ?? '')
                <td class="two-col">
                    <div class="full-label">Notas adicionales</div>
                    <div class="text-block">{{ $historia->notas_adicionales }}</div>
                </td>
                @endif
            </tr></table>
        </div>
    </div>
    @endif

    <!-- FIRMA -->
    <table style="width:100%;margin-top:30px;">
        <tr>
            <td style="width:25%;"></td>
            <td style="width:50%;text-align:center;padding-top:8px;">
                @if(isset($firmaBase64) && $firmaBase64)
                <img src="{{ $firmaBase64 }}" alt="Firma" style="height:50px;width:auto;"><br>
                @endif
                <div style="border-top:1px solid #334155;width:200px;margin:4px auto 0 auto;padding-top:6px;">
                    <div style="font-size:10px;font-weight:700;color:{!! $clinica->color_principal ?? '#0A1628' !!};">
                        {{ $historia->medico_nombre ?? $user->nombre_con_titulo ?? $user->name ?? '' }}
                    </div>
                    @if($historia->medico_cedula ?? null)
                    <div style="font-size:9px;color:#64748b;">Cédula: {{ $historia->medico_cedula }}</div>
                    @elseif($user->cedula_especialista ?? null)
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

</div>
</body>
</html>
