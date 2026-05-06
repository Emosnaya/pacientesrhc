<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Electrocardiograma</title>
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
        .urgente-badge { display: inline-block; background: #ef4444; color: white; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: 700; }
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
                    <div class="header-title">
                        Electrocardiograma
                        @if($ecg->urgente) <span class="urgente-badge">URGENTE</span> @endif
                    </div>
                    <div class="header-subtitle">{{ $clinica->nombre ?? '' }}</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Registro</div>
                        <div class="header-badge-value">#{{ $paciente->registro }}</div>
                    </div>
                    <div class="header-date">{{ $ecg->fecha_estudio ? $ecg->fecha_estudio->format('d/m/Y') : '' }}</div>
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
                <td><span class="patient-label">Género:</span> <span class="patient-value">{{ $paciente->genero == 1 ? 'Hombre' : 'Mujer' }}</span></td>
                <td><span class="patient-label">F. Nacimiento:</span> <span class="patient-value">{{ $paciente->fechaNacimiento }}</span></td>
                <td><span class="patient-label">Hora:</span> <span class="patient-value">{{ $ecg->hora ?? '' }}</span></td>
                <td><span class="patient-label">Médico:</span> <span class="patient-value">{{ $ecg->medico_interpreta ?? ($user->nombre_completo ?? $user->name ?? '') }}</span></td>
            </tr>
        </table>
    </div>

    <!-- INDICACIÓN Y CONTEXTO -->
    <div class="section">
        <div class="section-title">Indicación y contexto clínico</div>
        <div class="section-body">
            <table style="width:100%"><tr>
                <td class="two-col">
                    <div class="full-label">Indicación</div>
                    <div class="text-block">{{ $ecg->indicacion ?? '—' }}</div>
                </td>
                <td class="two-col">
                    <div class="full-label">Contexto clínico</div>
                    <div class="text-block">{{ $ecg->contexto_clinico ?? '—' }}</div>
                </td>
            </tr></table>
            <table class="row-table" style="margin-top:4px;">
                <tr>
                    <td width="33%"><span class="lbl">Velocidad papel:</span> {{ $ecg->velocidad_papel ?? '—' }} mm/s</td>
                    <td width="33%"><span class="lbl">Calibración:</span> {{ $ecg->calibracion ?? '—' }} mm/mV</td>
                    @if($ecg->comparado_previo)
                    <td width="33%"><span class="lbl">Comparado con previo:</span> <span class="check-yes">Sí</span></td>
                    @endif
                </tr>
            </table>
        </div>
    </div>

    <!-- RITMO Y FRECUENCIA + INTERVALOS -->
    @php $rf = $ecg->ritmo_frecuencia ?? []; $int = $ecg->intervalos ?? []; $eje = $ecg->eje_electrico ?? []; @endphp
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:40%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Ritmo y frecuencia</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="50%"><span class="lbl">Ritmo:</span> {{ $rf['ritmo'] ?? '—' }}</td>
                                <td width="50%"><span class="lbl">FC:</span> {{ $rf['fc'] ?? '—' }} lpm</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Regularidad:</span> {{ $rf['regularidad'] ?? '—' }}</td>
                                <td><span class="lbl">Origen:</span> {{ $rf['origen'] ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:35%;vertical-align:top;padding: 0 4px;">
                <div class="section">
                    <div class="section-title">Intervalos (ms)</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="50%"><span class="lbl">PR:</span> {{ $int['pr'] ?? '—' }}</td>
                                <td width="50%"><span class="lbl">QRS:</span> {{ $int['qrs'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">QT:</span> {{ $int['qt'] ?? '—' }}</td>
                                <td><span class="lbl">QTc ({{ $int['formula_qtc'] ?? 'Bazett' }}):</span> {{ $int['qtc'] ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:25%;vertical-align:top;padding-left:4px;">
                <div class="section">
                    <div class="section-title">Eje eléctrico</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr><td><span class="lbl">Eje QRS:</span> {{ $eje['eje_qrs'] ?? '—' }}°</td></tr>
                            <tr><td><span class="lbl">Eje P:</span> {{ $eje['eje_p'] ?? '—' }}°</td></tr>
                            <tr><td><span class="lbl">Eje T:</span> {{ $eje['eje_t'] ?? '—' }}°</td></tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- ONDA P + COMPLEJO QRS -->
    @php $op = $ecg->onda_p ?? []; $qrs = $ecg->complejo_qrs ?? []; @endphp
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:40%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Onda P</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="50%"><span class="lbl">Morfología:</span> {{ $op['morfologia'] ?? '—' }}</td>
                                <td width="50%"><span class="lbl">Duración:</span> {{ $op['duracion'] ?? '—' }} ms</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Amplitud:</span> {{ $op['amplitud'] ?? '—' }} mm</td>
                                <td>
                                    <span class="lbl">Crecimiento AI:</span> @if($op['crecimiento_ai'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif &nbsp;
                                    <span class="lbl">AD:</span> @if($op['crecimiento_ad'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:60%;vertical-align:top;padding-left:4px;">
                <div class="section">
                    <div class="section-title">Complejo QRS</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="33%"><span class="lbl">Morfología:</span> {{ $qrs['morfologia'] ?? '—' }}</td>
                                <td width="33%"><span class="lbl">Duración:</span> {{ $qrs['duracion'] ?? '—' }} ms</td>
                                <td width="33%"><span class="lbl">Bajo voltaje:</span> @if($qrs['bajo_voltaje'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="lbl">Bloqueo rama:</span>
                                    @if($qrs['bloqueo_rama']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $qrs['bloqueo_rama']['tipo'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                                <td>
                                    <span class="lbl">Hemibloqueo:</span>
                                    @if($qrs['hemibloqueo']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $qrs['hemibloqueo']['tipo'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                                <td>
                                    <span class="lbl">Ondas Q:</span>
                                    @if($qrs['ondas_q']['tiene'] ?? false)<span class="check-yes">Sí</span> {{ $qrs['ondas_q']['localizacion'] ?? '' }}@if($qrs['ondas_q']['patologicas'] ?? false) (patológicas)@endif@else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="lbl">HVI:</span>
                                    @if($qrs['hipertrofia_vi']['tiene'] ?? false)<span class="check-yes">Sí</span> {{ $qrs['hipertrofia_vi']['criterios'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                                <td>
                                    <span class="lbl">HVD:</span>
                                    @if($qrs['hipertrofia_vd']['tiene'] ?? false)<span class="check-yes">Sí</span> {{ $qrs['hipertrofia_vd']['criterios'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- SEGMENTO ST + ONDA T -->
    @php $st = $ecg->segmento_st ?? []; $ot = $ecg->onda_t ?? []; @endphp
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:50%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Segmento ST</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="50%">
                                    <span class="lbl">Elevación:</span>
                                    @if($st['elevacion']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> — {{ $st['elevacion']['derivaciones'] ?? '' }} {{ $st['elevacion']['magnitud'] ?? '' }} mm
                                    @else<span class="check-no">No</span>@endif
                                </td>
                                <td width="50%">
                                    <span class="lbl">Depresión:</span>
                                    @if($st['depresion']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> — {{ $st['depresion']['derivaciones'] ?? '' }} {{ $st['depresion']['magnitud'] ?? '' }} mm {{ $st['depresion']['tipo'] ?? '' }}
                                    @else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:4px;">
                <div class="section">
                    <div class="section-title">Onda T</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="33%"><span class="lbl">Morfología:</span> {{ $ot['morfologia'] ?? '—' }}</td>
                                <td width="33%">
                                    <span class="lbl">Inversiones:</span>
                                    @if($ot['inversiones']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $ot['inversiones']['derivaciones'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                                <td width="33%">
                                    <span class="lbl">Picudas:</span> @if($ot['picudas'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif &nbsp;
                                    <span class="lbl">Aplanadas:</span> @if($ot['aplanamiento'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- ARRITMIAS -->
    @php $arr = $ecg->arritmias ?? []; @endphp
    <div class="section">
        <div class="section-title">Arritmias</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="20%"><span class="lbl">Extrasístoles SV:</span> @if($arr['extrasistoles_sv'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td width="20%"><span class="lbl">Extrasístoles V:</span> @if($arr['extrasistoles_v'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td width="20%"><span class="lbl">FA:</span> @if($arr['fa'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td width="20%"><span class="lbl">Flutter:</span> @if($arr['flutter'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td width="20%"><span class="lbl">Taq. SV:</span> @if($arr['taquicardia_sv'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                </tr>
                <tr>
                    <td><span class="lbl">Taq. Ventricular:</span> @if($arr['taquicardia_v'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td><span class="lbl">Bradicardia:</span> @if($arr['bradicardia'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td>
                        <span class="lbl">Bloqueo AV:</span>
                        @if($arr['bloqueo_av']['tiene'] ?? false)<span class="check-yes">Sí</span> Gr.{{ $arr['bloqueo_av']['grado'] ?? '' }}@else<span class="check-no">No</span>@endif
                    </td>
                    <td><span class="lbl">Pausa sinusal:</span> @if($arr['pausa_sinusal'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- MARCAPASOS -->
    @php $mp = $ecg->marcapasos ?? []; @endphp
    @if($mp['presente'] ?? false)
    <div class="section">
        <div class="section-title">Marcapasos</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="25%"><span class="lbl">Tipo estimulación:</span> {{ $mp['tipo_estimulacion'] ?? '—' }}</td>
                    <td width="25%"><span class="lbl">Espigas visibles:</span> @if($mp['espigas_visibles'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td width="25%"><span class="lbl">Captura:</span> {{ $mp['captura'] ?? '—' }}</td>
                    <td width="25%"><span class="lbl">Sensado:</span> {{ $mp['sensado'] ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    <!-- IMAGEN ECG -->
    @if($ecg->imagen_path && file_exists(public_path('storage/' . $ecg->imagen_path)))
    @php
        $imgData = file_get_contents(public_path('storage/' . $ecg->imagen_path));
        $imgType = mime_content_type(public_path('storage/' . $ecg->imagen_path));
        $imgBase64 = 'data:' . $imgType . ';base64,' . base64_encode($imgData);
    @endphp
    <div class="section">
        <div class="section-title">Imagen del ECG</div>
        <div class="section-body" style="text-align:center;">
            <img src="{{ $imgBase64 }}" style="max-width:100%;height:auto;">
        </div>
    </div>
    @endif

    <!-- CAMBIOS VS PREVIO -->
    @if($ecg->comparado_previo && $ecg->cambios_vs_previo)
    <div class="section">
        <div class="section-title">Cambios vs estudio previo</div>
        <div class="section-body">
            <div class="text-block">{{ $ecg->cambios_vs_previo }}</div>
        </div>
    </div>
    @endif

    <!-- INTERPRETACIÓN Y CONCLUSIONES -->
    <div class="section">
        <div class="section-title">Interpretación y conclusiones</div>
        <div class="section-body">
            <table style="width:100%"><tr>
                <td class="two-col">
                    <div class="full-label">Interpretación</div>
                    <div class="text-block">{{ $ecg->interpretacion ?? '—' }}</div>
                </td>
                <td class="two-col">
                    <div class="full-label">Conclusiones</div>
                    <div class="text-block">{{ $ecg->conclusiones ?? '—' }}</div>
                </td>
            </tr><tr>
                <td colspan="2" style="padding-top:4px;">
                    <div class="full-label">Recomendaciones</div>
                    <div class="text-block">{{ $ecg->recomendaciones ?? '—' }}</div>
                </td>
            </tr></table>
        </div>
    </div>

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
                        {{ $ecg->medico_interpreta ?? $user->nombre_con_titulo ?? $user->name ?? '' }}
                    </div>
                    @if($ecg->cedula_medico ?? null)
                    <div style="font-size:9px;color:#64748b;">Cédula: {{ $ecg->cedula_medico }}</div>
                    @elseif($user->cedula_especialista ?? null)
                    <div style="font-size:9px;color:#64748b;">Cédula: {{ $user->cedula_especialista }}</div>
                    @endif
                    <div style="font-size:9px;color:#64748b;margin-top:2px;">Médico que interpreta</div>
                </div>
            </td>
            <td style="width:25%;"></td>
        </tr>
    </table>

</div>
</body>
</html>
