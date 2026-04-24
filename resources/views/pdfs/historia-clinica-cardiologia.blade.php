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
            background: #0A1628;
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
        .patient-name { font-size: 13px; font-weight: 700; color: #0A1628; margin-bottom: 6px; }
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
            background: #0A1628;
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
            border-top: 2px solid #0A1628;
            font-size: 9px;
        }
        .page-footer-table { width: 100%; }
        .clinic-name { font-weight: 700; color: #ef4444; }
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
                Generado con <strong style="color:#0A1628;">Lynkamed</strong>
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
                @if($historia->clasificacion_riesgo)
                <td colspan="2"><span class="patient-label">Clasificación de riesgo:</span> <span class="patient-value">{{ $historia->clasificacion_riesgo }}</span></td>
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

    <!-- ANTECEDENTES CARDIOVASCULARES -->
    @php $acv = $historia->antecedentes_cardiovasculares ?? []; @endphp
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
            </table>
        </div>
    </div>

    <!-- FACTORES DE RIESGO + ANTECEDENTES FAMILIARES -->
    @php $fr = $historia->factores_riesgo ?? []; $af = $historia->antecedentes_familiares ?? []; @endphp
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:55%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Factores de riesgo</div>
                    <div class="section-body">
                        <table class="row-table">
                            <tr>
                                <td width="50%">
                                    <span class="lbl">HTA:</span>
                                    @if($fr['hta']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> {{ $fr['hta']['tiempo'] ?? '' }} — {{ $fr['hta']['tratamiento'] ?? '' }}
                                    @else <span class="check-no">No</span> @endif
                                </td>
                                <td width="50%">
                                    <span class="lbl">DM:</span>
                                    @if($fr['dm']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> Tipo {{ $fr['dm']['tipo'] ?? '' }} — {{ $fr['dm']['tiempo'] ?? '' }}
                                    @else <span class="check-no">No</span> @endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="lbl">Dislipidemia:</span>
                                    @if($fr['dislipidemia']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> {{ $fr['dislipidemia']['detalle'] ?? '' }}
                                    @else <span class="check-no">No</span> @endif
                                </td>
                                <td>
                                    <span class="lbl">Tabaquismo:</span>
                                    @if($fr['tabaquismo']['tiene'] ?? false)
                                        <span class="check-yes">Sí</span> {{ $fr['tabaquismo']['estado'] ?? '' }} {{ $fr['tabaquismo']['cigarros_dia'] ?? '' }} cig/día
                                    @else <span class="check-no">No</span> @endif
                                </td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Obesidad:</span> @if($fr['obesidad'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                                <td><span class="lbl">Sedentarismo:</span> @if($fr['sedentarismo'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                            </tr>
                            <tr>
                                <td><span class="lbl">Estrés:</span> @if($fr['estres'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                                <td><span class="lbl">Apnea:</span> @if($fr['apnea'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif</td>
                            </tr>
                            @if($fr['otros'] ?? '')
                            <tr><td colspan="2"><span class="lbl">Otros:</span> {{ $fr['otros'] }}</td></tr>
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

    <!-- SÍNTOMAS -->
    @php $sint = $historia->sintomas ?? []; @endphp
    <div class="section">
        <div class="section-title">Síntomas</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="40%">
                        <span class="lbl">Dolor torácico:</span>
                        @if($sint['dolor_toracico']['tiene'] ?? false)
                            <span class="check-yes">Sí</span>
                            @if($sint['dolor_toracico']['tipo'] ?? '') — Tipo: {{ $sint['dolor_toracico']['tipo'] }} @endif
                            @if($sint['dolor_toracico']['localizacion'] ?? '') | Loc: {{ $sint['dolor_toracico']['localizacion'] }} @endif
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="30%">
                        <span class="lbl">Disnea (NYHA):</span>
                        @if($sint['disnea']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> {{ $sint['disnea']['clase_nyha'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="30%">
                        <span class="lbl">Palpitaciones:</span>
                        @if($sint['palpitaciones']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> {{ $sint['palpitaciones']['tipo'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                </tr>
                <tr>
                    <td><span class="lbl">Síncope:</span> @if($sint['sincope']['tiene'] ?? false)<span class="check-yes">Sí</span> — {{ $sint['sincope']['detalle'] ?? '' }}@else<span class="check-no">No</span>@endif</td>
                    <td><span class="lbl">Edema:</span> @if($sint['edema']['tiene'] ?? false)<span class="check-yes">Sí</span> {{ $sint['edema']['localizacion'] ?? '' }}@else<span class="check-no">No</span>@endif</td>
                    <td>
                        <span class="lbl">Ortopnea:</span> @if($sint['ortopnea'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif &nbsp;
                        <span class="lbl">DPN:</span> @if($sint['dpn'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif &nbsp;
                        <span class="lbl">Fatiga:</span> @if($sint['fatiga'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif
                    </td>
                </tr>
                @if($sint['otros'] ?? '')
                <tr><td colspan="3"><span class="lbl">Otros:</span> {{ $sint['otros'] }}</td></tr>
                @endif
            </table>
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

    <!-- EXPLORACIÓN CARDIOVASCULAR -->
    @php $exp = $historia->exploracion_cardiovascular ?? []; @endphp
    <div class="section">
        <div class="section-title">Exploración cardiovascular</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="33%"><span class="lbl">Estado general:</span> {{ $exp['estado_general'] ?? '—' }}</td>
                    <td width="33%"><span class="lbl">Cuello / IY (cm):</span> {{ $exp['cuello'] ?? '—' }} / {{ $exp['iy_cm'] ?? '—' }}</td>
                    <td width="33%"><span class="lbl">Tórax:</span> {{ $exp['torax'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td><span class="lbl">Ápex:</span> {{ $exp['apex'] ?? '—' }}</td>
                    <td><span class="lbl">Ritmo:</span> {{ $exp['ritmo'] ?? '—' }}</td>
                    <td>
                        <span class="lbl">R1:</span> {{ $exp['r1'] ?? '—' }} &nbsp;
                        <span class="lbl">R2:</span> {{ $exp['r2'] ?? '—' }} &nbsp;
                        <span class="lbl">R3:</span> @if($exp['r3'] ?? false)<span class="check-yes">+</span>@else —@endif &nbsp;
                        <span class="lbl">R4:</span> @if($exp['r4'] ?? false)<span class="check-yes">+</span>@else —@endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="lbl">Soplo:</span>
                        @if($exp['soplo']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — {{ $exp['soplo']['foco'] ?? '' }} Gr.{{ $exp['soplo']['grado'] ?? '' }} {{ $exp['soplo']['tipo'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td>
                        <span class="lbl">Frote pericárdico:</span>
                        @if($exp['frote_pericardico'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif
                    </td>
                    <td>
                        <span class="lbl">A. Pulmonar:</span> {{ $exp['auscultacion_pulmonar'] ?? '—' }} &nbsp;
                        <span class="lbl">Estertores:</span>
                        @if($exp['estertores']['tiene'] ?? false)<span class="check-yes">Sí</span> {{ $exp['estertores']['localizacion'] ?? '' }}@else<span class="check-no">No</span>@endif
                    </td>
                </tr>
                @if($exp['otros'] ?? '')
                <tr><td colspan="3"><span class="lbl">Otros:</span> {{ $exp['otros'] }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    <!-- PULSOS PERIFÉRICOS -->
    @php $pul = $historia->pulsos_perifericos ?? []; @endphp
    <div class="section">
        <div class="section-title">Pulsos periféricos</div>
        <div class="section-body">
            <table class="pulses-table">
                <tr>
                    <th>Zona</th>
                    <th>Derecho</th>
                    <th>Izquierdo</th>
                    <th>Zona</th>
                    <th>Derecho</th>
                    <th>Izquierdo</th>
                </tr>
                <tr>
                    <td>Carótida</td>
                    <td>{{ $pul['carotideo_der'] ?? '—' }}</td>
                    <td>{{ $pul['carotideo_izq'] ?? '—' }}</td>
                    <td>Radial</td>
                    <td>{{ $pul['radial_der'] ?? '—' }}</td>
                    <td>{{ $pul['radial_izq'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Femoral</td>
                    <td>{{ $pul['femoral_der'] ?? '—' }}</td>
                    <td>{{ $pul['femoral_izq'] ?? '—' }}</td>
                    <td>Poplíteo</td>
                    <td>{{ $pul['popliteo_der'] ?? '—' }}</td>
                    <td>{{ $pul['popliteo_izq'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Tibial</td>
                    <td>{{ $pul['tibial_der'] ?? '—' }}</td>
                    <td>{{ $pul['tibial_izq'] ?? '—' }}</td>
                    <td>Pedio</td>
                    <td>{{ $pul['pedio_der'] ?? '—' }}</td>
                    <td>{{ $pul['pedio_izq'] ?? '—' }}</td>
                </tr>
            </table>
            <table class="row-table mt-4">
                <tr>
                    <td width="50%">
                        <span class="lbl">Edema MMII:</span>
                        @if($pul['edema_mmii']['tiene'] ?? false)
                            <span class="check-yes">Sí</span> — Grado {{ $pul['edema_mmii']['grado'] ?? '' }}
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="50%">
                        <span class="lbl">Várices:</span>
                        @if($pul['varices'] ?? false)<span class="check-yes">Sí</span>@else<span class="check-no">No</span>@endif
                    </td>
                </tr>
            </table>
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
                            @foreach([
                                'glucosa' => 'Glucosa',
                                'hba1c' => 'HbA1c',
                                'creatinina' => 'Creatinina',
                                'tfg' => 'TFG',
                                'colesterol_total' => 'Colesterol Total',
                                'ldl' => 'LDL',
                                'hdl' => 'HDL',
                                'trigliceridos' => 'Triglicéridos',
                                'hemoglobina' => 'Hemoglobina',
                                'bnp' => 'BNP',
                                'troponinas' => 'Troponinas',
                                'dimero_d' => 'Dímero D',
                            ] as $key => $label)
                            @if($lab[$key] ?? '')
                            <tr>
                                <td width="50%"><span class="lbl">{{ $label }}:</span></td>
                                <td>{{ $lab[$key] }}</td>
                            </tr>
                            @endif
                            @endforeach
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
                        <div class="text-block">{{ $historia->pronostico ?? '—' }}</div>
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
                    <div style="font-size:10px;font-weight:700;color:#0A1628;">
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
