<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Electrocardiograma en Reposo</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}');
        }
        @page { margin: 12mm 10mm 16mm 10mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; line-height: 1.35; margin: 0; }
        table { border-collapse: collapse; page-break-inside: auto; }
        tr { page-break-inside: auto; page-break-after: auto; }
        td { page-break-inside: auto; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        .header { width: 100%; background: {!! $clinica->color_principal ?? '#0A1628' !!}; border-radius: 8px; margin-bottom: 6px; padding: 6px 10px; }
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
        .patient-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; margin-bottom: 5px; }
        .patient-table { width: 100%; border-collapse: collapse; }
        .patient-table td { padding: 2px 6px; font-size: 10px; }
        .patient-name { font-size: 13px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; margin-bottom: 6px; }
        .patient-label { color: #64748b; font-size: 9px; }
        .patient-value { font-weight: 600; color: #334155; }
        .section { margin-bottom: 5px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; page-break-inside: auto; }
        .section-title { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: white; font-size: 9px; font-weight: 700; padding: 3px 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-body { padding: 5px 8px; }
        .section-compact .section-body { padding: 4px 6px; }
        .section-compact .row-table td { padding: 1px 3px; font-size: 9px; }
        .row-table { width: 100%; border-collapse: collapse; }
        .row-table td { padding: 2px 4px; vertical-align: top; font-size: 9.5px; }
        .lbl { color: #64748b; font-size: 9px; white-space: nowrap; }
        .check-yes { color: #16a34a; font-weight: 700; }
        .check-no { color: #94a3b8; }
        .text-block { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px 6px; font-size: 9.5px; }
        .full-label { font-size: 9px; color: #64748b; margin-bottom: 2px; }
        .two-col { width: 50%; vertical-align: top; padding: 0 4px; }
        .urgente-badge { display: inline-block; background: #ef4444; color: white; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: 700; }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 4px 10mm; background: white; border-top: 1px solid {!! $clinica->color_principal ?? '#0A1628' !!}; font-size: 8px; }
        .page-footer-table { width: 100%; }
        .clinic-name { font-weight: 700; color: #ef4444; }
        .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 0; }
        .signature-block { margin-top: 8px; padding-top: 4px; text-align: center; }
        .signature-block img { height: 42px; width: auto; }
        .signature-line { border-top: 1px solid #334155; width: 200px; margin: 3px auto 0 auto; padding-top: 4px; }
        .ecg-image { max-width: 100%; max-height: 220px; height: auto; }
    </style>
</head>
<body>

@php
    use App\Support\ElectrocardiogramaPdfHelper as EcgPdf;

    $rf = $ecg->ritmo_frecuencia ?? [];
    $int = $ecg->intervalos ?? [];
    $eje = $ecg->eje_electrico ?? [];
    $op = $ecg->onda_p ?? [];
    $qrs = $ecg->complejo_qrs ?? [];
    $st = $ecg->segmento_st ?? [];
    $ot = $ecg->onda_t ?? [];
    $arr = $ecg->arritmias ?? [];
    $mp = $ecg->marcapasos ?? [];

    $esv = $arr['extrasistoles_supraventriculares'] ?? [];
    $ev = $arr['extrasistoles_ventriculares'] ?? [];
    $flutter = $arr['flutter_auricular'] ?? [];
    $tsv = $arr['taquicardia_supraventricular'] ?? [];
    $tv = $arr['taquicardia_ventricular'] ?? [];

    $flutterTiene = is_array($flutter) ? ($flutter['tiene'] ?? false) : (bool) $flutter;
    $tsvTiene = is_array($tsv) ? ($tsv['tiene'] ?? false) : (bool) $tsv;
    $tvTiene = is_array($tv) ? ($tv['tiene'] ?? false) : (bool) $tv;
    $mpTiene = ($mp['tiene'] ?? false) || ($mp['presente'] ?? false);

    $medico = $ecg->medico_realiza ?? $ecg->medico_interpreta ?? ($user->nombre_con_titulo ?? $user->name ?? '');
    $comparacion = $ecg->comparacion_previo ?? $ecg->cambios_vs_previo ?? '';
    $diagnostico = $ecg->diagnostico_electrocardiografico ?? $ecg->conclusiones ?? '';
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
                    <div class="header-title">
                        Electrocardiograma en Reposo
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

    <div class="patient-card">
        <div class="patient-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
        <table class="patient-table">
            <tr>
                <td><span class="patient-label">Edad:</span> <span class="patient-value">{{ $paciente->edad }} años</span></td>
                <td><span class="patient-label">Género:</span> <span class="patient-value">{{ $paciente->genero == 1 ? 'Hombre' : 'Mujer' }}</span></td>
                <td><span class="patient-label">F. Nacimiento:</span> <span class="patient-value">{{ $paciente->fechaNacimiento }}</span></td>
                <td><span class="patient-label">Hora:</span> <span class="patient-value">{{ $ecg->hora ?? '—' }}</span></td>
                <td><span class="patient-label">Médico:</span> <span class="patient-value">{{ $medico ?: '—' }}</span></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Datos del estudio</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="50%"><span class="lbl">Indicación:</span> {{ $ecg->indicacion ?: '—' }}</td>
                    <td width="25%"><span class="lbl">Velocidad papel:</span> {{ $ecg->velocidad_papel ?? '—' }} mm/s</td>
                    <td width="25%"><span class="lbl">Calibración:</span> {{ $ecg->calibracion ?? '—' }} mm/mV</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section section-compact">
        <div class="section-title">Ritmo, intervalos y eje</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="34%"><span class="lbl">Tipo de ritmo:</span> {{ EcgPdf::label($maps['tipo_ritmo'], $rf['tipo_ritmo'] ?? $rf['ritmo'] ?? null) }}</td>
                    <td width="22%"><span class="lbl">FC:</span> {{ $rf['frecuencia_cardiaca'] ?? $rf['fc'] ?? '—' }} lpm</td>
                    <td width="22%"><span class="lbl">PR:</span> {{ $int['pr'] ?? '—' }} ms</td>
                    <td width="22%"><span class="lbl">QRS:</span> {{ $int['qrs'] ?? '—' }} ms</td>
                </tr>
                <tr>
                    <td><span class="lbl">QT:</span> {{ $int['qt'] ?? '—' }} ms</td>
                    <td><span class="lbl">QTc ({{ EcgPdf::label($maps['formula_qtc'], $int['formula_qtc'] ?? 'bazett', 'Bazett') }}):</span> {{ $int['qtc'] ?? '—' }} ms</td>
                    <td><span class="lbl">Eje QRS:</span> {{ $eje['aqrs'] ?? $eje['eje_qrs'] ?? '—' }}°</td>
                    <td><span class="lbl">Eje P:</span> {{ $eje['ap'] ?? $eje['eje_p'] ?? '—' }}°</td>
                </tr>
                <tr>
                    <td><span class="lbl">Eje T:</span> {{ $eje['at'] ?? $eje['eje_t'] ?? '—' }}°</td>
                    <td colspan="3"><span class="lbl">Desviación:</span> {{ !empty($eje['desviacion']) ? EcgPdf::label($maps['desviacion'], $eje['desviacion']) : '—' }}</td>
                </tr>
                @if(!empty($rf['origen']))
                <tr>
                    <td colspan="4"><span class="lbl">Origen:</span> {{ $rf['origen'] }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="section section-compact">
        <div class="section-title">Conducción AV y bloqueos de rama</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="25%"><span class="lbl">Conducción AV:</span> {{ EcgPdf::label($maps['conduccion_av'], $rf['conduccion_av'] ?? null) }}</td>
                    <td width="25%">
                        <span class="lbl">BRD:</span>
                        @if($qrs['bloqueo_rama']['tiene'] ?? false)
                            <span class="check-yes">Sí</span>
                            @if(!empty($qrs['bloqueo_rama']['grado']))
                                ({{ EcgPdf::label($maps['bloqueo_grado'], $qrs['bloqueo_rama']['grado']) }})
                            @endif
                        @else<span class="check-no">No</span>@endif
                    </td>
                    <td width="25%">
                        <span class="lbl">BRI:</span>
                        @if($qrs['bloqueo_rama_izquierda']['tiene'] ?? false)
                            <span class="check-yes">Sí</span>
                            @if(!empty($qrs['bloqueo_rama_izquierda']['grado']))
                                ({{ EcgPdf::label($maps['bloqueo_grado'], $qrs['bloqueo_rama_izquierda']['grado']) }})
                            @endif
                        @else<span class="check-no">No</span>@endif
                    </td>
                    <td width="25%">
                        <span class="lbl">Fasc. anterior:</span> @if($qrs['bloqueo_fasciculo_anterior'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif
                    </td>
                </tr>
                <tr>
                    <td><span class="lbl">Fasc. posterior:</span> @if($qrs['bloqueo_fasciculo_posterior'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td><span class="lbl">Bifascicular:</span> @if($qrs['bloqueo_bifascicular'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td><span class="lbl">Trifascicular:</span> @if($qrs['bloqueo_trifascicular'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section section-compact">
        <div class="section-title">Onda P y complejo QRS</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="25%"><span class="lbl">P morfología:</span> {{ $op['morfologia'] ?? '—' }}</td>
                    <td width="25%"><span class="lbl">P duración:</span> {{ $op['duracion'] ?? '—' }} ms</td>
                    <td width="25%"><span class="lbl">QRS duración:</span> {{ $qrs['duracion'] ?? '—' }} ms</td>
                    <td width="25%"><span class="lbl">Transición:</span> {{ $qrs['transicion'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td><span class="lbl">P amplitud:</span> {{ $op['amplitud'] ?? '—' }} mV</td>
                    <td><span class="lbl">P mitrale:</span> @if($op['p_mitrale'] ?? $op['crecimiento_ai'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td><span class="lbl">P pulmonale:</span> @if($op['p_pulmonale'] ?? $op['crecimiento_ad'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td><span class="lbl">Bajo voltaje:</span> @if($qrs['bajo_voltaje'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span class="lbl">Ondas Q:</span>
                        @if($qrs['ondas_q']['tiene'] ?? false)<span class="check-yes">Sí</span> {{ $qrs['ondas_q']['localizacion'] ?? '' }}@else<span class="check-no">No</span>@endif
                    </td>
                    <td><span class="lbl">Alto voltaje VI:</span> @if($qrs['alto_voltaje_vi'] ?? $qrs['hipertrofia_vi']['tiene'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td><span class="lbl">Alto voltaje VD:</span> @if($qrs['alto_voltaje_vd'] ?? $qrs['hipertrofia_vd']['tiene'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section section-compact">
        <div class="section-title">Segmento ST y onda T</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="25%"><span class="lbl">ST normal:</span> @if($st['normal'] ?? true)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td width="25%">
                        <span class="lbl">Elevación ST:</span>
                        @if($st['elevacion']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $st['elevacion']['derivaciones'] ?? '' }} {{ $st['elevacion']['magnitud'] ?? '' }} mm
                        @else<span class="check-no">No</span>@endif
                    </td>
                    <td width="50%">
                        <span class="lbl">Depresión ST:</span>
                        @if($st['depresion']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $st['depresion']['derivaciones'] ?? '' }} {{ $st['depresion']['magnitud'] ?? '' }} mm
                        @else<span class="check-no">No</span>@endif
                    </td>
                </tr>
                <tr>
                    <td><span class="lbl">T morfología:</span> {{ $ot['morfologia'] ?? '—' }}</td>
                    <td>
                        <span class="lbl">Inversión T:</span>
                        @if(($ot['inversion']['tiene'] ?? false) || ($ot['inversiones']['tiene'] ?? false))
                            <span class="check-yes">Sí</span> — {{ $ot['inversion']['derivaciones'] ?? $ot['inversiones']['derivaciones'] ?? '' }}
                        @else<span class="check-no">No</span>@endif
                    </td>
                    <td>
                        <span class="lbl">Aplanamiento / hiperagudas:</span>
                        @if(($ot['aplanamiento']['tiene'] ?? false) || ($ot['aplanamiento'] ?? false) === true)<span class="check-yes">Aplan.</span>@else<span class="check-no">—</span>@endif
                        @if(!empty($ot['aplanamiento']['derivaciones'])) {{ $ot['aplanamiento']['derivaciones'] }} @endif
                        @if($ot['hiperagudas'] ?? $ot['picudas'] ?? false) <span class="check-yes">Hiperagudas</span> @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section section-compact">
        <div class="section-title">Arritmias</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="25%">
                        <span class="lbl">Extrasístoles SV:</span>
                        @if($esv['tiene'] ?? false)
                            <span class="check-yes">Sí</span>
                            @if(!empty($esv['frecuencia'])) — {{ EcgPdf::label($maps['frecuencia_es'], $esv['frecuencia']) }} @endif
                        @else<span class="check-no">No</span>@endif
                    </td>
                    <td width="25%"><span class="lbl">FA:</span> @if($arr['fibrilacion_auricular'] ?? $arr['fa'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td width="25%">
                        <span class="lbl">Flutter:</span>
                        @if($flutterTiene)<span class="check-yes">Sí</span>@if(!empty($flutter['conduccion'])) — {{ $flutter['conduccion'] }}@endif@else<span class="check-no">No</span>@endif
                    </td>
                    <td width="25%"><span class="lbl">Taq. auricular ectópica:</span> @if($arr['taquicardia_auricular_ectopica'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span class="lbl">Taq. SV:</span>
                        @if($tsvTiene)<span class="check-yes">Sí</span>@if(!empty($tsv['ciclo_fc'])) — {{ $tsv['ciclo_fc'] }}@endif@else<span class="check-no">No</span>@endif
                    </td>
                    <td>
                        <span class="lbl">Extrasístoles V:</span>
                        @if($ev['tiene'] ?? false)
                            <span class="check-yes">Sí</span>
                            @if(!empty($ev['frecuencia'])) — {{ EcgPdf::label($maps['frecuencia_es'], $ev['frecuencia']) }} @endif
                        @else<span class="check-no">No</span>@endif
                    </td>
                    <td>
                        <span class="lbl">Taq. ventricular:</span>
                        @if($tvTiene)<span class="check-yes">Sí</span>@if(!empty($tv['tipo'])) — {{ EcgPdf::label($maps['tv_tipo'], $tv['tipo']) }}@endif@else<span class="check-no">No</span>@endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><span class="lbl">Fibrilación ventricular:</span> @if($arr['fibrilacion_ventricular'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    @if(!empty($arr['otras']))
                    <td colspan="2"><span class="lbl">Otras arritmias:</span> {{ $arr['otras'] }}</td>
                    @else
                    <td colspan="2"></td>
                    @endif
                </tr>
            </table>
        </div>
    </div>

    @if($mpTiene)
    <div class="section section-compact">
        <div class="section-title">Marcapasos</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="25%"><span class="lbl">Tipo:</span> {{ EcgPdf::label($maps['marcapasos_tipo'], $mp['tipo'] ?? $mp['tipo_estimulacion'] ?? null) }}</td>
                    <td width="25%"><span class="lbl">Modo:</span> {{ $mp['modo'] ?? '—' }}</td>
                    <td width="25%"><span class="lbl">Captura:</span> {{ EcgPdf::label($maps['marcapasos_captura'], $mp['captura'] ?? null) }}</td>
                    <td width="25%"><span class="lbl">Sensado:</span> {{ EcgPdf::label($maps['marcapasos_sensado'], $mp['sensado'] ?? null) }}</td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    @if($imagenEcg && file_exists(public_path('storage/' . $imagenEcg)))
    @php
        $imgData = file_get_contents(public_path('storage/' . $imagenEcg));
        $imgType = mime_content_type(public_path('storage/' . $imagenEcg));
        $imgBase64 = 'data:' . $imgType . ';base64,' . base64_encode($imgData);
    @endphp
    <div class="section">
        <div class="section-title">Imagen del ECG</div>
        <div class="section-body" style="text-align:center;">
            <img src="{{ $imgBase64 }}" class="ecg-image" alt="ECG">
        </div>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Interpretación y conclusiones</div>
        <div class="section-body">
            <table style="width:100%">
                <tr>
                    <td class="two-col">
                        <div class="full-label">Interpretación</div>
                        <div class="text-block">{{ $ecg->interpretacion ?: '—' }}</div>
                    </td>
                    <td class="two-col">
                        <div class="full-label">Diagnóstico electrocardiográfico</div>
                        <div class="text-block">{{ $diagnostico ?: '—' }}</div>
                    </td>
                </tr>
                @if($comparacion)
                <tr>
                    <td colspan="2" style="padding-top:3px;">
                        <div class="full-label">Comparación con ECG previo</div>
                        <div class="text-block">{{ $comparacion }}</div>
                    </td>
                </tr>
                @endif
                @if($ecg->recomendaciones)
                <tr>
                    <td colspan="2" style="padding-top:3px;">
                        <div class="full-label">Recomendaciones</div>
                        <div class="text-block">{{ $ecg->recomendaciones }}</div>
                    </td>
                </tr>
                @endif
            </table>

            <div class="signature-block">
                @if(isset($firmaBase64) && $firmaBase64)
                <img src="{{ $firmaBase64 }}" alt="Firma"><br>
                @endif
                <div class="signature-line">
                    <div style="font-size:10px;font-weight:700;color:{!! $clinica->color_principal ?? '#0A1628' !!};">
                        {{ $medico }}
                    </div>
                    @if($ecg->cedula_medico ?? null)
                    <div style="font-size:9px;color:#64748b;">Cédula: {{ $ecg->cedula_medico }}</div>
                    @elseif($user->cedula_especialista ?? null)
                    <div style="font-size:9px;color:#64748b;">Cédula: {{ $user->cedula_especialista }}</div>
                    @endif
                    <div style="font-size:9px;color:#64748b;margin-top:2px;">Médico que realiza</div>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
