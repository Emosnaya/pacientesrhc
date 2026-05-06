<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Prueba Ergométrica</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 8.5px;
      line-height: 1.25;
      color: #1e293b;
      background: #fff;
      padding: 8px 14px 30px 14px;
    }

    /* ── HEADER ── */
    .header {
      background: {!! $clinica->color_principal ?? '#0A1628' !!};
      border-radius: 6px;
      padding: 6px 10px;
      margin-bottom: 6px;
    }
    .header table { width:100%; border-collapse:collapse; }
    .header td { vertical-align:middle; padding:0; }
    .logo-box {
      width:36px; height:36px;
      background:white; border-radius:5px; padding:3px;
      text-align:center; margin-right:8px;
    }
    .logo-box img { max-height:30px; max-width:30px; }
    .h-title { font-size:13px; font-weight:700; color:#fff; letter-spacing:-0.3px; }
    .h-sub   { font-size:8px; color:#94a3b8; }
    .h-meta  { text-align:right; white-space:nowrap; }
    .h-badge { background:rgba(255,255,255,0.15); padding:3px 8px; border-radius:4px; display:inline-block; margin-bottom:2px; }
    .h-badge-lbl { font-size:7px; text-transform:uppercase; letter-spacing:.4px; color:#94a3b8; }
    .h-badge-val { font-size:11px; font-weight:700; color:#fff; }
    .h-date { font-size:8px; color:#94a3b8; }

    /* ── PATIENT STRIP ── */
    .pat-strip {
      background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;
      padding:5px 10px; margin-bottom:6px;
    }
    .pat-name { font-size:11px; font-weight:700; color:{!! $clinica->color_principal ?? '#0A1628' !!}; margin-bottom:3px; }
    .pat-strip table { width:100%; border-collapse:collapse; }
    .pat-strip td { padding:1px 5px; font-size:8px; }
    .lbl { color:#64748b; }
    .val { font-weight:600; color:#334155; }
    .diag { margin-top:4px; padding-top:3px; border-top:1px solid #e2e8f0; font-size:8px; }

    /* ── SECTION TITLE ── */
    .sec {
      font-size:9px; font-weight:700;
      color:{!! $clinica->color_principal ?? '#0A1628' !!};
      border-bottom:1.5px solid {!! $clinica->color_principal ?? '#0A1628' !!};
      padding-bottom:1px; margin-bottom:3px; margin-top:5px;
      text-transform:uppercase; letter-spacing:.4px;
    }

    /* ── GRID HELPERS ── */
    .row  { display:table; width:100%; border-collapse:collapse; }
    .col  { display:table-cell; vertical-align:top; padding-right:6px; }
    .col:last-child { padding-right:0; }
    .w50 { width:50%; }
    .w33 { width:33.33%; }
    .w66 { width:66.66%; }

    /* ── CHECKBOXES LINE ── */
    .chk-img { height:10px; margin-top:1px; vertical-align:middle; }

    /* ── TABLE ── */
    .tabla {
      width:100%; border-collapse:collapse;
      font-size:8px; margin-bottom:4px;
    }
    .tabla th, .tabla td {
      border:1px solid #cbd5e1;
      padding:2px 4px; text-align:center;
    }
    .tabla thead tr { background:{!! $clinica->color_principal ?? '#0A1628' !!}; color:#fff; }
    .tabla thead th { font-size:7.5px; letter-spacing:.3px; }
    .tabla tr.rec-hdr td { background:#334155; color:#fff; font-weight:700; font-size:7.5px; letter-spacing:.3px; }
    .tabla tr.umbral td, .tabla tr.umbral th { background:#fef9c3; font-weight:600; }
    .tabla tbody tr:nth-child(even):not(.rec-hdr):not(.umbral) { background:#f1f5f9; }
    .tabla td:first-child { text-align:left; font-weight:600; color:#475569; padding-left:5px; }

    /* ── DATA PAIRS ── */
    .pairs { font-size:8px; line-height:1.75; }
    .pairs b { font-weight:700; color:#334155; }

    /* ── FOOTER ── */
    .page-footer {
      position:fixed; bottom:0; left:0; right:0;
      padding:4px 14px;
      background:#fff;
      border-top:2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
      font-size:8px;
    }
    .page-footer table { width:100%; }
    .page-footer .cn { font-weight:700; color:{!! $clinica->color_principal ?? '#0A1628' !!}; }
    .page-footer .cc { text-align:right; color:#64748b; }

    /* ── FIRMA ── */
    .firma-wrap {
      margin-top:8px;
      padding-top:6px;
      border-top:1px dashed #cbd5e1;
      text-align:center;
    }
    .firma-wrap img { height:44px; width:auto; display:block; margin:0 auto 2px; }
    .firma-line { border-top:1px solid #475569; width:140px; margin:2px auto; }
    .firma-name { font-size:8px; color:#334155; font-weight:600; }
    .firma-ced  { font-size:7.5px; color:#64748b; }

    /* ── GRAY BOX ── */
    .gbox {
      background:#f8fafc;
      border:1px solid #e2e8f0;
      border-left:3px solid {!! $clinica->color_principal ?? '#0A1628' !!};
      border-radius:4px;
      padding:5px 8px;
    }

    /* ── CHECKS ── */
    .checks { font-size:8px; line-height:1.8; }
    .checks b { font-weight:700; color:#475569; }
    .chk-ok { color:#16a34a; font-weight:700; }
    .chk-no { color:#94a3b8; }
  </style>
</head>
<body>

<!-- FOOTER fijo -->
<div class="page-footer">
  <table><tr>
    <td class="cn">{{ $clinica->nombre ?? 'Clínica' }}</td>
    <td class="cc">
      {{ $clinica->telefono ?? '' }}
      @if($clinica->email ?? null) | {{ $clinica->email }} @endif
    </td>
  </tr></table>
</div>

<!-- HEADER -->
<div class="header">
  <table><tr>
    <td style="width:42px;">
      <div class="logo-box">
        @if(isset($clinicaLogo) && $clinicaLogo)
          <img src="{{ $clinicaLogo }}" alt="Logo">
        @endif
      </div>
    </td>
    <td>
      <div class="h-title">Prueba Ergométrica {{ isset($data->tipo_esfuerzo) && $data->tipo_esfuerzo === 'pulmonar' ? 'Pulmonar' : 'Cardíaca' }}</div>
      <div class="h-sub">Reporte de evaluación funcional</div>
    </td>
    <td class="h-meta">
      <div class="h-badge">
        <div class="h-badge-lbl">Registro</div>
        <div class="h-badge-val">#{{ $paciente->registro }}</div>
      </div>
      <div class="h-date">{{ date('d/m/Y', strtotime($data->fecha)) }}</div>
    </td>
  </tr></table>
</div>

<!-- PACIENTE -->
<div class="pat-strip">
  <div class="pat-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
  <table><tr>
    <td><span class="lbl">Peso:</span> <span class="val">{{ $paciente->peso }} kg</span></td>
    <td><span class="lbl">Talla:</span> <span class="val">{{ $paciente->talla }} cm</span></td>
    <td><span class="lbl">Edad:</span> <span class="val">{{ $paciente->edad }} años</span></td>
    <td><span class="lbl">IMC:</span> <span class="val">{{ round($paciente->imc,2) }}</span></td>
    <td><span class="lbl">Género:</span> <span class="val">{{ $paciente->genero == 1 ? 'Masculino' : 'Femenino' }}</span></td>
    @if($paciente->medicamentos)
    <td colspan="2"><span class="lbl">Medicamentos:</span> {{ $paciente->medicamentos }}</td>
    @endif
  </tr></table>
  @if($paciente->diagnostico)
  <div class="diag"><b>Diagnóstico:</b> {{ $paciente->diagnostico }}</div>
  @endif
</div>

<!-- PRUEBA + TABLA en 2 columnas -->
<div class="row">
  <!-- Izquierda: info de prueba -->
  <div class="col w50">
    <div class="sec">Prueba</div>
    <div class="checks">
      <b>Equipo:</b>
      Banda @if($data->banda===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
      Cicloergómetro @if($data->ciclo===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
      VO2 directo @if($data->medicionGases===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif
    </div>
    <div class="checks">
      <b>Protocolo:</b>
      Bruce @if($data->bruce===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
      Balke @if($data->balke===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
      @if(isset($data->tipo_esfuerzo) && $data->tipo_esfuerzo === 'pulmonar')
      Naughton @if(isset($data->naughton) && $data->naughton===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
      @endif
      Submáxima @if($data->pba_submax===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif
    </div>
    <div class="checks">
      <b>Momento:</b>
      1ª vez @if($data->pruebaIngreso===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
      Fase II @if($data->pruebaFinFase2===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif &nbsp;
      Fase III @if($data->pruebaFinFase3===1)<img class="chk-img" src="img/check-solid.svg">@else<img class="chk-img" src="img/x-solid.svg">@endif
    </div>
    <div class="checks" style="margin-top:3px;">
      <b>FCmax teórica:</b> {{$data->fc_max_calc}} &nbsp;
      <b>FC 85%:</b> {{round($data->fc_85)}} &nbsp;
      <b>% FCmax alc.:</b> {{round($data->fc_max_alcanzado)}}
    </div>
  </div>

  <!-- Derecha: tabla -->
  <div class="col w50">
    <div class="sec">Registro por Etapa</div>
    <table class="tabla">
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
  </div>
</div>

<!-- DESEMPEÑO + GASES en 2 columnas -->
<div class="row" style="margin-top:4px;">
  <div class="col w66">
    <div class="sec">Desempeño</div>
    <div class="gbox pairs">
      <b>Tiempo esf.:</b> {{ round($data->tiempoEsfuerzo,2) }} min
      (protocolo:
      @if(isset($data->tipo_esfuerzo) && $data->tipo_esfuerzo=='pulmonar' && isset($data->naughton) && $data->naughton==1)
        Naughton
      @elseif($data->bruce==1)
        Bruce
      @elseif($data->balke==1)
        Balke
      @else
        Bruce
      @endif)
      &nbsp; <b>Suspensión:</b> {{$data->motivoSuspension}}<br>
      <b>METs teórico:</b> {{round($data->mets_teorico_general,2)}} &nbsp;
      <b>%METs alc.:</b>
      @if($data->medicionGases===1 && $data->vo2_max_percent !== null)
        {{ round($data->vo2_max_percent,2) }}
      @else
        {{ $data->mets_teorico_general != 0 ? round($data->mets_max/$data->mets_teorico_general*100,2) : '—' }}
      @endif &nbsp;
      <b>R. Pres.:</b> {{round($data->resp_presora,2)}} &nbsp;
      <b>MVo2(METs):</b> {{round($data->mvo2/3.5*0.1,2)}}<br>
      <b>R. Cron.:</b> {{round($data->resp_crono,2)}} &nbsp;
      <b>TASmax/TASbasal:</b> {{sprintf("%.2f", floor($data->indice_tas*100)/100)}} &nbsp;
      <b>IEM:</b> {{sprintf("%.2f", floor($data->iem*100)/100)}} &nbsp;
      <b>Rec. FC 1er min:</b> {{$data->fcmax_fc1er}} lpm<br>
      <b>Rec TAS (3/1):</b> {{ $data->tas_1er_min != 0 ? sprintf("%.2f", floor(($data->tas_3er_min/$data->tas_1er_min)*100)/100) : '—' }} &nbsp;
      <b>PCE (mmHg%):</b> {{round($data->pce)}}
    </div>
  </div>
  <div class="col w33">
    <div class="sec">Gases Espirados</div>
    <div class="gbox pairs">
      <b>VO2max:</b> {{ round($data->vo2_max_gases,2) }} mlO2/Kg/min<br>
      <b>VO2pico:</b> {{round($data->vo2_pico_gases,2)}} mlO2/Kg/min<br>
      <b>R/Q (máx. esf.):</b> {{round($data->r_qmax,2)}}<br>
      <b>Umbral A/An:</b> {{$data->umbral_aeer_anaer==null?0:$data->umbral_aeer_anaer}} mlO2/Kg/min<br>
      <b>%PO2 teórico:</b> {{$data->po2_teor==null?0:$data->po2_teor}}
    </div>
  </div>
</div>

<!-- ISQUEMIA + ARRITMIAS + PUNTUACIONES en 3 columnas -->
<div class="row" style="margin-top:4px;">
  <div class="col w33">
    <div class="sec">Isquemia</div>
    <div class="pairs">
      <b>Índice Angina:</b> {{$data->scoreAngina}}<br>
      <b>Dep. máx ST (mm):</b> {{$data->MaxInfra}}<br>
      <b>Tipo cambio:</b> {{$data->tipoCambioElectrico}}
    </div>
  </div>
  <div class="col w33">
    <div class="sec">Arritmias</div>
    <div class="pairs">{{$data->tipoArritmias}}</div>
  </div>
  <div class="col w33">
    <div class="sec">Puntuaciones</div>
    <div class="pairs">
      <b>Duke:</b> {{ round($data->duke,2) }}<br>
      <b>Veteranos (VA):</b> {{round($data->veteranos,2)}}
    </div>
  </div>
</div>

<!-- CONCLUSIONES + CARDIOPATÍA en 2 columnas -->
<div class="row" style="margin-top:4px;">
  <div class="col w50">
    <div class="sec">Conclusiones</div>
    <div class="gbox pairs">
      <b>Conclusiones:</b> {{ $data->conclusiones }}<br>
      <b>Riesgo general:</b> {{$data->riesgo}}
    </div>
  </div>
  <div class="col w50">
    <div class="sec">Cardiopatía Isquémica</div>
    <div class="gbox pairs">
      <b>Confusor:</b> {{$data->confusor}}<br>
      <b>Prob. pre-prueba:</b> {{$data->prevalencia*100}}%<br>
      <b>Sensibilidad:</b> {{$data->sensibilidad*100}}%<br>
      <b>Especificidad:</b> {{$data->especificidad*100}}%<br>
      <b>V. Predictivo:</b> {{round($data->vpp*100)}}%
    </div>
  </div>
</div>

<!-- FIRMA CENTRADA AL FONDO -->
@if(isset($autor) && $autor)
<div style="margin-top:14px; text-align:center;">
  @if(isset($esAutor) && $esAutor && isset($firmaBase64) && $firmaBase64)
  <img src="{{ $firmaBase64 }}" alt="Firma" style="height:50px; width:auto; display:block; margin:0 auto 4px;">
  @endif
  <div style="width:180px; border-top:1px solid #475569; margin:0 auto 3px;"></div>
  <div style="font-size:9px; font-weight:700; color:#1e293b;">{{ $autor->nombre_completo }}</div>
  @if($autor->cedula)
  <div style="font-size:8px; color:#64748b;">Cédula Profesional: {{ $autor->cedula }}</div>
  @endif
</div>
@endif

</body>
</html>
