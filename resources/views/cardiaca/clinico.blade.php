<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            line-height: 1.3;
            color: #1e293b;
            background: #ffffff;
            padding: 6px 12px;
        }
        .page-footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            padding: 4px 14px; background: white;
            border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
            font-size: 7.5px;
        }
        .pf-table { width: 100%; border-collapse: collapse; }
        .pf-clinic { font-weight: 700; color: #ef4444; }
        .pf-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 26px; }
        .header {
            width: 100%;
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            border-radius: 6px; margin-bottom: 5px; padding: 5px 10px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-cell { width: 44px; padding-right: 8px !important; }
        .header-logo { width: 36px; height: 36px; background: white; border-radius: 5px; padding: 3px; text-align: center; }
        .header-logo img { max-height: 30px; max-width: 30px; }
        .header-title { font-size: 13px; font-weight: 700; color: white; letter-spacing: -0.3px; }
        .header-subtitle { font-size: 7.5px; color: #94a3b8; }
        .header-meta-cell { text-align: right; width: 100px; }
        .header-badge { background: rgba(255,255,255,0.15); padding: 3px 8px; border-radius: 4px; display: inline-block; margin-bottom: 3px; }
        .header-badge-label { font-size: 7px; text-transform: uppercase; color: #94a3b8; }
        .header-badge-value { font-size: 10px; font-weight: 700; color: white; }
        .header-date { font-size: 7.5px; color: #94a3b8; }
        .patient-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px 9px; margin-bottom: 5px; }
        .patient-name { font-size: 11px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; margin-bottom: 4px; }
        .pt { width: 100%; border-collapse: collapse; }
        .pt td { padding: 1px 4px; font-size: 7.5px; }
        .plabel { color: #64748b; }
        .pvalue { font-weight: 600; color: #334155; }
        .pdx { margin-top: 4px; padding-top: 4px; border-top: 1px solid #e2e8f0; font-size: 7.5px; }
        .pdx-label { color: #64748b; font-weight: 600; }
        .section-title {
            font-size: 8px; font-weight: 700;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            border-bottom: 1.5px solid {!! $clinica->color_principal ?? '#0A1628' !!};
            padding-bottom: 2px; margin-bottom: 3px; margin-top: 4px;
            text-transform: uppercase; letter-spacing: 0.3px;
        }
        .section-content { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px 7px; font-size: 7.5px; }
        .section-accent { border-left: 3px solid {!! $clinica->color_principal ?? '#0A1628' !!}; padding: 3px 7px; font-size: 7.5px; background: #f8fafc; }
        .dt { width: 100%; border-collapse: collapse; }
        .dt td { border: 1px solid #cbd5e1; padding: 2px 5px; font-size: 7.5px; background: #f8fafc; }
        .dt tr:nth-child(even) td { background: #ffffff; }
        .dt .lbl { color: #64748b; }
        .dt .val { font-weight: 700; color: #0f172a; }
        .dt thead th { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: white; padding: 2px 5px; font-size: 7px; border: 1px solid #cbd5e1; }
        .cols { width: 100%; border-collapse: collapse; }
        .cols > tbody > tr > td { vertical-align: top; padding: 0 3px; }
        .cols > tbody > tr > td:first-child { padding-left: 0; }
        .cols > tbody > tr > td:last-child { padding-right: 0; }
        .firma-wrap { text-align: center; margin-top: 5px; }
        .firma-wrap img { height: 40px; width: auto; }
        .firma-line { border-top: 1px solid #334155; width: 130px; margin: 2px auto 0 auto; }
        .firma-name { font-size: 7.5px; color: #334155; }
        .f-bold { font-weight: 700; }
        .f-normal { font-weight: normal; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

<div class="page-footer">
    <table class="pf-table">
        <tr>
            <td class="pf-clinic">{{ $clinica->nombre ?? 'Clínica' }}</td>
            <td class="pf-contact">{{ $clinica->telefono ?? '' }} @if($clinica->email ?? null) | {{ $clinica->email }} @endif</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;padding-top:2px;font-size:6.5px;color:#94a3b8;">
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
            <td style="padding-left:8px;">
                <div class="header-title">Historia Clínica de Rehabilitación Cardiaca</div>
                <div class="header-subtitle">Historia clínica del paciente</div>
            </td>
            <td class="header-meta-cell">
                <div class="header-badge">
                    <div class="header-badge-label">Registro</div>
                    <div class="header-badge-value">#{{ $paciente->registro }}</div>
                </div>
                <div class="header-date">{{ date('d/m/Y', strtotime($data->fecha)) }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="patient-card">
    <div class="patient-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
    <table class="pt">
        <tr>
        <td><span class="plabel">Edad:</span> <span class="pvalue">{{ $paciente->edad }} años</span></td>
        <td><span class="plabel">Género:</span> <span class="pvalue">{{ $paciente->genero == 1 ? 'Hombre' : 'Mujer' }}</span></td>
            <td><span class="plabel">Peso:</span> <span class="pvalue">{{ $paciente->peso }} kg</span></td>
            <td><span class="plabel">Talla:</span> <span class="pvalue">{{ $paciente->talla }} m</span></td>
            <td><span class="plabel">IMC:</span> <span class="pvalue">{{ round($paciente->imc, 2) }}</span></td>
            <td><span class="plabel">E. Civil:</span> <span class="pvalue">{{ $paciente->estadoCivil }}</span></td>
        </tr>
        <tr>
            <td><span class="plabel">F. Nac.:</span> <span class="pvalue">{{ $paciente->fechaNacimiento ? date('d/m/Y', strtotime($paciente->fechaNacimiento)) : '—' }}</span></td>
            <td><span class="plabel">Profesión:</span> <span class="pvalue">{{ $paciente->profesion }}</span></td>
            <td><span class="plabel">Ingreso (1ª):</span> <span class="pvalue">{{ date('d/m/Y', strtotime($data->fecha_1vez)) }}</span></td>
            <td><span class="plabel">Estratif.:</span> <span class="pvalue">{{ date('d/m/Y', strtotime($data->estratificacion)) }}</span></td>
            <td colspan="2"><span class="plabel">Dom.:</span> <span class="pvalue">{{ $paciente->domicilio }}</span> &nbsp; <span class="plabel">Tel:</span> <span class="pvalue">{{ $paciente->telefono }}</span></td>
        </tr>
    </table>
    @if($paciente->medicamentos)
    <div class="pdx"><span class="pdx-label">Medicamentos:</span> {{ $paciente->medicamentos }}</div>
    @endif
    @if($paciente->diagnostico)
    <div class="pdx"><span class="pdx-label">Diagnóstico:</span> {{ $paciente->diagnostico }}</div>
    @endif
</div>

<table class="cols">
    <tr>
        <td style="width:50%;">
            <div class="section-title">Antecedentes Cardiovasculares</div>
            <table class="dt">
                <tr>
                    <td><span class="lbl">IM Anterior:</span> <span class="val">{{$data->imAnterior===null?"n":date('d/m/Y',strtotime($data->imAnterior))}}</span></td>
                    <td><span class="lbl">IM Septal:</span> <span class="val">{{$data->imSeptal===null?"n":date('d/m/Y',strtotime($data->imSeptal))}}</span></td>
                    <td><span class="lbl">IM Apical:</span> <span class="val">{{$data->imApical===null?"n":date('d/m/Y',strtotime($data->imApical))}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">IM Lateral:</span> <span class="val">{{$data->imLateral===null?"n":date('d/m/Y',strtotime($data->imLateral))}}</span></td>
                    <td><span class="lbl">IM Inferior:</span> <span class="val">{{$data->imInferior===null?"n":date('d/m/Y',strtotime($data->imInferior))}}</span></td>
                    <td><span class="lbl">IM del VD:</span> <span class="val">{{$data->imdelVD===null?"n":date('d/m/Y',strtotime($data->imdelVD))}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">A. Inestable:</span> <span class="val">{{$data->anginaInestabale===null?"n":$data->anginaInestabale}}</span></td>
                    <td><span class="lbl">A. Estable:</span> <span class="val">{{$data->anginaEstabale===null?"n":$data->anginaEstabale}}</span></td>
                    <td><span class="lbl">Ch. Cardiogénico:</span> <span class="val">{{$data->choque_card===null?"n":date('d/m/Y',strtotime($data->choque_card))}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">Muerte Súbita:</span> <span class="val">{{$data->m_subita===null?"n":date('d/m/Y',strtotime($data->m_subita))}}</span></td>
                    <td><span class="lbl">Falla Cardiaca:</span> <span class="val">{{$data->falla_cardiaca===null||$data->falla_cardiaca===0?"n":"s"}}</span></td>
                    <td><span class="lbl">CRVC:</span> <span class="val">{{$data->crvc===null?"n":date('d/m/Y',strtotime($data->crvc))}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">CRVC (HV):</span> <span class="val">{{$data->crvc_hemoductos===null?"n":$data->crvc_hemoductos}}</span></td>
                    <td><span class="lbl">I.A. Periférica:</span> <span class="val">{{$data->insuficiencia_art_per===null||$data->insuficiencia_art_per===0?"n":"s"}}</span></td>
                    <td><span class="lbl">V. Mitral:</span> <span class="val">{{$data->v_mitral===null||$data->v_mitral===0?"n":"s"}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">V. Aórtica:</span> <span class="val">{{$data->v_aortica===null||$data->v_aortica===0?"n":"s"}}</span></td>
                    <td><span class="lbl">V. Tricúspide:</span> <span class="val">{{$data->v_tricuspide===null||$data->v_tricuspide===0?"n":"s"}}</span></td>
                    <td><span class="lbl">V. Pulmonar:</span> <span class="val">{{$data->v_pulmonar===null||$data->v_pulmonar===0?"n":"s"}}</span></td>
                </tr>
                <tr>
                    <td colspan="3"><span class="lbl">Congénitos:</span> <span class="val">{{$data->congenitos===null||$data->congenitos===0?"n":"s"}}</span></td>
                </tr>
            </table>
        </td>
        <td style="width:50%;padding-left:5px !important;">
            <div class="section-title">Factores de Riesgo Cardiovascular</div>
            <table class="dt">
                <tr>
                    <td><span class="lbl">Hipercolest.:</span> <span class="val">{{$data->hipercolesterolemia_y===null||$data->hipercolesterolemia_y==0?"n":$data->hipercolesterolemia_y}}</span></td>
                    <td><span class="lbl">Hipertensión:</span> <span class="val">{{$data->hipertension_años===null||$data->hipertension_años==0?"n":$data->hipertension_años}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">Estrés:</span> <span class="val">{{$data->estres_years===null||$data->estres_years==0?"n":$data->estres_years}}</span></td>
                    <td><span class="lbl">Diabetes M.:</span> <span class="val">{{$data->diabetes_y===null||$data->diabetes_y==0?"n":$data->diabetes_y}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">Hipertriglicerid.:</span> <span class="val">{{$data->hipertrigliceridemia_y===null||$data->hipertrigliceridemia_y==0?"n":$data->hipertrigliceridemia_y}}</span></td>
                    <td><span class="lbl">Depresión:</span> <span class="val">{{$data->depresion_years===null||$data->depresion_years==0?"n":$data->depresion_years}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">Ansiedad:</span> <span class="val">{{$data->ansiedad_years===null||$data->ansiedad_years==0?"n":$data->ansiedad_years}}</span></td>
                    <td><span class="lbl">Tabaquismo:</span> <span class="val">{{$data->tabaquismo===null||$data->tabaquismo===0?"n":"s"}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">Cig./día:</span> <span class="val">{{$data->cig_dia===null||$data->cig_dia===0?"n":$data->cig_dia}}</span></td>
                    <td><span class="lbl">Años fumando:</span> <span class="val">{{$data->cig_years===null||$data->cig_years===0?"n":$data->cig_years}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">Abandonó Cig.:</span> <span class="val">{{$data->cig_abandono===0||$data->cig_abandono===null?"n":"s"}}</span></td>
                    <td><span class="lbl">Años abandono:</span> <span class="val">{{$data->cig_años_abandono===0||$data->cig_años_abandono===null?"n":$data->cig_años_abandono}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">Act. Física:</span> <span class="val">{{$data->actividad_fis===0||$data->actividad_fis===null?"n":"s"}}</span></td>
                    <td><span class="lbl">Tipo A.F.:</span> <span class="val">{{$data->tipo_actividad===null||$data->tipo_actividad==0?"n":$data->tipo_actividad}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">Hrs/sem.:</span> <span class="val">{{$data->actividad_hrs_smn===null||$data->actividad_hrs_smn==0?"n":$data->actividad_hrs_smn}}</span></td>
                    <td><span class="lbl">Años practicando:</span> <span class="val">{{$data->actividad_years===null||$data->actividad_years==0?"n":$data->actividad_years}}</span></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- TRATAMIENTO + CLASE FUNCIONAL -->
<table class="cols" style="margin-top:4px;">
    <tr>
        <td style="width:60%;">
            <div class="section-title">Tratamiento Médico</div>
            <div class="section-accent">{{ $paciente->medicamentos }}</div>
        </td>
        <td style="width:40%;padding-left:5px !important;">
            <div class="section-title">Clase Funcional</div>
            <div class="section-content">
                <span class="f-bold">NYHA:</span> {{ $data->cf_nyha }} &nbsp;&nbsp;
                <span class="f-bold">CCS:</span> {{ $data->clase_f_ccs }} &nbsp;&nbsp;
                <span class="f-bold">DASI (METs):</span> {{ round($data->dasi, 2) }}
            </div>
        </td>
    </tr>
</table>

<div class="section-title">Laboratorios</div>
<table class="cols">
    <tr>
        <td style="width:50%;">
            <table class="dt">
                <thead><tr><th colspan="4" style="text-align:left;">Biometría Hemática / Química Sanguínea</th></tr></thead>
                <tr>
                    <td><span class="lbl">F. BH:</span> <span class="val">{{$data->bh_fecha===null?"—":date('d/m/Y',strtotime($data->bh_fecha))}}</span></td>
                    <td><span class="lbl">Hb:</span> <span class="val">{{$data->hb===null||$data->hb==0?"n":$data->hb}}</span></td>
                    <td><span class="lbl">Leuc.:</span> <span class="val">{{$data->leucos===null||$data->leucos==0?"n":$data->leucos}}</span></td>
                    <td><span class="lbl">Plaq.:</span> <span class="val">{{$data->plaquetas===null||$data->plaquetas==0?"n":$data->plaquetas}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">F. QS:</span> <span class="val">{{$data->qs===null?"n":date('d/m/Y',strtotime($data->qs))}}</span></td>
                    <td><span class="lbl">Gluc.:</span> <span class="val">{{$data->glucosa===null||$data->glucosa==0?"n":$data->glucosa}}</span></td>
                    <td><span class="lbl">Creat.:</span> <span class="val">{{$data->creatinina===null||$data->creatinina==0?"n":$data->creatinina}}</span></td>
                    <td><span class="lbl">A. Úr.:</span> <span class="val">{{$data->ac_unico===null||$data->ac_unico===0?"n":$data->ac_unico}}</span></td>
                </tr>
            </table>
        </td>
        <td style="width:50%;padding-left:5px !important;">
            <table class="dt">
                <thead><tr><th colspan="4" style="text-align:left;">Lípidos / Coagulación / Biomarcadores</th></tr></thead>
                <tr>
                    <td><span class="lbl">Col.:</span> <span class="val">{{$data->colesterol===null||$data->colesterol===0?"n":$data->colesterol}}</span></td>
                    <td><span class="lbl">LDL:</span> <span class="val">{{$data->ldl===null||$data->ldl===0?"n":$data->ldl}}</span></td>
                    <td><span class="lbl">HDL:</span> <span class="val">{{$data->hdl===0||$data->hdl===null?"n":$data->hdl}}</span></td>
                    <td><span class="lbl">Tg.:</span> <span class="val">{{$data->trigliceridos===null||$data->trigliceridos==0?"n":$data->trigliceridos}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">TP:</span> <span class="val">{{$data->tp===0||$data->tp===null?"n":$data->tp}}</span></td>
                    <td><span class="lbl">INR:</span> <span class="val">{{$data->inr===0||$data->inr===null?"n":$data->inr}}</span></td>
                    <td><span class="lbl">TPT:</span> <span class="val">{{$data->tpt===null||$data->tpt==0?"n":$data->tpt}}</span></td>
                    <td><span class="lbl">PCRas:</span> <span class="val">{{$data->pcras===null||$data->pcras==0?"n":$data->pcras}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">PRO-BNP:</span> <span class="val">{{$data->pro_bnp===null||$data->pro_bnp===''?"n":$data->pro_bnp}}</span></td>
                    <td colspan="3"></td>
                </tr>
                @if($data->otros_lab)
                <tr><td colspan="4"><span class="lbl">Otros:</span> <span class="val">{{$data->otros_lab}}</span></td></tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<table class="cols">
    <tr>
        <td style="width:50%;">
            <div class="section-title">Electrocardiograma</div>
            <table class="dt">
                <tr>
                    <td><span class="lbl">Fecha:</span> <span class="val">{{$data->ecg_fecha===null?"—":date('d/m/Y',strtotime($data->ecg_fecha))}}</span></td>
                    <td><span class="lbl">Ritmo:</span> <span class="val">{{$data->ritmo===null||$data->ritmo===''?"n":$data->ritmo}}</span></td>
                    <td><span class="lbl">FC:</span> <span class="val">{{$data->fc_ecog===null||$data->fc_ecog==0?"n":round($data->fc_ecog)}} lpm</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">aP:</span> <span class="val">{{$data->aP===null||$data->aP==0?"n":$data->aP."°"}}</span></td>
                    <td><span class="lbl">aQRS:</span> <span class="val">{{$data->aQRS===null||$data->aQRS==0?"n":$data->aQRS."°"}}</span></td>
                    <td><span class="lbl">aT:</span> <span class="val">{{$data->aT===0||$data->aT===null?"n":$data->aT."°"}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">Dur. QRS:</span> <span class="val">{{$data->duracion_qrs===null||$data->duracion_qrs==0?"n":$data->duracion_qrs}} ms</span></td>
                    <td><span class="lbl">Dur. P:</span> <span class="val">{{$data->duracion_p===null||$data->duracion_p==0?"n":$data->duracion_p}} ms</span></td>
                    <td><span class="lbl">PR:</span> <span class="val">{{$data->pr===null||$data->pr===0?"n":$data->pr}} ms</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">QTm:</span> <span class="val">{{$data->qtm===null||$data->qtm===0?"n":sprintf("%.2f",floor($data->qtm*100)/100)}} ms</span></td>
                    <td><span class="lbl">QTc:</span> <span class="val">{{$data->qtc===0||$data->qtc===null?"n":sprintf("%.2f",floor($data->qtc*100)/100)}} ms</span></td>
                    <td><span class="lbl">BAV:</span> <span class="val">{{$data->bav===null||$data->bav===''||$data->bav==0?"n":$data->bav}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">BRIHH:</span> <span class="val">{{$data->brihh===null||$data->brihh===0?"n":"s"}}</span></td>
                    <td><span class="lbl">BRDHH:</span> <span class="val">{{$data->brdhh===null||$data->brdhh===0?"n":"s"}}</span></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span class="lbl">Q AS:</span> <span class="val">{{$data->q_as===null||$data->q_as===0?"n":"s"}}</span></td>
                    <td><span class="lbl">Q Inf.:</span> <span class="val">{{$data->q_inf===null||$data->q_inf===0?"n":"s"}}</span></td>
                    <td><span class="lbl">Q Lat.:</span> <span class="val">{{$data->q_lat===0||$data->q_lat===null?"n":"s"}}</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">Q Ant.:</span> <span class="val">{{$data->q_ant===null||$data->q_ant===0?"n":"s"}}</span></td>
                    <td><span class="lbl">Q Post. Inf.:</span> <span class="val">{{$data->q_poster_inferior===null||$data->q_poster_inferior===0?"n":"s"}}</span></td>
                    <td></td>
                </tr>
                @if($data->otros_ecg)
                <tr>
                    <td colspan="3"><span class="lbl">Otros hallazgos:</span> <span class="val">{{$data->otros_ecg}}</span></td>
                </tr>
                @endif
            </table>
        </td>
        <td style="width:50%;padding-left:5px !important;">
            <div class="section-title">Ecocardiografía</div>
            <table class="dt">
                <tr>
                    <td><span class="lbl">Fecha:</span> <span class="val">{{$data->eco_fecha===null?"—":date('d/m/Y',strtotime($data->eco_fecha))}}</span></td>
                    <td><span class="lbl">FE:</span> <span class="val">{{$data->fe_por===null?"n":$data->fe_por}}%</span></td>
                    <td><span class="lbl">Tapse:</span> <span class="val">{{$data->rel_e_a===null||$data->rel_e_a==0?"n":$data->rel_e_a}} mm</span></td>
                </tr>
                <tr>
                    <td><span class="lbl">SGL:</span> <span class="val">{{$data->dd_por===null||$data->dd_por==0?"n":$data->dd_por}} %</span></td>
                    <td><span class="lbl">Movilidad:</span> <span class="val">{{$data->trivi_por===null||$data->trivi_por===0?"n":$data->trivi_por}}</span></td>
                    <td><span class="lbl">PSAP:</span> <span class="val">{{$data->ds_por===null||$data->ds_por==0?"n":$data->ds_por}} mmHg</span></td>
                </tr>
                <tr>
                    <td colspan="3"><span class="lbl">Valvulopatía:</span> <span class="val">{{$data->valvulopatia===0||$data->valvulopatia===null?"n":"s"}}</span> &nbsp; <span class="lbl">Otros:</span> <span class="val">{{$data->otros_eco}}</span></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="cols">
    <tr>
        <td style="width:50%;">
            <div class="section-title">Medicina Nuclear / IRM</div>
            <div class="section-content" style="margin-bottom:3px;">
                <span class="f-bold">Fecha:</span> {{$data->mn_fecha===null?"—":date('d/m/Y',strtotime($data->mn_fecha))}} &nbsp;
                <span class="f-bold">FE:</span> {{$data->fe_por_mn}} &nbsp;
                <span class="f-bold">VRIE:</span> {{$data->vrie===null||$data->vrie===0?"n":"s"}} &nbsp;
                <span class="f-bold">VRIE F.:</span> {{$data->vrie_fcha===null?"n":date('d/m/Y',strtotime($data->vrie_fcha))}}
            </div>
            <table class="dt">
                <thead>
                    <tr><th></th><th>P. Anterior</th><th>P. Septal</th><th>P. Inferior</th><th>P. Lateral</th><th>FEVI</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="f-bold">IM</td>
                        <td class="text-center">{{$data->ant_im===null||$data->ant_im===0?"n":"s"}}</td>
                        <td class="text-center">{{$data->sept_im===null||$data->sept_im===0?"n":"s"}}</td>
                        <td class="text-center">{{$data->inf_im===null||$data->inf_im===0?"n":"s"}}</td>
                        <td class="text-center">{{$data->lat_im===null||$data->lat_im===0?"n":"s"}}</td>
                        <td><span class="lbl">Basal:</span> {{$data->fevi_basal===null?0:$data->fevi_basal}}%</td>
                    </tr>
                    <tr>
                        <td class="f-bold">Isquemia</td>
                        <td class="text-center">{{$data->ant_isq===null||$data->ant_isq===0?"n":"s"}}</td>
                        <td class="text-center">{{$data->sept_isq===null||$data->sept_isq===0?"n":"s"}}</td>
                        <td class="text-center">{{$data->inf_isq===null||$data->inf_isq===0?"n":"s"}}</td>
                        <td class="text-center">{{$data->lat_isq===null||$data->lat_isq===0?"n":"s"}}</td>
                        <td><span class="lbl">10 Dob.:</span> {{$data->fevi_10_dobuta===null?0:$data->fevi_10_dobuta}}%</td>
                    </tr>
                    <tr>
                        <td class="f-bold">R. Reversa</td>
                        <td class="text-center">{{$data->ant_rr===null||$data->ant_rr===0?"n":"s"}}</td>
                        <td class="text-center">{{$data->sept_rr===null||$data->sept_rr===0?"n":"s"}}</td>
                        <td class="text-center">{{$data->inf_rr===null||$data->inf_rr===0?"n":"s"}}</td>
                        <td class="text-center">{{$data->lat_rr===null||$data->lat_rr===0?"n":"s"}}</td>
                        <td><span class="lbl">R. abs.:</span> {{$data->reserva_inot_absolut===null?0:$data->reserva_inot_absolut}}% <span class="lbl">rel.:</span> {{$data->reserva_inot_relat===null?0:$data->reserva_inot_relat}}%</td>
                    </tr>
                </tbody>
            </table>
            <div class="section-title" style="margin-top:6px;">Holter</div>
            <table class="dt">
                <tr>
                    <td><span class="lbl">Holter:</span> <span class="val">{{$data->holter===null||$data->holter===0?"n":"s"}}</span></td>
                    <td><span class="lbl">Fecha:</span> <span class="val">{{$data->holter_fecha===null?"—":date('d/m/Y',strtotime($data->holter_fecha))}}</span></td>
                    <td><span class="lbl">Riesgo:</span> <span class="val">{{$data->holter_riesgo===null||$data->holter_riesgo===''?"n":$data->holter_riesgo}}</span></td>
                </tr>
                @if($data->holter_dignostico)
                <tr>
                    <td colspan="3"><span class="lbl">Diagnóstico:</span> <span class="val">{{$data->holter_dignostico}}</span></td>
                </tr>
                @endif
            </table>
        </td>
        <td style="width:50%;padding-left:5px !important;">
            <div class="section-title">Cateterismo / Antiotag Coronaria</div>
            <div class="section-content" style="margin-bottom:3px;">
                <span class="f-bold">Fecha:</span> {{$data->catet_fecha===null?"—":date('d/m/Y',strtotime($data->catet_fecha))}} &nbsp;
                <span class="f-bold">FE:</span> {{$data->catet_fe===null?0:$data->catet_fe}} &nbsp;
                <span class="f-bold">D2VI:</span> {{$data->catet_d2vi===null||$data->catet_d2vi===0?"n":$data->catet_d2vi}} &nbsp;
                <span class="f-bold">Tronco:</span> {{$data->catet_tco===null?0:$data->catet_tco}}
            </div>
            <table class="dt">
                <thead>
                    <tr><th>Arteria</th><th>Prox.</th><th>Medio</th><th>Distal</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="f-bold">DA</td>
                        <td>{{$data->catet_da_prox===null||$data->catet_da_prox===''||$data->catet_da_prox===0||$data->catet_da_prox==='0'?"n":$data->catet_da_prox.(is_numeric($data->catet_da_prox)?'%':'')}}</td>
                        <td>{{$data->catet_da_med===null||$data->catet_da_med===''||$data->catet_da_med===0||$data->catet_da_med==='0'?"n":$data->catet_da_med.(is_numeric($data->catet_da_med)?'%':'')}}</td>
                        <td>{{$data->catet_da_dist===null||$data->catet_da_dist===''||$data->catet_da_dist===0||$data->catet_da_dist==='0'?"n":$data->catet_da_dist.(is_numeric($data->catet_da_dist)?'%':'')}}</td>
                    </tr>
                    <tr>
                        <td class="f-bold">1a Diagonal</td>
                        <td colspan="3">{{$data->catet_1a_d===null||$data->catet_1a_d===''||$data->catet_1a_d===0||$data->catet_1a_d==='0'?"n":$data->catet_1a_d.(is_numeric($data->catet_1a_d)?'%':'')}}</td>
                    </tr>
                    <tr>
                        <td class="f-bold">2a Diagonal</td>
                        <td colspan="3">{{$data->catet_2a_d===null||$data->catet_2a_d===''||$data->catet_2a_d===0||$data->catet_2a_d==='0'?"n":$data->catet_2a_d.(is_numeric($data->catet_2a_d)?'%':'')}}</td>
                    </tr>
                    <tr>
                        <td class="f-bold">Cx</td>
                        <td>{{$data->catet_cx_prox===null||$data->catet_cx_prox===''||$data->catet_cx_prox===0||$data->catet_cx_prox==='0'?"n":$data->catet_cx_prox.(is_numeric($data->catet_cx_prox)?'%':'')}}</td>
                        <td>—</td>
                        <td>{{$data->catet_cx_dist===null||$data->catet_cx_dist===''||$data->catet_cx_dist===0||$data->catet_cx_dist==='0'?"n":$data->catet_cx_dist.(is_numeric($data->catet_cx_dist)?'%':'')}}</td>
                    </tr>
                    <tr>
                        <td class="f-bold">OM</td>
                        <td colspan="3">{{$data->catet_om===null||$data->catet_om===''||$data->catet_om===0||$data->catet_om==='0'?"n":$data->catet_om.(is_numeric($data->catet_om)?'%':'')}}</td>
                    </tr>
                    <tr>
                        <td class="f-bold">PL</td>
                        <td colspan="3">{{$data->catet_pl===null||$data->catet_pl===''||$data->catet_pl===0||$data->catet_pl==='0'?"n":$data->catet_pl.(is_numeric($data->catet_pl)?'%':'')}}</td>
                    </tr>
                    <tr>
                        <td class="f-bold">CD</td>
                        <td>{{$data->catet_cd_aprox===null||$data->catet_cd_aprox===''||$data->catet_cd_aprox===0||$data->catet_cd_aprox==='0'?"n":$data->catet_cd_aprox.(is_numeric($data->catet_cd_aprox)?'%':'')}}</td>
                        <td>{{$data->catet_cd_med===null||$data->catet_cd_med===''||$data->catet_cd_med===0||$data->catet_cd_med==='0'?"n":$data->catet_cd_med.(is_numeric($data->catet_cd_med)?'%':'')}}</td>
                        <td>{{$data->catet_cd_dist===null||$data->catet_cd_dist===''||$data->catet_cd_dist===0||$data->catet_cd_dist==='0'?"n":$data->catet_cd_dist.(is_numeric($data->catet_cd_dist)?'%':'')}}</td>
                    </tr>
                    <tr>
                        <td class="f-bold">R. Vent. Izq.</td>
                        <td colspan="3">{{$data->catet_r_vent_izq===null||$data->catet_r_vent_izq===''||$data->catet_r_vent_izq===0||$data->catet_r_vent_izq==='0'?"n":$data->catet_r_vent_izq.(is_numeric($data->catet_r_vent_izq)?'%':'')}}</td>
                    </tr>
                    <tr>
                        <td class="f-bold">DP</td>
                        <td colspan="3">{{$data->catet_dp===null||$data->catet_dp===''||$data->catet_dp===0||$data->catet_dp==='0'?"n":$data->catet_dp.(is_numeric($data->catet_dp)?'%':'')}}</td>
                    </tr>
                </tbody>
            </table>
            @if($data->catet_otros || $data->catet_movilidad || $data->catet_riesgo)
            <div class="section-content" style="margin-top:3px;">
                @if($data->catet_movilidad)<span class="f-bold">Movilidad:</span> {{$data->catet_movilidad}} &nbsp;@endif
                @if($data->catet_riesgo)<span class="f-bold">Riesgo:</span> {{$data->catet_riesgo}} &nbsp;@endif
                @if($data->catet_otros)<span class="f-bold">Otros:</span> {{$data->catet_otros}}@endif
            </div>
            @endif
        </td>
    </tr>
</table>

<table class="cols">
    <tr>
        <td style="width:50%;">
            <div class="section-title">Estudios</div>
            <div class="section-accent">{{ $data->estudios }}</div>
        </td>
        <td style="width:50%;padding-left:5px !important;">
            <div class="section-title">Plan</div>
            <div class="section-accent">{{ $data->plan }}</div>
        </td>
    </tr>
</table>

@if(isset($autor) && $autor)
<div style="margin-top:5px;font-size:7.5px;">
    <span class="f-bold">Elaboró:</span> {{ $autor->nombre_completo }}
    @if($autor->cedula) &nbsp; <span style="color:#64748b;">Cédula: {{ $autor->cedula }}</span> @endif
</div>
@endif

@if(isset($esAutor) && $esAutor && isset($firmaBase64) && $firmaBase64)
<div class="firma-wrap">
    <img src="{{ $firmaBase64 }}" alt="Firma">
    <div class="firma-line"></div>
    <div class="firma-name">{{ $autor->nombre_completo ?? $user->nombre_con_titulo }}</div>
</div>
@endif

</div>
</body>
</html>
