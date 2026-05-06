<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ecocardiograma</title>
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
        .three-col { width: 33%; vertical-align: top; padding: 0 4px; }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 6px 20px; background: white; border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!}; font-size: 9px; }
        .page-footer-table { width: 100%; }
        .clinic-name { font-weight: 700; color: #ef4444; }
        .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 40px; }
        .val-table { width: 100%; border-collapse: collapse; }
        .val-table td { border: 1px solid #e2e8f0; padding: 3px 6px; font-size: 9px; }
        .val-table th { background: #f1f5f9; border: 1px solid #e2e8f0; padding: 3px 6px; font-size: 8.5px; font-weight: 700; color: #64748b; }
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
                    <div class="header-title">Ecocardiograma</div>
                    <div class="header-subtitle">{{ $clinica->nombre ?? '' }}{{ $ecocardiograma->tipo_estudio ? ' — ' . $ecocardiograma->tipo_estudio : '' }}</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Registro</div>
                        <div class="header-badge-value">#{{ $paciente->registro }}</div>
                    </div>
                    <div class="header-date">{{ $ecocardiograma->fecha_estudio ? $ecocardiograma->fecha_estudio->format('d/m/Y') : '' }}</div>
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
                <td><span class="patient-label">Peso:</span> <span class="patient-value">{{ $paciente->peso ?? '—' }} kg</span></td>
                <td><span class="patient-label">Talla:</span> <span class="patient-value">{{ $paciente->talla ?? '—' }} m</span></td>
                <td><span class="patient-label">Hora:</span> <span class="patient-value">{{ $ecocardiograma->hora ?? '' }}</span></td>
                <td><span class="patient-label">Calidad imagen:</span> <span class="patient-value">{{ $ecocardiograma->calidad_imagen ?? '—' }}</span></td>
            </tr>
            <tr>
                <td colspan="3"><span class="patient-label">Médico:</span> <span class="patient-value">{{ $ecocardiograma->medico_realiza ?? ($user->nombre_completo ?? $user->name ?? '') }}</span></td>
                <td colspan="3"><span class="patient-label">Indicación:</span> <span class="patient-value">{{ $ecocardiograma->indicacion ?? '—' }}</span></td>
            </tr>
        </table>
    </div>

    <!-- VENTRÍCULO IZQUIERDO -->
    @php $vi = $ecocardiograma->ventriculo_izquierdo ?? []; @endphp
    <div class="section">
        <div class="section-title">Ventrículo izquierdo</div>
        <div class="section-body">
            <table class="val-table">
                <tr>
                    <th>DDVI (mm)</th><th>DSVI (mm)</th><th>VD (ml)</th><th>VS (ml)</th>
                    <th>Septum (mm)</th><th>PP (mm)</th><th>Masa (g)</th><th>i-Masa (g/m²)</th>
                    <th>GR</th><th>FEVI (%)</th><th>FA (%)</th><th>GLS (%)</th>
                </tr>
                <tr>
                    <td>{{ $vi['diametro_diastolico'] ?? '—' }}</td>
                    <td>{{ $vi['diametro_sistolico'] ?? '—' }}</td>
                    <td>{{ $vi['volumen_diastolico'] ?? '—' }}</td>
                    <td>{{ $vi['volumen_sistolico'] ?? '—' }}</td>
                    <td>{{ $vi['septum'] ?? '—' }}</td>
                    <td>{{ $vi['pared_posterior'] ?? '—' }}</td>
                    <td>{{ $vi['masa'] ?? '—' }}</td>
                    <td>{{ $vi['indice_masa'] ?? '—' }}</td>
                    <td>{{ $vi['grosor_relativo'] ?? '—' }}</td>
                    <td>{{ $vi['fevi'] ?? '—' }}{{ $vi['fevi_metodo'] ? ' (' . $vi['fevi_metodo'] . ')' : '' }}</td>
                    <td>{{ $vi['fraccion_acortamiento'] ?? '—' }}</td>
                    <td>{{ $vi['gls'] ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- MOTILIDAD + FUNCIÓN DIASTÓLICA -->
    @php $mot = $ecocardiograma->motilidad_regional ?? []; $dias = $ecocardiograma->funcion_diastolica ?? []; @endphp
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:40%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Motilidad regional</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="50%">
                                    <span class="lbl">Hipoquinesia:</span>
                                    @if($mot['hipoquinesia']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $mot['hipoquinesia']['segmentos'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                                <td width="50%">
                                    <span class="lbl">Aquinesia:</span>
                                    @if($mot['aquinesia']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $mot['aquinesia']['segmentos'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="lbl">Disquinesia:</span>
                                    @if($mot['disquinesia']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $mot['disquinesia']['segmentos'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                                <td>
                                    @if($mot['descripcion'] ?? '')
                                    <span class="lbl">Desc.:</span> {{ $mot['descripcion'] }}
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:60%;vertical-align:top;padding-left:4px;">
                <div class="section">
                    <div class="section-title">Función diastólica</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="20%"><span class="lbl">E mitral:</span> {{ $dias['e_mitral'] ?? '—' }}</td>
                                <td width="20%"><span class="lbl">A mitral:</span> {{ $dias['a_mitral'] ?? '—' }}</td>
                                <td width="20%"><span class="lbl">E/A:</span> {{ $dias['relacion_ea'] ?? '—' }}</td>
                                <td width="20%"><span class="lbl">e' sept:</span> {{ $dias['e_prima_septal'] ?? '—' }}</td>
                                <td width="20%"><span class="lbl">e' lat:</span> {{ $dias['e_prima_lateral'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">E/e':</span> {{ $dias['relacion_e_e_prima'] ?? '—' }}</td>
                                <td><span class="lbl">TD (ms):</span> {{ $dias['tiempo_desaceleracion'] ?? '—' }}</td>
                                <td><span class="lbl">TRIV (ms):</span> {{ $dias['triv'] ?? '—' }}</td>
                                <td colspan="2"><span class="lbl">Patrón:</span> {{ $dias['patron'] ?? '—' }} {{ $dias['grado_disfuncion'] ? '— Grado ' . $dias['grado_disfuncion'] : '' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- VD + AURÍCULAS -->
    @php $vd = $ecocardiograma->ventriculo_derecho ?? []; $aur = $ecocardiograma->auriculas ?? []; @endphp
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:50%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Ventrículo derecho</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="33%"><span class="lbl">Diam. basal:</span> {{ $vd['diametro_basal'] ?? '—' }} mm</td>
                                <td width="33%"><span class="lbl">Diam. medio:</span> {{ $vd['diametro_medio'] ?? '—' }} mm</td>
                                <td width="33%"><span class="lbl">Longitud:</span> {{ $vd['longitud'] ?? '—' }} mm</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">TAPSE:</span> {{ $vd['tapse'] ?? '—' }} mm</td>
                                <td><span class="lbl">Onda S tricusp.:</span> {{ $vd['onda_s_tricuspide'] ?? '—' }} cm/s</td>
                                <td><span class="lbl">FAC:</span> {{ $vd['fac'] ?? '—' }}% — {{ $vd['funcion'] ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:4px;">
                <div class="section">
                    <div class="section-title">Aurículas</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="50%">
                                    <span class="lbl">AI — Diám.:</span> {{ $aur['ai']['diametro'] ?? '—' }} mm &nbsp;
                                    <span class="lbl">Vol.:</span> {{ $aur['ai']['volumen'] ?? '—' }} ml &nbsp;
                                    <span class="lbl">iVol.:</span> {{ $aur['ai']['volumen_indexado'] ?? '—' }} ml/m²
                                    @if($aur['ai']['dilatacion'] ?? false) <span class="check-yes">(dilatada)</span>@endif
                                </td>
                                <td width="50%">
                                    <span class="lbl">AD — Área:</span> {{ $aur['ad']['area'] ?? '—' }} cm²
                                    @if($aur['ad']['dilatacion'] ?? false) <span class="check-yes">(dilatada)</span>@endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- VÁLVULAS -->
    @php
        $vm  = $ecocardiograma->valvula_mitral ?? [];
        $va  = $ecocardiograma->valvula_aortica ?? [];
        $vt  = $ecocardiograma->valvula_tricuspide ?? [];
        $vp  = $ecocardiograma->valvula_pulmonar ?? [];
    @endphp
    <div class="section">
        <div class="section-title">Válvulas</div>
        <div class="section-body">
            <table style="width:100%">
                <tr>
                    <td class="two-col">
                        <div class="full-label">Mitral</div>
                        <table class="row-table">
                            <tr>
                                <td width="50%"><span class="lbl">Morfología:</span> {{ $vm['morfologia'] ?? '—' }}</td>
                                <td width="50%"><span class="lbl">Área:</span> {{ $vm['area'] ?? '—' }} cm²</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="lbl">Estenosis:</span>
                                    @if($vm['estenosis']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $vm['estenosis']['grado'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                                <td>
                                    <span class="lbl">Insuf.:</span>
                                    @if($vm['insuficiencia']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $vm['insuficiencia']['grado'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                            @if($vm['prolapso']['tiene'] ?? false)
                            <tr><td colspan="2"><span class="lbl">Prolapso:</span> <span class="check-yes">Sí</span> — valva {{ $vm['prolapso']['valva'] ?? '' }}</td></tr>
                            @endif
                        </table>
                    </td>
                    <td class="two-col">
                        <div class="full-label">Aórtica</div>
                        <table class="row-table">
                            <tr>
                                <td width="50%"><span class="lbl">Morfología:</span> {{ $va['morfologia'] ?? '—' }}</td>
                                <td width="50%"><span class="lbl">Área:</span> {{ $va['area'] ?? '—' }} cm²</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Vel. pico:</span> {{ $va['velocidad_pico'] ?? '—' }} m/s &nbsp; <span class="lbl">Grd.:</span> {{ $va['gradiente_medio'] ?? '—' }} mmHg</td>
                                <td>
                                    <span class="lbl">Estenosis:</span>
                                    @if($va['estenosis']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $va['estenosis']['grado'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <span class="lbl">Insuf.:</span>
                                    @if($va['insuficiencia']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $va['insuficiencia']['grado'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="two-col" style="padding-top:6px;">
                        <div class="full-label">Tricúspide</div>
                        <table class="row-table">
                            <tr>
                                <td width="50%"><span class="lbl">Morfología:</span> {{ $vt['morfologia'] ?? '—' }}</td>
                                <td width="50%">
                                    <span class="lbl">Insuf.:</span>
                                    @if($vt['insuficiencia']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $vt['insuficiencia']['grado'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                            <tr>
                                <td><span class="lbl">PSAP:</span> {{ $vt['psap'] ?? '—' }} mmHg</td>
                                <td><span class="lbl">P. AD est.:</span> {{ $vt['presion_ad_estimada'] ?? '—' }} mmHg</td>
                            </tr>
                        </table>
                    </td>
                    <td class="two-col" style="padding-top:6px;">
                        <div class="full-label">Pulmonar</div>
                        <table class="row-table">
                            <tr>
                                <td width="50%"><span class="lbl">Morfología:</span> {{ $vp['morfologia'] ?? '—' }}</td>
                                <td width="50%">
                                    <span class="lbl">Insuf.:</span>
                                    @if($vp['insuficiencia']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $vp['insuficiencia']['grado'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Vel. pico:</span> {{ $vp['velocidad_pico'] ?? '—' }} m/s</td>
                                <td><span class="lbl">Gradiente:</span> {{ $vp['gradiente'] ?? '—' }} mmHg</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- AORTA + PERICARDIO -->
    @php $aor = $ecocardiograma->aorta ?? []; $per = $ecocardiograma->pericardio ?? []; @endphp
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:55%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Aorta</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="25%"><span class="lbl">Raíz:</span> {{ $aor['raiz'] ?? '—' }} mm</td>
                                <td width="25%"><span class="lbl">Ascendente:</span> {{ $aor['ascendente'] ?? '—' }} mm</td>
                                <td width="25%"><span class="lbl">Arco:</span> {{ $aor['arco'] ?? '—' }} mm</td>
                                <td width="25%"><span class="lbl">Descendente:</span> {{ $aor['descendente'] ?? '—' }} mm</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Aneurisma:</span> @if($aor['aneurisma'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                                <td><span class="lbl">Coartación:</span> @if($aor['coartacion'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                                <td><span class="lbl">Disección:</span> @if($aor['diseccion'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:45%;vertical-align:top;padding-left:4px;">
                <div class="section">
                    <div class="section-title">Pericardio</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="50%"><span class="lbl">Aspecto:</span> {{ $per['aspecto'] ?? '—' }}</td>
                                <td width="50%">
                                    <span class="lbl">Derrame:</span>
                                    @if($per['derrame']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $per['derrame']['cantidad'] ?? '' }} {{ $per['derrame']['localizacion'] ?? '' }}@else<span class="check-no">No</span>@endif
                                </td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Taponamiento:</span> @if($per['taponamiento'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                                <td><span class="lbl">Constricción:</span> @if($per['constriccion'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- HALLAZGOS ADICIONALES -->
    @php $hall = $ecocardiograma->hallazgos_adicionales ?? []; @endphp
    @if(($hall['trombo']['tiene'] ?? false) || ($hall['masa']['tiene'] ?? false) || ($hall['fop'] ?? false) || ($hall['cia'] ?? false) || ($hall['civ'] ?? false) || ($hall['otros'] ?? ''))
    <div class="section">
        <div class="section-title">Hallazgos adicionales</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="25%"><span class="lbl">Trombo:</span> @if($hall['trombo']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $hall['trombo']['localizacion'] ?? '' }}@else<span class="check-no">No</span>@endif</td>
                    <td width="25%"><span class="lbl">Masa:</span> @if($hall['masa']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $hall['masa']['descripcion'] ?? '' }}@else<span class="check-no">No</span>@endif</td>
                    <td width="15%"><span class="lbl">FOP:</span> @if($hall['fop'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td width="15%"><span class="lbl">CIA:</span> @if($hall['cia'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                    <td width="20%"><span class="lbl">CIV:</span> @if($hall['civ'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                </tr>
                @if($hall['otros'] ?? '')
                <tr><td colspan="5"><span class="lbl">Otros:</span> {{ $hall['otros'] }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    @endif

    <!-- CONCLUSIONES Y RECOMENDACIONES -->
    <div class="section">
        <div class="section-title">Conclusiones y recomendaciones</div>
        <div class="section-body">
            <table style="width:100%"><tr>
                <td class="two-col">
                    <div class="full-label">Conclusiones</div>
                    <div class="text-block">{{ $ecocardiograma->conclusiones ?? '—' }}</div>
                </td>
                <td class="two-col">
                    <div class="full-label">Recomendaciones</div>
                    <div class="text-block">{{ $ecocardiograma->recomendaciones ?? '—' }}</div>
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
                        {{ $ecocardiograma->medico_realiza ?? $user->nombre_con_titulo ?? $user->name ?? '' }}
                    </div>
                    @if($ecocardiograma->cedula_medico ?? null)
                    <div style="font-size:9px;color:#64748b;">Cédula: {{ $ecocardiograma->cedula_medico }}</div>
                    @elseif($user->cedula_especialista ?? null)
                    <div style="font-size:9px;color:#64748b;">Cédula: {{ $user->cedula_especialista }}</div>
                    @endif
                    <div style="font-size:9px;color:#64748b;margin-top:2px;">Médico que realiza</div>
                </div>
            </td>
            <td style="width:25%;"></td>
        </tr>
    </table>

</div>
</body>
</html>
