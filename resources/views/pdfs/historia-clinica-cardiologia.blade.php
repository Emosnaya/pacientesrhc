<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historia Clínica Cardiológica</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}');
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            margin: 20px 25px;
        }
        table { border-collapse: collapse; }
        /* === HEADER === */
        .header {
            width: 100%;
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 8px 12px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-cell { width: 60px; padding-right: 12px !important; }
        .header-logo {
            width: 45px; height: 45px;
            background: white; border-radius: 6px;
            padding: 5px; text-align: center;
        }
        .header-logo img { max-height: 35px; max-width: 35px; }
        .header-title { font-size: 15px; font-weight: 700; color: white; }
        .header-subtitle { font-size: 9px; color: #94a3b8; }
        .header-meta-cell { text-align: right; width: 120px; }
        .header-badge {
            background: rgba(255,255,255,0.15);
            padding: 5px 10px; border-radius: 5px;
            display: inline-block; margin-bottom: 4px;
        }
        .header-badge-label { font-size: 8px; text-transform: uppercase; color: #94a3b8; }
        .header-badge-value { font-size: 12px; font-weight: 700; color: white; }
        .header-date { font-size: 9px; color: #94a3b8; }
        /* === PATIENT CARD === */
        .patient-card {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 10px 12px; margin-bottom: 8px;
        }
        .patient-table { width: 100%; border-collapse: collapse; }
        .patient-table td { padding: 2px 6px; font-size: 10px; }
        .patient-name { font-size: 13px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; margin-bottom: 6px; }
        .patient-label { color: #64748b; font-size: 9px; }
        .patient-value { font-weight: 600; color: #334155; }
        /* === SECTIONS === */
        .section {
            margin-bottom: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .section-title {
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            color: white;
            font-size: 9px;
            font-weight: 700;
            padding: 4px 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-body { padding: 8px 10px; }
        .section-subtitle {
            background: #e2e8f0;
            color: #334155;
            font-size: 9px;
            font-weight: 700;
            padding: 3px 8px;
            margin: 4px -10px;
        }
        .row-table { width: 100%; border-collapse: collapse; }
        .row-table td { padding: 2px 4px; vertical-align: top; font-size: 9.5px; }
        .lbl { color: #64748b; font-size: 9px; white-space: nowrap; }
        .val { font-weight: 600; }
        .check-yes { color: #16a34a; font-weight: 700; }
        .check-no { color: #94a3b8; }
        .text-block {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 4px; padding: 5px 8px;
            font-size: 9.5px; min-height: 20px;
        }
        .vitals-table { width: 100%; border-collapse: collapse; }
        .vitals-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            text-align: center;
            font-size: 9.5px;
        }
        .vitals-table th {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            font-size: 8.5px;
            color: #64748b;
            font-weight: 700;
        }
        .pulses-table { width: 100%; border-collapse: collapse; }
        .pulses-table td, .pulses-table th {
            border: 1px solid #e2e8f0;
            padding: 3px 6px;
            font-size: 9px;
            text-align: center;
        }
        .pulses-table th { background: #f1f5f9; font-weight: 700; color: #64748b; }
        /* === PAGE FOOTER === */
        .page-footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            padding: 6px 20px;
            background: white;
            border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
            font-size: 9px;
        }
        .page-footer-table { width: 100%; }
        .clinic-name { font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; }
        .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 40px; }
        .two-col { width: 50%; vertical-align: top; padding: 0 4px; }
        .mb-4 { margin-bottom: 4px; }
        .mt-4 { margin-top: 4px; }
        .full-label { font-size: 9px; color: #64748b; margin-bottom: 2px; }
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
                    <div class="header-title">Historia Clínica Cardiológica</div>
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
                <td><span class="patient-label">Género:</span> <span class="patient-value">{{ $paciente->genero == 1 ? 'Hombre' : 'Mujer' }}</span></td>
                <td><span class="patient-label">F. Nacimiento:</span> <span class="patient-value">{{ $paciente->fechaNacimiento }}</span></td>
                <td><span class="patient-label">Estado Civil:</span> <span class="patient-value">{{ $paciente->estadoCivil }}</span></td>
                <td><span class="patient-label">Teléfono:</span> <span class="patient-value">{{ $paciente->telefono }}</span></td>
            </tr>
            <tr>
                <td colspan="2"><span class="patient-label">Médico:</span> <span class="patient-value">{{ $user->name ?? '' }}</span></td>
                <td><span class="patient-label">Hora:</span> <span class="patient-value">{{ $historia->hora ?? '' }}</span></td>
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

    <!-- ANTECEDENTES CARDIOVASCULARES -->
    @php
        $acv = $historia->antecedentes_cardiovasculares ?? [];
        $anpObesidad = $historia->antecedentes_no_patologicos ?? [];
        $frObesidad = $historia->factores_riesgo ?? [];
        if (!($acv['obesidad'] ?? false) && (($anpObesidad['obesidad'] ?? false) || ($frObesidad['obesidad'] ?? false))) {
            $acv['obesidad'] = true;
        }
    @endphp
    <div class="section">
        <div class="section-title">Antecedentes cardiovasculares</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="25%">
                        <span class="lbl">IAM:</span>
                        @if($acv['iam']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['iam']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="25%">
                        <span class="lbl">Angina:</span>
                        @if($acv['angina']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['angina']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="25%">
                        <span class="lbl">Arritmias:</span>
                        @if($acv['arritmias']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['arritmias']['tipo'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="25%">
                        <span class="lbl">IC (NYHA):</span>
                        @if($acv['ic']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['ic']['clase_nyha'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="lbl">Valvulopatía:</span>
                        @if($acv['valvulopatia']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['valvulopatia']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td>
                        <span class="lbl">Card. Congénita:</span>
                        @if($acv['cardiopatia_congenita']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['cardiopatia_congenita']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td>
                        <span class="lbl">Dispositivo:</span>
                        @if($acv['dispositivo']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['dispositivo']['tipo'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td>
                        <span class="lbl">Cx Cardiaca:</span>
                        @if($acv['cirugia_cardiaca']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['cirugia_cardiaca']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="lbl">Cateterismo:</span>
                        @if($acv['cateterismo']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['cateterismo']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td>
                        <span class="lbl">Angioplastia:</span>
                        @if($acv['angioplastia']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['angioplastia']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td colspan="2">
                        <span class="lbl">Otros:</span> {{ $acv['otros'] ?? '—' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span class="lbl">Cirugías:</span>
                        @if(!empty($acv['cirugias'] ?? []))
                            <div class="text-block">{!! nl2br(e(implode("\n", $acv['cirugias']))) !!}</div>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <span class="lbl">Transfusiones:</span>
                        @if($acv['transfusiones']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['transfusiones']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td>
                        <span class="lbl">Enf. Respiratorias:</span>
                        @if($acv['enfermedades_respiratorias']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['enfermedades_respiratorias']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="lbl">Gastrointestinales:</span>
                        @if($acv['gastrointestinales']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['gastrointestinales']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td>
                        <span class="lbl">Enf. Renales:</span>
                        @if($acv['enfermedades_renales']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['enfermedades_renales']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td colspan="2">
                        <span class="lbl">Traumatismos / Accidentes:</span>
                        @if($acv['traumatismos_accidentes']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $acv['traumatismos_accidentes']['detalle'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <span class="lbl">Obesidad:</span>
                        @if($acv['obesidad'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ANTECEDENTES GINECOOBSTÉTRICOS (solo pacientes femeninas: genero == 0) -->
    @php $gineco = $historia->antecedentes_gineco_obstetricos ?? []; @endphp
    @if(isset($paciente->genero) && (int)$paciente->genero === 0 && !empty($gineco))
    <div class="section">
        <div class="section-title">Antecedentes Ginecoobstétricos</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="20%"><span class="lbl">Menarquia:</span> {{ $gineco['menarquia'] ?? '—' }} años</td>
                    <td width="20%"><span class="lbl">FUM:</span> {{ $gineco['fum'] ?? '—' }}</td>
                    <td width="20%">
                        <span class="lbl">Ciclos:</span>
                        {{ ($gineco['ciclos']['regulares'] ?? true) ? 'Regulares' : 'Irregulares' }}
                        @if($gineco['ciclos']['duracion'] ?? '') {{ $gineco['ciclos']['duracion'] }} días @endif
                    </td>
                    <td width="20%">
                        <span class="lbl">Menopausia:</span>
                        @if($gineco['menopausia']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $gineco['menopausia']['edad'] ?? '' }} años
                            ({{ $gineco['menopausia']['tipo'] ?? '' }})
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="20%">
                        <span class="lbl">T. Hormonal:</span>
                        @if($gineco['terapia_hormonal']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $gineco['terapia_hormonal']['tipo'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        @php $fo = $gineco['formula_obstetrica'] ?? []; @endphp
                        <span class="lbl">Fórmula obstétrica:</span>
                        G<strong>{{ $fo['gestas'] ?? '0' }}</strong>
                        P<strong>{{ $fo['partos'] ?? '0' }}</strong>
                        C<strong>{{ $fo['cesareas'] ?? '0' }}</strong>
                        A<strong>{{ $fo['abortos'] ?? '0' }}</strong>
                        HV<strong>{{ $fo['hijos_vivos'] ?? '0' }}</strong>
                        &nbsp;&nbsp;
                        @php $comp = $gineco['complicaciones'] ?? []; @endphp
                        @if(in_array(true, $comp, true))
                            <span class="lbl">Compl. obstétricas:</span>
                            @if($comp['preeclampsia'] ?? false) <span class="check-yes">Preeclampsia</span> @endif
                            @if($comp['eclampsia'] ?? false) <span class="check-yes">Eclampsia</span> @endif
                            @if($comp['diabetes_gestacional'] ?? false) <span class="check-yes">DG</span> @endif
                            @if($comp['parto_pretermino'] ?? false) <span class="check-yes">Parto pretérmino</span> @endif
                            @if($comp['perdida_gestacional_recurrente'] ?? false) <span class="check-yes">Pérd. gestacional</span> @endif
                        @endif
                    </td>
                </tr>
                @if($gineco['otros'] ?? '')
                <tr><td colspan="5"><span class="lbl">Otros:</span> {{ $gineco['otros'] }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    @endif

    <!-- ANTECEDENTES NO PATOLÓGICOS + ANTECEDENTES FAMILIARES -->
    @php
        $anp = $historia->antecedentes_no_patologicos ?? [];
        if (empty($anp)) {
            $frLegacy = $historia->factores_riesgo ?? [];
            $anp = [
                'tabaquismo' => $frLegacy['tabaquismo'] ?? ['tiene' => false],
                'actividad_fisica' => ['tiene' => false, 'detalle' => ''],
                'alcoholismo' => ['tiene' => false, 'detalle' => ''],
                'consumo_drogas' => ['tiene' => false, 'detalle' => ''],
                'sedentarismo' => $frLegacy['sedentarismo'] ?? false,
                'estres' => $frLegacy['estres'] ?? false,
                'otros' => $frLegacy['otros'] ?? '',
            ];
        }
        $af = $historia->antecedentes_familiares ?? [];
    @endphp
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:55%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Antecedentes personales no patológicos</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="50%">
                                    <span class="lbl">Tabaquismo:</span>
                                    @if($anp['tabaquismo']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> {{ $anp['tabaquismo']['estado'] ?? '' }} {{ $anp['tabaquismo']['cigarros_dia'] ?? '' }} cig/día
                                    @else <span class="check-no">No</span> @endif
                                </td>
                                <td width="50%">
                                    <span class="lbl">Actividad física:</span>
                                    @if($anp['actividad_fisica']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> — {{ $anp['actividad_fisica']['detalle'] ?? '' }}
                                    @else <span class="check-no">No</span> @endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="lbl">Alcoholismo:</span>
                                    @if($anp['alcoholismo']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> — {{ $anp['alcoholismo']['detalle'] ?? '' }}
                                    @else <span class="check-no">No</span> @endif
                                </td>
                                <td>
                                    <span class="lbl">Consumo de drogas:</span>
                                    @if($anp['consumo_drogas']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> — {{ $anp['consumo_drogas']['detalle'] ?? '' }}
                                    @else <span class="check-no">No</span> @endif
                                </td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Sedentarismo:</span> @if($anp['sedentarismo'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                                <td><span class="lbl">Estrés:</span> @if($anp['estres'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                            </tr>
                            @if($anp['otros'] ?? '')
                            <tr><td colspan="2"><span class="lbl">Otros:</span> {{ $anp['otros'] }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:45%;vertical-align:top;padding-left:4px;">
                <div class="section">
                    <div class="section-title">Antecedentes familiares</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td>
                                    <span class="lbl">Card. Isquémica:</span>
                                    @if($af['cardiopatia_isquemica']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> ({{ $af['cardiopatia_isquemica']['parentesco'] ?? '' }})
                                    @else <span class="check-no">No</span> @endif
                                </td>
                                <td>
                                    <span class="lbl">Muerte súbita:</span>
                                    @if($af['muerte_subita']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> ({{ $af['muerte_subita']['parentesco'] ?? '' }})
                                    @else <span class="check-no">No</span> @endif
                                </td>
                            </tr>
                            <tr>
                                <td><span class="lbl">HTA:</span> @if($af['hta'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                                <td><span class="lbl">DM:</span> @if($af['dm'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Dislipidemia:</span> @if($af['dislipidemia'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                                <td><span class="lbl">Miocardiopatía:</span> @if($af['miocardiopatia'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                            </tr>
                            @if($af['otros'] ?? '')
                            <tr><td colspan="2"><span class="lbl">Otros:</span> {{ $af['otros'] }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- MEDICACIÓN -->
    <div class="section">
        <div class="section-title">Medicación y alergias</div>
        <div class="section-body">
            <table style="width:100%"><tr>
                <td class="two-col">
                    <div class="full-label">Medicación cardiovascular</div>
                    <div class="text-block">{{ $historia->medicacion_cardiovascular ?? '—' }}</div>
                </td>
                <td class="two-col">
                    <div class="full-label">Otros medicamentos</div>
                    <div class="text-block">{{ $historia->medicacion_otros ?? '—' }}</div>
                </td>
            </tr><tr>
                <td colspan="2" style="padding-top:4px;">
                    <div class="full-label">Alergias</div>
                    <div class="text-block">{{ $historia->alergias ?? '—' }}</div>
                </td>
            </tr></table>
        </div>
    </div>

    <!-- SIGNOS VITALES -->
    <div class="section">
        <div class="section-title">Signos vitales y somatometría</div>
        <div class="section-body">
            <table class="vitals-table">
                <tr>
                    <th>TA Sistólica</th>
                    <th>TA Diastólica</th>
                    <th>FC (lpm)</th>
                    <th>FR (rpm)</th>
                    <th>SpO2 (%)</th>
                    <th>Temp (°C)</th>
                    <th>Peso (kg)</th>
                    <th>Talla (m)</th>
                    <th>IMC</th>
                    <th>Perímetro Abd.</th>
                </tr>
                <tr>
                    <td>{{ $historia->ta_sistolica ?? '—' }}</td>
                    <td>{{ $historia->ta_diastolica ?? '—' }}</td>
                    <td>{{ $historia->fc ?? '—' }}</td>
                    <td>{{ $historia->fr ?? '—' }}</td>
                    <td>{{ $historia->spo2 ?? '—' }}</td>
                    <td>{{ $historia->temperatura ?? '—' }}</td>
                    <td>{{ $historia->peso ?? '—' }}</td>
                    <td>{{ $historia->talla ?? '—' }}</td>
                    <td>{{ $historia->imc ?? '—' }}</td>
                    <td>{{ $historia->perimetro_abdominal ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- EXPLORACIÓN FÍSICA -->
    @php
        $exploracionFisica = $historia->exploracion_fisica;
        if (empty($exploracionFisica)) {
            $expLegacy = $historia->exploracion_cardiovascular ?? [];
            $exploracionFisica = $expLegacy['otros'] ?? '';
        }
    @endphp
    <div class="section">
        <div class="section-title">Exploración física</div>
        <div class="section-body">
            <div class="text-block">{{ $exploracionFisica ?: '—' }}</div>
        </div>
    </div>

    <!-- ESTUDIOS PREVIOS + LABORATORIOS -->
    @php $est = $historia->estudios_previos ?? []; $lab = $historia->laboratorios ?? []; @endphp
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:50%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Estudios previos</div>
                    <div class="section-body">
                        <table class="row-table">
                            @foreach([
                                'ecg' => 'ECG',
                                'ecocardiograma' => 'Ecocardiograma',
                                'prueba_esfuerzo' => 'Prueba de esfuerzo',
                                'holter' => 'Holter',
                                'mapa' => 'MAPA',
                                'cateterismo' => 'Cateterismo',
                                'angiotac' => 'AngioTAC',
                                'rmn_cardiaca' => 'RMN Cardiaca',
                                'radiografia_torax' => 'Radiografía Tórax',
                                'perfusion_miocardica' => 'Perfusión Miocárdica',
                                'medicina_nuclear' => 'Medicina Nuclear',
                                'angiotac_coronarias' => 'Angio-TAC Coronarias',
                            ] as $key => $label)
                            @if($est[$key] ?? '')
                            <tr>
                                <td width="40%"><span class="lbl">{{ $label }}:</span></td>
                                <td>{{ $est[$key] }}</td>
                            </tr>
                            @endif
                            @endforeach
                            @if($est['otros'] ?? '')
                            <tr><td><span class="lbl">Otros:</span></td><td>{{ $est['otros'] }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:4px;">
                <div class="section">
                    <div class="section-title">Laboratorios</div>
                    <div class="section-body">
                        <table class="row-table">
                            @php
                                $labFilas = [
                                    [
                                        'hemoglobina' => 'Hemoglobina',
                                        'leucocitos' => 'Leucocitos',
                                        'plaquetas' => 'Plaquetas',
                                        'hematocrito' => 'Hematocrito',
                                    ],
                                    [
                                        'glucosa' => 'Glucosa',
                                        'bun' => 'BUN',
                                        'creatinina' => 'Creatinina',
                                        'acido_urico' => 'Ácido Úrico',
                                    ],
                                    [
                                        'colesterol_total' => 'Colesterol Total',
                                        'ldl' => 'LDL',
                                        'hdl' => 'HDL',
                                        'trigliceridos' => 'Triglicéridos',
                                    ],
                                    ['hba1c' => 'HbA1c'],
                                    [
                                        'bnp' => 'BNP/NT-proBNP',
                                        'troponinas' => 'Troponinas',
                                        'dimero_d' => 'Dímero D',
                                    ],
                                ];
                            @endphp
                            @foreach($labFilas as $fila)
                                @php
                                    $valores = [];
                                    foreach ($fila as $key => $label) {
                                        if ($lab[$key] ?? '') {
                                            $valores[] = $label . ': ' . $lab[$key];
                                        }
                                    }
                                @endphp
                                @if(count($valores))
                                <tr>
                                    <td colspan="2">{{ implode(' &nbsp;|&nbsp; ', $valores) }}</td>
                                </tr>
                                @endif
                            @endforeach
                            @php
                                $electro = [];
                                foreach (['cloro' => 'Cloro', 'potasio' => 'Potasio', 'magnesio' => 'Magnesio', 'calcio' => 'Calcio'] as $k => $l) {
                                    if ($lab['electrolitos'][$k] ?? '') $electro[] = $l . ': ' . $lab['electrolitos'][$k];
                                }
                                foreach (['tsh' => 'TSH', 't3' => 'T3', 't4' => 'T4', 't3_libre' => 'T3 libre'] as $k => $l) {
                                    if ($lab['perfil_tiroideo'][$k] ?? '') $electro[] = $l . ': ' . $lab['perfil_tiroideo'][$k];
                                }
                            @endphp
                            @if(count($electro))
                            <tr>
                                <td colspan="2">{{ implode(' &nbsp;|&nbsp; ', $electro) }}</td>
                            </tr>
                            @endif
                            @if($lab['otros'] ?? '')
                            <tr><td><span class="lbl">Otros:</span></td><td>{{ $lab['otros'] }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- DIAGNÓSTICOS -->
    <div class="section">
        <div class="section-title">Diagnóstico</div>
        <div class="section-body">
            <table style="width:100%"><tr>
                <td class="two-col">
                    <div class="full-label">Diagnóstico principal</div>
                    <div class="text-block">{{ $historia->diagnostico_principal ?? '—' }}</div>
                </td>
                <td class="two-col">
                    <div class="full-label">CIE-10</div>
                    <div class="text-block">{{ $historia->diagnostico_cie10 ?? '—' }}</div>
                </td>
            </tr><tr>
                <td colspan="2" style="padding-top:4px;">
                    <div class="full-label">Diagnósticos secundarios</div>
                    <div class="text-block">{{ $historia->diagnosticos_secundarios ?? '—' }}</div>
                </td>
            </tr></table>
        </div>
    </div>

    <!-- PLAN DE TRATAMIENTO -->
    <div class="section">
        <div class="section-title">Plan de tratamiento</div>
        <div class="section-body">
            <table style="width:100%">
                <tr>
                    <td class="two-col">
                        <div class="full-label">Plan farmacológico</div>
                        <div class="text-block">{{ $historia->plan_farmacologico ?? '—' }}</div>
                    </td>
                    <td class="two-col">
                        <div class="full-label">Plan no farmacológico</div>
                        <div class="text-block">{{ $historia->plan_no_farmacologico ?? '—' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="two-col" style="padding-top:4px;">
                        <div class="full-label">Estudios solicitados</div>
                        <div class="text-block">{{ $historia->estudios_solicitados ?? '—' }}</div>
                    </td>
                    <td class="two-col" style="padding-top:4px;">
                        <div class="full-label">Interconsultas</div>
                        <div class="text-block">{{ $historia->interconsultas ?? '—' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="two-col" style="padding-top:4px;">
                        <div class="full-label">Indicaciones</div>
                        <div class="text-block">{{ $historia->indicaciones ?? '—' }}</div>
                    </td>
                    <td class="two-col" style="padding-top:4px;">
                        <div class="full-label">Pronóstico</div>
                        <div class="text-block">
                            @php
                                $escalaPronostico = [
                                    'excelente' => 'Excelente',
                                    'bueno' => 'Bueno',
                                    'reservado' => 'Reservado',
                                    'malo' => 'Malo',
                                    'grave' => 'Grave',
                                ];
                                $pronosticoTexto = $escalaPronostico[$historia->pronostico ?? ''] ?? ($historia->pronostico ?: '—');
                            @endphp
                            {{ $pronosticoTexto }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="two-col" style="padding-top:4px;">
                        <div class="full-label">Próxima cita</div>
                        <div class="text-block">{{ $historia->proxima_cita ? $historia->proxima_cita->format('d/m/Y') : '—' }}</div>
                    </td>
                    <td class="two-col" style="padding-top:4px;">
                        <div class="full-label">Notas adicionales</div>
                        <div class="text-block">{{ $historia->notas_adicionales ?? '—' }}</div>
                    </td>
                </tr>
            </table>
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
                        {{ $user->nombre_con_titulo ?? $user->name ?? '' }}
                        @if($user->cedula_especialista ?? null) — {{ $user->cedula_especialista }}@endif
                    </div>
                    <div style="font-size:9px;color:#64748b;margin-top:2px;">Firma del médico</div>
                </div>
            </td>
            <td style="width:25%;"></td>
        </tr>
    </table>

</div><!-- end content-wrapper -->
</body>
</html>
