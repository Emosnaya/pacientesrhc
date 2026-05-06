<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historia Clínica Dental</title>
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
        .header-table td { vertical-align: middle; padding: 0; border: none; }
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
        .patient-table td { padding: 2px 6px; font-size: 10px; border: none; }
        .patient-name { font-size: 13px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; margin-bottom: 6px; }
        .patient-label { color: #64748b; font-size: 9px; }
        .patient-value { font-weight: 600; color: #334155; }

        /* === SECTIONS === */
        .section {
            margin-bottom: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .section-title {
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            color: white;
            font-size: 9px;
            font-weight: 700;
            padding: 4px 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            page-break-after: avoid;
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

        /* === FIELD ROWS === */
        .row-table { width: 100%; border-collapse: collapse; }
        .row-table td { padding: 2px 4px; vertical-align: top; font-size: 9.5px; border: none; }
        .lbl { color: #64748b; font-size: 9px; white-space: nowrap; }
        .val { font-weight: 600; }
        .check-yes { color: #16a34a; font-weight: 700; }
        .check-no { color: #94a3b8; }
        .text-block {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 4px; padding: 5px 8px;
            font-size: 9.5px; min-height: 20px;
        }

        /* === CHECKLIST GRID === */
        .check-grid { width: 100%; border-collapse: collapse; }
        .check-grid td { padding: 2px 6px; font-size: 9.5px; border: none; vertical-align: middle; }
        .check-box {
            display: inline-block;
            width: 9px; height: 9px;
            border: 1px solid #94a3b8;
            border-radius: 2px;
            margin-right: 4px;
            vertical-align: middle;
            background: white;
            text-align: center;
            line-height: 9px;
            font-size: 8px;
            color: #16a34a;
            font-weight: 700;
        }
        .check-box.checked { background: #dcfce7; border-color: #16a34a; color: #16a34a; }

        /* === VITALS TABLE === */
        .vitals-table { width: 100%; border-collapse: collapse; }
        .vitals-table th {
            background: #f1f5f9; border: 1px solid #e2e8f0;
            padding: 4px 6px; font-size: 8.5px; color: #64748b; font-weight: 700;
            text-align: center;
        }
        .vitals-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px; text-align: center; font-size: 9.5px;
        }

        /* === FIRMA === */
        .firma-section {
            margin-top: 12px; text-align: center;
            page-break-before: avoid; page-break-inside: avoid;
        }
        .firma-image { max-width: 150px; max-height: 50px; }

        /* === PAGE FOOTER === */
        .page-footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            padding: 6px 20px; background: white;
            border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!}; font-size: 9px;
        }
        .page-footer-table { width: 100%; }
        .page-footer-table td { border: none; padding: 0; }
        .clinic-name { font-weight: 700; color: #ef4444; }
        .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 40px; }

        /* Layout helpers */
        .two-col { width: 50%; vertical-align: top; padding: 0 4px; }
        .three-col { width: 33.33%; vertical-align: top; }
    </style>
</head>
<body>

<!-- PAGE FOOTER (fixed) -->
<div class="page-footer">
    <table class="page-footer-table">
        <tr>
            <td class="clinic-name">{{ $clinica->nombre ?? '' }}</td>
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
                        @if(!empty($clinicaLogo))
                            <img src="{{ $clinicaLogo }}" alt="Logo">
                        @endif
                    </div>
                </td>
                <td style="padding-left:10px;">
                    <div class="header-title">Historia Clínica Dental</div>
                    <div class="header-subtitle">Odontología{{ !empty($data->lugar) ? ' · ' . $data->lugar : '' }}</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Registro</div>
                        <div class="header-badge-value">#{{ $paciente->registro ?? 'N/A' }}</div>
                    </div>
                    <div class="header-date">{{ $data->fecha ? $data->fecha->format('d/m/Y') : date('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- PATIENT CARD -->
    <div class="patient-card">
        <div class="patient-name">{{ $paciente->apellidoPat ?? '' }} {{ $paciente->apellidoMat ?? '' }} {{ $paciente->nombre }}</div>
        <table class="patient-table">
            <tr>
                <td>
                    <span class="patient-label">Edad:</span>
                    <span class="patient-value">{{ $paciente->fechaNacimiento ? \Carbon\Carbon::parse($paciente->fechaNacimiento)->age : ($paciente->edad ?? '—') }} años</span>
                </td>
                <td>
                    <span class="patient-label">Género:</span>
                    <span class="patient-value">{{ ($paciente->genero ?? '') == 1 || ($paciente->genero ?? '') === 'masculino' ? 'Masculino' : 'Femenino' }}</span>
                </td>
                <td>
                    <span class="patient-label">Teléfono:</span>
                    <span class="patient-value">{{ $paciente->telefono ?? '—' }}</span>
                </td>
                <td>
                    <span class="patient-label">F. Nacimiento:</span>
                    <span class="patient-value">{{ $paciente->fechaNacimiento ? \Carbon\Carbon::parse($paciente->fechaNacimiento)->format('d/m/Y') : '—' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <span class="patient-label">Domicilio:</span>
                    <span class="patient-value">{{ $paciente->domicilio ?? $paciente->direccion ?? '—' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- HISTORIA Y MOTIVO -->
    <div class="section">
        <div class="section-title">Historia y motivo de consulta</div>
        <div class="section-body">
            <table style="width:100%"><tr>
                <td class="two-col">
                    <div class="lbl" style="margin-bottom:3px;">Motivo de consulta</div>
                    <div class="text-block">{{ $data->motivo_consulta ?? '—' }}</div>
                </td>
                <td class="two-col">
                    <div class="lbl" style="margin-bottom:3px;">Historia de la enfermedad actual</div>
                    <div class="text-block">{{ $data->historia_enfermedad ?? '—' }}</div>
                </td>
            </tr></table>
        </div>
    </div>

    <!-- INFORMACIÓN MÉDICA GENERAL -->
    <div class="section">
        <div class="section-title">Información médica general</div>
        <div class="section-body">
            @if($data->medicamentos_actuales && count($data->medicamentos_actuales) > 0)
            <div class="section-subtitle">Medicamentos actuales</div>
            <table class="row-table" style="margin-top:4px;">
                @foreach($data->medicamentos_actuales as $med)
                <tr>
                    <td width="50%"><span class="lbl">Medicamento:</span> <span class="val">{{ $med['medicamento'] ?? '—' }}</span></td>
                    <td width="50%"><span class="lbl">Dosis:</span> <span class="val">{{ $med['dosis'] ?? '—' }}</span></td>
                </tr>
                @endforeach
            </table>
            <div style="margin-top:6px;"></div>
            @endif

            <table class="row-table">
                <tr>
                    <td width="25%">
                        <span class="lbl">¿Alérgico a anestésicos?</span><br>
                        @if($data->alergico_anestesicos)
                            <span class="check-yes">Sí</span>
                            @if($data->anestesicos_detalle) — {{ $data->anestesicos_detalle }} @endif
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="25%">
                        <span class="lbl">¿Alérgico a medicamentos?</span><br>
                        @if($data->alergico_medicamentos)
                            <span class="check-yes">Sí</span>
                            @if($data->medicamentos_alergicos_detalle) — {{ $data->medicamentos_alergicos_detalle }} @endif
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="25%">
                        <span class="lbl">¿Embarazada?</span><br>
                        @if($data->embarazada) <span class="check-yes">Sí</span> @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="25%">
                        <span class="lbl">¿Toma anticonceptivos?</span><br>
                        @if($data->toma_anticonceptivos) <span class="check-yes">Sí</span> @else <span class="check-no">No</span> @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- INFORMACIÓN DENTAL -->
    <div class="section">
        <div class="section-title">Información dental</div>
        <div class="section-body">
            <table class="check-grid">
                <tr>
                    <td width="25%"><span class="check-box {{ $data->mal_aliento ? 'checked' : '' }}">{{ $data->mal_aliento ? '✓' : '' }}</span> Mal aliento</td>
                    <td width="25%"><span class="check-box {{ $data->hipersensibilidad_dental ? 'checked' : '' }}">{{ $data->hipersensibilidad_dental ? '✓' : '' }}</span> Hipersensibilidad dental</td>
                    <td width="25%"><span class="check-box {{ $data->respira_boca ? 'checked' : '' }}">{{ $data->respira_boca ? '✓' : '' }}</span> Respira por la boca</td>
                    <td width="25%"><span class="check-box {{ $data->muerde_unas ? 'checked' : '' }}">{{ $data->muerde_unas ? '✓' : '' }}</span> Muerde uñas</td>
                </tr>
                <tr>
                    <td><span class="check-box {{ $data->muerde_labios ? 'checked' : '' }}">{{ $data->muerde_labios ? '✓' : '' }}</span> Muerde labios</td>
                    <td><span class="check-box {{ $data->aprieta_dientes ? 'checked' : '' }}">{{ $data->aprieta_dientes ? '✓' : '' }}</span> Aprieta dientes</td>
                    <td></td><td></td>
                </tr>
            </table>
            <div style="margin-top:6px;"></div>
            <table class="row-table">
                <tr>
                    <td width="33%"><span class="lbl">Veces que cepilla al día:</span> <span class="val">{{ $data->veces_cepilla_dia ?? '—' }}</span></td>
                    <td width="33%"><span class="lbl">Método de higienización:</span> <span class="val">{{ $data->higienizacion_metodo ?? '—' }}</span></td>
                    <td width="34%"><span class="lbl">Última visita al odontólogo:</span> <span class="val">{{ $data->ultima_visita_odontologo ? \Carbon\Carbon::parse($data->ultima_visita_odontologo)->format('d/m/Y') : '—' }}</span></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ANTECEDENTES FAMILIARES + PATOLÓGICOS (2 cols) -->
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:50%;vertical-align:top;padding-right:4px;">
                <div class="section">
                    <div class="section-title">Antecedentes familiares</div>
                    <div class="section-body">
                        <table class="check-grid">
                            <tr>
                                <td width="50%"><span class="check-box {{ $data->af_diabetes ? 'checked' : '' }}">{{ $data->af_diabetes ? '✓' : '' }}</span> Diabetes</td>
                                <td width="50%"><span class="check-box {{ $data->af_hipertension ? 'checked' : '' }}">{{ $data->af_hipertension ? '✓' : '' }}</span> Hipertensión</td>
                            </tr>
                            <tr>
                                <td><span class="check-box {{ $data->af_cancer ? 'checked' : '' }}">{{ $data->af_cancer ? '✓' : '' }}</span> Cáncer</td>
                                <td><span class="check-box {{ $data->af_cardiacas ? 'checked' : '' }}">{{ $data->af_cardiacas ? '✓' : '' }}</span> Enf. Cardiacas</td>
                            </tr>
                            <tr>
                                <td><span class="check-box {{ $data->af_vih ? 'checked' : '' }}">{{ $data->af_vih ? '✓' : '' }}</span> VIH</td>
                                <td><span class="check-box {{ $data->af_epilepsia ? 'checked' : '' }}">{{ $data->af_epilepsia ? '✓' : '' }}</span> Epilepsia</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:4px;">
                <div class="section">
                    <div class="section-title">Información patológica</div>
                    <div class="section-body">
                        <table class="check-grid">
                            <tr>
                                <td width="50%"><span class="check-box {{ $data->ip_diabetes ? 'checked' : '' }}">{{ $data->ip_diabetes ? '✓' : '' }}</span> Diabetes</td>
                                <td width="50%"><span class="check-box {{ $data->ip_hipertension ? 'checked' : '' }}">{{ $data->ip_hipertension ? '✓' : '' }}</span> Hipertensión</td>
                            </tr>
                            <tr>
                                <td><span class="check-box {{ $data->ip_veneras ? 'checked' : '' }}">{{ $data->ip_veneras ? '✓' : '' }}</span> VIH/Enf. Venéreas</td>
                                <td><span class="check-box {{ $data->ip_cancer ? 'checked' : '' }}">{{ $data->ip_cancer ? '✓' : '' }}</span> Cáncer</td>
                            </tr>
                            <tr>
                                <td><span class="check-box {{ $data->ip_asma ? 'checked' : '' }}">{{ $data->ip_asma ? '✓' : '' }}</span> Asma</td>
                                <td><span class="check-box {{ $data->ip_epilepsia ? 'checked' : '' }}">{{ $data->ip_epilepsia ? '✓' : '' }}</span> Epilepsia</td>
                            </tr>
                            <tr>
                                <td><span class="check-box {{ $data->ip_cardiacas ? 'checked' : '' }}">{{ $data->ip_cardiacas ? '✓' : '' }}</span> Enf. Cardiacas</td>
                                <td><span class="check-box {{ $data->ip_gastricas ? 'checked' : '' }}">{{ $data->ip_gastricas ? '✓' : '' }}</span> Enf. Gástricas</td>
                            </tr>
                            <tr>
                                <td><span class="check-box {{ $data->ip_cicatriz ? 'checked' : '' }}</span>{{ $data->ip_cicatriz ? '✓' : '' }}</span> Cicatriz</td>
                                <td><span class="check-box {{ $data->ip_presion_alta_baja ? 'checked' : '' }}">{{ $data->ip_presion_alta_baja ? '✓' : '' }}</span> Presión alta/baja</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- ANTECEDENTES TOXICOLÓGICOS -->
    <div class="section">
        <div class="section-title">Antecedentes toxicológicos</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    <td width="33%">
                        <span class="lbl">¿Fuma?</span><br>
                        @if($data->at_fuma) <span class="check-yes">Sí</span> @if($data->at_fuma_detalle) — {{ $data->at_fuma_detalle }} @endif
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="33%">
                        <span class="lbl">¿Consume drogas?</span><br>
                        @if($data->at_drogas) <span class="check-yes">Sí</span> @if($data->at_drogas_detalle) — {{ $data->at_drogas_detalle }} @endif
                        @else <span class="check-no">No</span> @endif
                    </td>
                    <td width="34%">
                        <span class="lbl">¿Consume alcohol?</span><br>
                        @if($data->at_toma) <span class="check-yes">Sí</span> @if($data->at_toma_detalle) — {{ $data->at_toma_detalle }} @endif
                        @else <span class="check-no">No</span> @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ANTECEDENTES GINECOOBSTÉTRICOS (solo femenino) -->
    @php $generoFemenino = ($paciente->genero ?? '') == 0 || ($paciente->genero ?? '') === 'femenino'; @endphp
    @if($generoFemenino && ($data->ag_menarca || $data->ag_menopausia || $data->ag_embarazo || $data->ag_menarca_edad || $data->ag_menopausia_edad))
    <div class="section">
        <div class="section-title">Antecedentes ginecoobstétricos</div>
        <div class="section-body">
            <table class="row-table">
                <tr>
                    @if($data->ag_menarca && $data->ag_menarca_edad)
                    <td width="33%"><span class="lbl">Edad de menarca:</span> <span class="val">{{ $data->ag_menarca_edad }} años</span></td>
                    @endif
                    @if($data->ag_menopausia && $data->ag_menopausia_edad)
                    <td width="33%"><span class="lbl">Edad de menopausia:</span> <span class="val">{{ $data->ag_menopausia_edad }} años</span></td>
                    @endif
                    @if($data->ag_embarazo)
                    <td width="34%"><span class="lbl">Embarazo:</span> <span class="check-yes">Sí</span></td>
                    @endif
                </tr>
            </table>
        </div>
    </div>
    @endif

    <!-- ANTECEDENTES ODONTOLÓGICOS -->
    <div class="section">
        <div class="section-title">Antecedentes odontológicos</div>
        <div class="section-body">
            <table class="check-grid">
                <tr>
                    <td width="25%"><span class="check-box {{ $data->ao_limpieza_6meses ? 'checked' : '' }}">{{ $data->ao_limpieza_6meses ? '✓' : '' }}</span> Limpieza últ. 6 meses</td>
                    <td width="25%"><span class="check-box {{ $data->ao_sangrado ? 'checked' : '' }}">{{ $data->ao_sangrado ? '✓' : '' }}</span> Sangrado de encías</td>
                    <td width="25%"><span class="check-box {{ $data->ao_dolor_masticar ? 'checked' : '' }}">{{ $data->ao_dolor_masticar ? '✓' : '' }}</span> Dolor al masticar</td>
                    <td width="25%"><span class="check-box {{ $data->ao_tratamiento_ortodoncia ? 'checked' : '' }}">{{ $data->ao_tratamiento_ortodoncia ? '✓' : '' }}</span> Ortodoncia previa</td>
                </tr>
                <tr>
                    <td><span class="check-box {{ $data->ao_morder_labios ? 'checked' : '' }}">{{ $data->ao_morder_labios ? '✓' : '' }}</span> Morder labios</td>
                    <td><span class="check-box {{ $data->ao_dieta_dulces ? 'checked' : '' }}">{{ $data->ao_dieta_dulces ? '✓' : '' }}</span> Dieta rica en dulces</td>
                    <td><span class="check-box {{ $data->ao_cepilla_dientes ? 'checked' : '' }}">{{ $data->ao_cepilla_dientes ? '✓' : '' }}</span> Cepilla dientes</td>
                    <td><span class="check-box {{ $data->ao_trauma_cara ? 'checked' : '' }}">{{ $data->ao_trauma_cara ? '✓' : '' }}</span> Trauma en cara</td>
                </tr>
                <tr>
                    <td colspan="4"><span class="check-box {{ $data->ao_dolor_abrir ? 'checked' : '' }}">{{ $data->ao_dolor_abrir ? '✓' : '' }}</span> Dolor al abrir boca</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- EXAMEN DE TEJIDOS BLANDOS -->
    <div class="section">
        <div class="section-title">Examen de tejidos blandos</div>
        <div class="section-body">
            <table class="vitals-table">
                <tr>
                    <th>Carrillos</th>
                    <th>Encías</th>
                    <th>Lengua</th>
                    <th>Paladar</th>
                    <th>ATM</th>
                    <th>Labios</th>
                </tr>
                <tr>
                    <td>{{ $data->etb_carrillos ?? '—' }}</td>
                    <td>{{ $data->etb_encias ?? '—' }}</td>
                    <td>{{ $data->etb_lengua ?? '—' }}</td>
                    <td>{{ $data->etb_paladar ?? '—' }}</td>
                    <td>{{ $data->etb_atm ?? '—' }}</td>
                    <td>{{ $data->etb_labios ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- SIGNOS VITALES -->
    <div class="section">
        <div class="section-title">Signos vitales</div>
        <div class="section-body">
            <table class="vitals-table">
                <tr>
                    <th>TA</th>
                    <th>Pulso</th>
                    <th>FC</th>
                    <th>Peso</th>
                    <th>Altura</th>
                </tr>
                <tr>
                    <td>{{ $data->sv_ta ?? '—' }}</td>
                    <td>{{ $data->sv_pulso ?? '—' }}</td>
                    <td>{{ $data->sv_fc ?? '—' }}</td>
                    <td>{{ $data->sv_peso ? $data->sv_peso . ' kg' : '—' }}</td>
                    <td>{{ $data->sv_altura ? $data->sv_altura . ' cm' : '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ELABORÓ / FIRMA -->
    <div class="firma-section">
        <p style="font-weight:700;font-size:10px;margin-bottom:4px;">
            Elaboró:
            @if(isset($autor) && $autor)
                {{ $autor->nombre_completo }}
                @if($autor->cedula) | Cédula: {{ $autor->cedula }} @endif
            @else
                {{ $user->nombre_con_titulo }}
            @endif
        </p>
        @if(isset($esAutor) && $esAutor && isset($firmaBase64) && $firmaBase64)
            <img src="{{ $firmaBase64 }}" alt="Firma Digital" class="firma-image">
            <p style="font-weight:700;margin-top:4px;">{{ $user->nombre_con_titulo }}</p>
            @if($user->cedula)
            <p style="font-size:8px;color:#64748b;">Cédula Profesional: {{ $user->cedula }}</p>
            @endif
            <div style="width:200px;border-top:1px solid #333;margin:5px auto;"></div>
            <p style="font-size:9px;">Firma del Doctor</p>
        @endif
    </div>

</div><!-- end content-wrapper -->
</body>
</html>
