<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Prueba Ergométrica</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 9px;
      line-height: 1.35;
      color: #1e293b;
      background: #ffffff;
      padding: 8px 15px;
    }

    /* ── PAGE FOOTER (fixed) ── */
    .page-footer {
      position: fixed; bottom: 0; left: 0; right: 0;
      padding: 4px 14px;
      background: white;
      border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
      font-size: 8px;
    }
    .page-footer-table { width: 100%; border-collapse: collapse; }
    .page-footer .clinic-name { font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; }
    .page-footer .clinic-contact { text-align: right; color: #64748b; }

    /* ── CONTENT WRAPPER ── */
    .content-wrapper { padding-bottom: 28px; }

    /* ── HEADER ── */
    .header {
      width: 100%;
      background: {!! $clinica->color_principal ?? '#0A1628' !!};
      border-radius: 6px;
      margin-bottom: 7px;
      padding: 6px 11px;
    }
    .header-table { width: 100%; border-collapse: collapse; }
    .header-table td { vertical-align: middle; padding: 0; }
    .header-logo-cell { width: 50px; padding-right: 10px !important; }
    .header-logo {
      width: 38px; height: 38px;
      background: white; border-radius: 5px; padding: 3px; text-align: center;
    }
    .header-logo img { max-height: 32px; max-width: 32px; }
    .header-title { font-size: 15px; font-weight: 700; color: white; letter-spacing: -0.3px; }
    .header-subtitle { font-size: 8.5px; color: #94a3b8; }
    .header-meta-cell { text-align: right; width: 108px; }
    .header-badge {
      background: rgba(255,255,255,0.15); padding: 4px 9px;
      border-radius: 4px; display: inline-block; margin-bottom: 2px;
    }
    .header-badge-label { font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.4px; color: #94a3b8; }
    .header-badge-value { font-size: 12px; font-weight: 700; color: white; }
    .header-date { font-size: 8.5px; color: #94a3b8; }

    /* ── PATIENT CARD ── */
    .patient-card {
      background: #f8fafc; border: 1px solid #e2e8f0;
      border-radius: 6px; padding: 6px 10px; margin-bottom: 7px;
    }
    .patient-name { font-size: 12px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; margin-bottom: 4px; }
    .pt { width: 100%; border-collapse: collapse; }
    .pt td { padding: 1.5px 5px; font-size: 8.5px; }
    .plabel { color: #64748b; }
    .pvalue { font-weight: 600; color: #334155; }
    .pdx { margin-top: 4px; padding-top: 4px; border-top: 1px solid #e2e8f0; font-size: 8.5px; }
    .pdx-label { color: #64748b; font-weight: 600; }

    /* ── METRICS BAR ── */
    .metrics-table { width: 100%; border-collapse: collapse; margin-bottom: 7px; }
    .metrics-table td {
      text-align: center; padding: 6px 5px;
      border: 1px solid #e2e8f0; background: white;
    }
    .metrics-table td.highlight {
      background: {!! $clinica->color_principal ?? '#0A1628' !!};
      color: white; border-color: {!! $clinica->color_principal ?? '#0A1628' !!};
    }
    .metric-label { font-size: 7.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
    .metrics-table td.highlight .metric-label { color: #94a3b8; }
    .metric-value { font-size: 13px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; }
    .metrics-table td.highlight .metric-value { color: white; }
    .metric-unit { font-size: 7.5px; font-weight: 400; color: #64748b; }
    .metrics-table td.highlight .metric-unit { color: #94a3b8; }

    /* ── SECTION TITLE ── */
    .section-title {
      font-size: 9px; font-weight: 700;
      color: {!! $clinica->color_principal ?? '#0A1628' !!};
      text-transform: uppercase; letter-spacing: 0.4px;
      margin-bottom: 4px; padding-bottom: 2px;
      border-bottom: 1.5px solid {!! $clinica->color_principal ?? '#0A1628' !!};
    }

    /* ── SECTION CONTENT (gray bg) ── */
    .section-content {
      background: #f8fafc; border: 1px solid #e2e8f0;
      border-radius: 5px; padding: 5px 8px; margin-bottom: 6px;
      font-size: 8.5px; line-height: 1.7;
    }
    .section-content b { font-weight: 700; color: #334155; }

    /* ── SECTION CONTENT with accent left border ── */
    .section-accent {
      background: #f8fafc; border: 1px solid #e2e8f0;
      border-left: 3px solid {!! $clinica->color_principal ?? '#0A1628' !!};
      border-radius: 0 5px 5px 0; padding: 5px 9px; margin-bottom: 6px;
      font-size: 8.5px; line-height: 1.7;
    }
    .section-accent b { font-weight: 700; color: #334155; }

    /* ── TWO / THREE COLUMN LAYOUT (table based for DomPDF) ── */
    .cols { width: 100%; border-collapse: collapse; }
    .cols td { vertical-align: top; padding: 0 4px; }
    .cols td:first-child { padding-left: 0; }
    .cols td:last-child { padding-right: 0; }
    .w50 { width: 50%; }
    .w33 { width: 33.33%; }
    .w66 { width: 66.66%; }

    /* ── CHECK IMAGES ── */
    .chk-img { height: 11px; margin-top: 1px; vertical-align: middle; }

    /* ── STAGE TABLE ── */
    .stage-table { width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 3px; }
    .stage-table th, .stage-table td {
      border: 1px solid #cbd5e1; padding: 2.5px 4px; text-align: center;
    }
    .stage-table thead tr { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: #fff; }
    .stage-table thead th { font-size: 7.5px; letter-spacing: 0.3px; }
    .stage-table tr.rec-hdr td { background: #334155; color: #fff; font-weight: 700; font-size: 8px; }
    .stage-table tr.umbral td { background: #fef9c3; font-weight: 600; }
    .stage-table tbody tr:nth-child(even):not(.rec-hdr):not(.umbral) { background: #f1f5f9; }
    .stage-table td:first-child { text-align: left; font-weight: 600; color: #475569; padding-left: 6px; }

    /* ── CHECKS LIST ── */
    .checks { font-size: 8.5px; line-height: 1.75; }
    .checks b { font-weight: 700; color: #334155; }

    /* ── SIGNATURE ── */
    .firma-wrap { margin-top: 8px; text-align: center; }
    .firma-wrap img { height: 44px; width: auto; display: block; margin: 0 auto 3px; }
    .firma-line { border-top: 1px solid #475569; width: 165px; margin: 0 auto 2px; }
    .firma-name { font-size: 8.5px; font-weight: 700; color: #1e293b; }
    .firma-ced  { font-size: 8px; color: #64748b; }
  </style>
</head>
<body>

<!-- PAGE FOOTER (fixed) -->
<div class="page-footer">
  <table class="page-footer-table"><tr>
    <td class="clinic-name">{{ $clinica->nombre ?? 'Clínica' }}</td>
    <td class="clinic-contact">
      {{ $clinica->telefono ?? '' }}
      @if($clinica->email ?? null) | {{ $clinica->email }} @endif
    </td>
  </tr>
  <tr>
    <td colspan="2" style="text-align:center; padding-top:3px; font-size:7px; color:#94a3b8;">
      Generado con <strong style="color:{!! $clinica->color_principal ?? '#0A1628' !!};">Lynkamed</strong>
    </td>
  </tr></table>
</div>

<div class="content-wrapper">

<!-- HEADER -->
<div class="header">
  <table class="header-table"><tr>
    <td class="header-logo-cell">
      <div class="header-logo">
        @if(isset($clinicaLogo) && $clinicaLogo)
          <img src="{{ $clinicaLogo }}" alt="Logo">
        @endif
      </div>
    </td>
    <td style="padding-left:10px;">
      <div class="header-title">Prueba Ergométrica {{ isset($data->tipo_esfuerzo) && $data->tipo_esfuerzo === 'pulmonar' ? 'Pulmonar' : 'Cardíaca' }}</div>
    </td>
    <td class="header-meta-cell">
      <div class="header-badge">
        <div class="header-badge-label">Registro</div>
        <div class="header-badge-value">#{{ $paciente->registro }}</div>
      </div>
      <div class="header-date">{{ date('d/m/Y', strtotime($data->fecha)) }}</div>
    </td>
  </tr></table>
</div>

<!-- PACIENTE -->
<div class="patient-card">
  <div class="patient-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
  <table class="pt">
    <tr>
      <td><span class="plabel">Edad:</span> <span class="pvalue">{{ $paciente->edad }} años</span></td>
      <td><span class="plabel">Género:</span> <span class="pvalue">{{ $paciente->genero == 1 ? 'Hombre' : 'Mujer' }}</span></td>
      <td><span class="plabel">Peso:</span> <span class="pvalue">{{ $paciente->peso }} kg</span></td>
      <td><span class="plabel">Talla:</span> <span class="pvalue">{{ $paciente->talla }} m</span></td>
      <td><span class="plabel">IMC:</span> <span class="pvalue">{{ round($paciente->imc, 2) }}</span></td>
    </tr>
    <tr>
      <td><span class="plabel">F. Nac.:</span> <span class="pvalue">{{ $paciente->fechaNacimiento ? date('d/m/Y', strtotime($paciente->fechaNacimiento)) : '—' }}</span></td>
      <td><span class="plabel">Prueba:</span> <span class="pvalue">#{{ $data->numPrueba ?? '—' }}</span></td>
      <td><span class="plabel">F. Prueba:</span> <span class="pvalue">{{ $data->fecha ? date('d/m/Y', strtotime($data->fecha)) : '—' }}</span></td>
    </tr>
  </table>
  @if($paciente->medicamentos)
  <div class="pdx"><span class="pdx-label">Diagnóstico:</span> {{ $paciente->diagnostico }}</div>
  @endif
  @if($paciente->diagnostico)
  <div class="pdx"><span class="pdx-label">Medicamentos:</span> {{ $paciente->medicamentos }}</div>
  @endif
</div>

<!-- MÉTRICAS FC -->
<table class="metrics-table">
  <tr>
    <td class="highlight">
      <div class="metric-label">FCmax teórica</div>
      <div class="metric-value">{{ $data->fc_max_calc }} <span class="metric-unit">lpm</span></div>
    </td>
    <td class="highlight">
      <div class="metric-label">FC 85%</div>
      <div class="metric-value">{{ round($data->fc_85) }} <span class="metric-unit">lpm</span></div>
    </td>
    <td class="highlight">
      <div class="metric-label">% FCmax alc.</div>
      <div class="metric-value">{{ round($data->fc_max_alcanzado) }}<span class="metric-unit">%</span></div>
    </td>
    <td>
      <div class="metric-label">METs máx.</div>
      <div class="metric-value" style="color:{!! $clinica->color_principal ?? '#0A1628' !!};">{{ round($data->mets_max,1) }}</div>
    </td>
    <td>
      <div class="metric-label">Tiempo esf.</div>
      <div class="metric-value" style="color:{!! $clinica->color_principal ?? '#0A1628' !!};">{{ round($data->tiempoEsfuerzo,2) }} <span class="metric-unit">min</span></div>
    </td>
    <td>
      <div class="metric-label">Motivo de suspensión:</div>
      <div class="metric-value" style="font-size:9px; color:{!! $clinica->color_principal ?? '#0A1628' !!};">{{ $data->motivoSuspension }}</div>
    </td>
  </tr>
</table>

<!-- PRUEBA + TABLA DE ETAPAS -->
<table class="cols" style="margin-bottom:5px;">
  <tr>
    <!-- Izquierda: configuración de prueba -->
    <td class="w50">
      <div class="section-title">Configuración de Prueba</div>
      <div class="section-content">
        <div class="checks">
          <b>Equipo:</b>
          Banda @if($data->banda===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
          Cicloergómetro @if($data->ciclo===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
          VO2 directo @if($data->medicionGases===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif
        </div>
        <div class="checks">
          <b>Protocolo:</b>
          Bruce @if($data->bruce===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
          Balke @if($data->balke===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif
          @if(isset($data->tipo_esfuerzo) && $data->tipo_esfuerzo === 'pulmonar')
          &nbsp; Naughton @if(isset($data->naughton) && $data->naughton===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif
          @endif
          &nbsp; Submáxima @if($data->pba_submax===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif
        </div>
        <div class="checks">
          <b>Momento:</b>
          1ª vez @if($data->pruebaIngreso===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
          Fase II @if($data->pruebaFinFase2===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
          Fase III @if($data->pruebaFinFase3===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif
        </div>
      </div>
    </td>
    <!-- Derecha: tabla de etapas -->
    <td class="w50">
      <div class="section-title">Registro por Etapa</div>
      <table class="stage-table">
        <thead><tr>
          <th>Etapa</th><th>METs</th><th>FC</th><th>TAS</th><th>TAD</th><th>Borg</th><th>DP</th>
        </tr></thead>
        <tbody>
          <tr>
            <td>Basal</td><td>1.0</td><td>{{$data->fcBasal}}</td><td>{{$data->tasBasal}}</td><td>{{$data->tadBasal}}</td><td>—</td><td>{{$data->dapBasal}}</td>
          </tr>
          <tr>
            <td>Borg 12</td><td>{{round($data->mets_borg_12,1)}}</td><td>{{$data->fcBorg12}}</td><td>{{$data->tasBorg12}}</td><td>{{$data->tadBorg12}}</td><td>—</td><td>{{$data->dpBorg12}}</td>
          </tr>
          <tr>
            <td>Máx. Esf.</td><td>{{round($data->mets_max,1)}}</td><td>{{$data->fcMax}}</td><td>{{$data->tasMax}}</td><td>{{$data->tadMax}}</td><td>{{$data->borgMax}}</td><td>{{$data->dpMax}}</td>
          </tr>
          <tr class="rec-hdr"><td colspan="7">— Recuperación —</td></tr>
          <tr>
            <td>1er min</td><td>—</td><td>{{$data->fc_1er_min}}</td><td>{{$data->tas_1er_min}}</td><td>{{$data->tad_1er_min}}</td><td>{{$data->borg_1er_min}}</td><td>{{$data->fc_1er_min*$data->tas_1er_min}}</td>
          </tr>
          <tr>
            <td>3er min</td><td>—</td><td>{{$data->fc_3er_min}}</td><td>{{$data->tas_3er_min}}</td><td>{{$data->tad_3er_min}}</td><td>{{$data->borg_3er_min}}</td><td>{{$data->fc_3er_min*$data->tas_3er_min}}</td>
          </tr>
          <tr>
            <td>5to min</td><td>—</td><td>{{$data->fc_5to_min}}</td><td>{{$data->tas_5to_min}}</td><td>{{$data->tad_5to_min}}</td><td>—</td><td>{{$data->fc_5to_min*$data->tas_5to_min}}</td>
          </tr>
          <tr>
            <td>8vo min</td><td>—</td><td>{{$data->fc_8vo_min}}</td><td>{{$data->tas_8vo_min}}</td><td>{{$data->tad_8vo_min}}</td><td>—</td><td>{{$data->fc_8vo_min*$data->tas_8vo_min}}</td>
          </tr>
          <tr class="umbral">
            <td><b>U. Isq.</b></td><td>{{round($data->mets_banda_U_isq,1)}}</td><td>{{$data->fc_U_isq}}</td><td>{{$data->tas_U_isq}}</td><td>{{$data->tad_U_isq}}</td><td>{{$data->borg_U_isq}}</td><td>{{$data->fc_U_isq*$data->tas_U_isq}}</td>
          </tr>
        </tbody>
      </table>
    </td>
  </tr>
</table>

<!-- DESEMPEÑO + GASES -->
<table class="cols" style="margin-bottom:5px;">
  <tr>
    <td class="w66">
      <div class="section-title">Desempeño</div>
      <div class="section-accent">
        <b>METs teórico:</b> {{round($data->mets_teorico_general,2)}} &nbsp;
        <b>%METs alc.:</b>
        @if($data->medicionGases===1 && $data->vo2_max_percent !== null)
          {{ round($data->vo2_max_percent,2) }}
        @else
          {{ $data->mets_teorico_general != 0 ? round($data->mets_max/$data->mets_teorico_general*100,2) : '—' }}
        @endif
        &nbsp; <b>R. Pres.:</b> {{round($data->resp_presora,2)}} &nbsp; <b>MVo2(METs):</b> {{round($data->mvo2/3.5*0.1,2)}}<br>
        <b>R. Cron.:</b> {{round($data->resp_crono,2)}} &nbsp;
        <b>TASmax/TASbasal:</b> {{sprintf("%.2f", floor($data->indice_tas*100)/100)}} &nbsp;
        <b>IEM:</b> {{sprintf("%.2f", floor($data->iem*100)/100)}} &nbsp;
        <b>Rec. FC 1er min:</b> {{$data->fcmax_fc1er}} lpm<br>
        <b>Rec TAS (3/1):</b> {{ $data->tas_1er_min != 0 ? sprintf("%.2f", floor(($data->tas_3er_min/$data->tas_1er_min)*100)/100) : '—' }} &nbsp;
        <b>PCE (mmHg%):</b> {{round($data->pce)}}
      </div>
    </td>
    <td class="w33">
      <div class="section-title">Gases Espirados (CPET)</div>
      <div class="section-content">
        <b>VO2max:</b> {{ round($data->vo2_max_gases,2) }} mlO2/Kg/min<br>
        <b>VO2pico:</b> {{round($data->vo2_pico_gases,2)}} mlO2/Kg/min<br>
        <b>R/Q (máx. esf.):</b> {{round($data->r_qmax,2)}}<br>
        <b>Umbral A/An:</b> {{$data->umbral_aeer_anaer==null?0:$data->umbral_aeer_anaer}} mlO2/Kg/min<br>
        <b>%PO2 teórico:</b> {{$data->po2_teor==null?0:$data->po2_teor}}
      </div>
    </td>
  </tr>
</table>

<!-- ISQUEMIA + ARRITMIAS + PUNTUACIONES -->
<table class="cols" style="margin-bottom:5px;">
  <tr>
    <td class="w33">
      <div class="section-title">Isquemia</div>
      <div class="section-content">
        <b>Índice Angina:</b> {{$data->scoreAngina}}<br>
        <b>Dep. máx ST (mm):</b> {{$data->MaxInfra}}<br>
        <b>Tipo cambio del ST:</b> {{$data->tipoCambioElectrico}}
      </div>
    </td>
    <td class="w33">
      <div class="section-title">Arritmias</div>
      <div class="section-content">{{$data->tipoArritmias}}</div>
    </td>
    <td class="w33">
      <div class="section-title">Puntuaciones</div>
      <div class="section-content">
        <b>Duke:</b> {{ round($data->duke,2) }}<br>
        <b>Veteranos (VA):</b> {{round($data->veteranos,2)}}
      </div>
    </td>
  </tr>
</table>

<!-- RIESGO GLOBAL -->
<table class="cols" style="margin-bottom:5px;">
  <tr>
    <td>
      <div class="section-title">Riesgo Global</div>
      <div class="section-accent">{{ $data->riesgo }}</div>
    </td>
  </tr>
</table>

<!-- CONCLUSIONES -->
<table class="cols" style="margin-bottom:5px;">
  <tr>
    <td>
      <div class="section-title">Conclusiones</div>
      <div class="section-accent">{{ $data->conclusiones }}</div>
      <div class="section-content" style="margin-top:4px;">
        <b>Confusor:</b> {{$data->confusor}}<br>
        <b>Prob. pre-prueba:</b> {{$data->prevalencia*100}}%<br>
        <b>Sensibilidad:</b> {{$data->sensibilidad*100}}% &nbsp; <b>Especificidad:</b> {{$data->especificidad*100}}%<br>
        <b>V. Predictivo:</b> {{round($data->vpp*100)}}%
      </div>
    </td>
  </tr>
</table>

<!-- FIRMA -->
@if(isset($autor) && $autor)
<div class="firma-wrap">
  @if(isset($esAutor) && $esAutor && isset($firmaBase64) && $firmaBase64)
  <img src="{{ $firmaBase64 }}" alt="Firma">
  @endif
  <div class="firma-line"></div>
  <div class="firma-name">{{ $autor->nombre_completo }}</div>
  @if($autor->cedula)
  <div class="firma-ced">Cédula Profesional: {{ $autor->cedula }}</div>
  @endif
</div>
@endif

</div><!-- end content-wrapper -->
</body>
</html>
