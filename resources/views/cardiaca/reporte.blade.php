
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- Bootstrap CSS -->
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #1e293b;
            background: #ffffff;
            padding: 8px 16px;
        }
        /* Estilo para el logo */
        .logo-container {
            height: 36px;
            overflow: hidden;
            display: inline-block;
        }
        .logo-container img {
            height: 36px;
            width: auto;
        }
        /* Estilo para la línea de firma */
        .signature {
        text-align: left;
        width: 100%;
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    
    .signature img {
        max-width: 200px;
        max-height: 80px;
        object-fit: contain;
        border: none;
        filter: none;
    }
    .line {
        display: inline-block;
        border-top: 1px solid black;
        width: 20%;
        margin-top: 4rem;
        margin-right: 2rem;
        margin-left: 2rem
        padding: 1rem;
    }
    .text {
        font-size: 11px;
        text-align: center;
        width: 100%; /* Espacio entre línea y texto */
    }
        .tabla{
            font-size: 10px;
            margin-bottom: 0;
            width: 100%
        }
        .f-10{
          font-size: 12px;
        }
        .f-15{
          font-size: 14px;
        }
        .paciente{
            font-size: 12px
        }
        .text-right{
            text-align: right;
        }
        .f-bold{
            font-weight: bold;
        }
        .f-normal{
            font-weight: normal
        }
        .text-lft{
            text-align: left;
        }
        .text-ctr{
            text-align: center;
        }

        .flex{
          display: flex;
        }

        .container-g {
        width: 90%;
    }
    .table-container-g {
        width: 31%;
        float: left;
        padding: 0.5rem
    }
    .text-container-g {
        width: 40%;
        float: left;
    }
    .table-g {
        border: 1px solid black;
        width: 100%;
    }
    .border-t{
      border: 1px solid black;
    }
    .border-l{
      border-left: 1px solid black;
    }
    .border-r{
      border-right: 1px solid black;
    }
    .border-b{
      border-bottom: 1px solid black;
    }
    .b-dark{
      background-color: #000;
      color: white;
      
    }
    .b-w{
      background-color: #ffffff;
      color: black;
      
    }
    .coments{
      position: absolute;
      text-align:left;
      left: 0;
    }
    .txt{
      width: 20%;
      margin-left: 1.5rem;
      margin-right: 5rem;
    }
    .medio{
      position: relative;
    }
  
  .texto-izquierda {
    text-align: left; /* Alinear a la izquierda */
    position: absolute; /* Posicionamiento absoluto */
    left: 0; /* /* Alinear a la izquierda */
  }
  
  .texto-derecha {
    text-align: right; /* Alinear a la derecha */
    position: absolute; /* Posicionamiento absoluto */
    right: 0;; /* Alinear a la derecha */
  }
  .linea-pu {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 11rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.5rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-t {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 17.2rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.6rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-p {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 10.5rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.6rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .contenedor {
    position: relative; /* Establece contexto de posición */
    text-align:justify; /* Alinea contenido al centro horizontalmente */
    margin-bottom: 0; /* Espacio opcional al final del contenedor */
  }
  
  .titulo {
    display: inline-block;/* Hace que el título sea un bloque en línea */ /* Opcional: fondo blanco detrás del título */ /* Espaciado opcional alrededor del título */
    position: relative; /* Establece contexto de posición */
    z-index: 1; /* Asegura que el título esté por encima de la línea */
  }
  .m-t-1{
    margin-top: -1rem;
  }
  .back-blk{
    background-color: #000;
  }
  .f-17{
    font-size: 17px;
  }
  .ma-bo{
    margin-left: 4rem;
    padding-left: 7.2rem
  }
  .mt-d{
    margin-top: 2rem;
  }
  .txt-blue{
    color: #255FA5;
  }
  .txt-r{
    color: #FB0006;
  }
  .marg-final{
    margin-top: 7rem
  }
        /* === HEADER MODERNO === */
        .header { width: 100%; background: {!! $clinica->color_principal ?? '#0A1628' !!}; border-radius: 8px; margin-bottom: 6px; padding: 6px 10px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-cell { width: 60px; padding-right: 12px !important; }
        .header-logo { width: 45px; height: 45px; background: white; border-radius: 6px; padding: 5px; text-align: center; }
        .header-logo img { max-height: 35px; max-width: 35px; }
        .header-title { font-size: 16px; font-weight: 700; color: white; letter-spacing: -0.5px; }
        .header-subtitle { font-size: 9px; color: #94a3b8; }
        .header-meta-cell { text-align: right; width: 120px; }
        .header-badge { background: rgba(255,255,255,0.15); padding: 5px 10px; border-radius: 5px; display: inline-block; margin-bottom: 4px; }
        .header-badge-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; }
        .header-badge-value { font-size: 12px; font-weight: 700; color: white; }
        .header-date { font-size: 9px; color: #94a3b8; }
        .patient-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 7px 10px; margin-bottom: 6px; }
        .patient-table { width: 100%; border-collapse: collapse; }
        .patient-table td { padding: 1px 6px; font-size: 9px; }
        .patient-name { font-size: 12px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; margin-bottom: 3px; }
        .patient-label { color: #64748b; font-size: 8.5px; }
        .patient-value { font-weight: 600; color: #334155; }
        .patient-diagnosis { margin-top: 3px; padding-top: 3px; border-top: 1px solid #e2e8f0; font-size: 9px; }
        .patient-diagnosis-label { font-size: 8.5px; color: #64748b; font-weight: 600; }

        /* === PÁGINA 1 COMPACTA === */
        .page-one-intro {
            font-size: 11px;
            line-height: 1.35;
            margin: 0 0 5px;
        }
        .page-one-method { margin: 0 0 4px; }
        .page-one-method-title {
            font-size: 11px;
            font-weight: 700;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            margin-bottom: 3px;
        }
        .page-one-method p {
            font-size: 10px;
            line-height: 1.35;
            margin: 0 0 3px;
        }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 6px 20px; background: white; border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!}; font-size: 9px; }
        .page-footer-table { width: 100%; }
        .page-footer .clinic-name { font-weight: 700; color: #ef4444; }
        .page-footer .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 35px; }

        /* === TABLA DE RESULTADOS === */
        .results-section { margin: 4px 0 0; }
        .results-section-title {
            font-size: 11px;
            font-weight: 700;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            margin-bottom: 4px;
            padding-bottom: 2px;
            border-bottom: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
        }
        .results-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            background: #ffffff;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 0;
        }
        .results-table thead th {
            padding: 5px 6px;
            font-size: 9px;
            font-weight: 700;
            text-align: center;
            color: #ffffff;
            border: none;
        }
        .results-table thead th:first-child {
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            text-align: left;
        }
        .results-table thead th:nth-child(2) { background: #255FA5; }
        .results-table thead th:nth-child(3) { background: #FB0006; }
        .results-table thead th:nth-child(4) { background: #475569; }
        .results-table tbody th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-align: left;
            padding: 3px 6px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }
        .results-table tbody td {
            padding: 3px 6px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-weight: 600;
        }
        .results-table tbody td:nth-child(2) { background: rgba(37, 95, 165, 0.07); }
        .results-table tbody td:nth-child(3) { background: rgba(251, 0, 6, 0.05); }
        .results-table tbody tr:nth-child(even) th { background: #e8edf3; }
        .results-table tbody tr:nth-child(even) td:nth-child(2) { background: rgba(37, 95, 165, 0.11); }
        .results-table tbody tr:nth-child(even) td:nth-child(3) { background: rgba(251, 0, 6, 0.08); }
        .results-table tbody tr:last-child th,
        .results-table tbody tr:last-child td { border-bottom: none; }
        .table-footnotes {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 5px 8px;
            font-size: 8px;
            color: #64748b;
            line-height: 1.45;
        }
        .table-footnotes p { margin: 0; }

        /* === SALTOS DE PÁGINA === */
        .charts-section { page-break-before: always; }
        .page-letter {
            page-break-before: always;
            padding-top: 6px;
            padding-bottom: 42px;
        }
        .letter-patient-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 10px;
            margin: 8px 0 10px;
        }
        .letter-patient-table { width: 100%; border-collapse: collapse; }
        .letter-patient-table td {
            padding: 2px 6px;
            font-size: 10px;
            vertical-align: top;
        }
        .letter-title {
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            margin-bottom: 8px;
        }
        .letter-greeting {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .letter-body p {
            font-size: 11px;
            line-height: 1.45;
            margin: 0 0 8px;
            text-align: justify;
        }
        .letter-section-title {
            font-size: 12px;
            font-weight: 700;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            margin: 10px 0 4px;
        }
        .letter-elaboro {
            font-size: 10px;
            color: #64748b;
            margin: 12px 0 0;
        }
        .letter-signature-block {
            margin-top: 14px;
        }
        .letter-signature-block .signature {
            margin: 0 0 6px;
        }
        .letter-signature-block .signature img {
            max-width: 160px;
            max-height: 65px;
        }
        .letter-sign-atentamente {
            font-size: 11px;
            margin: 0 0 8px;
        }
        .letter-sign-name {
            font-size: 12px;
            font-weight: 700;
            margin: 0;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
        }
        .letter-sign-role {
            font-size: 11px;
            margin: 2px 0 0;
            color: #475569;
        }

        /* === GRÁFICAS COMPARATIVAS === */
        .charts-section { margin-top: 0; margin-bottom: 10px; }
        .charts-title {
            font-size: 13px;
            font-weight: 700;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            margin-bottom: 8px;
            border-bottom: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
            padding-bottom: 3px;
        }
        .charts-row { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .charts-row td { width: 50%; vertical-align: top; padding: 4px 8px; }
        .chart-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            background: #f8fafc;
            min-height: 125px;
        }
        .chart-box-title {
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            text-align: center;
            margin-bottom: 8px;
        }
        .bar-row { margin-bottom: 8px; }
        .bar-row-label {
            font-size: 9px;
            color: #64748b;
            margin-bottom: 2px;
        }
        .bar-row-track {
            width: 100%;
            height: 18px;
            background: #e2e8f0;
            border-radius: 3px;
            position: relative;
        }
        .bar-row-fill {
            height: 18px;
            border-radius: 3px;
            min-width: 2px;
        }
        .bar-primera { background: #255FA5; }
        .bar-segunda { background: #FB0006; }
        .bar-row-value {
            font-size: 10px;
            font-weight: 700;
            color: #334155;
            margin-top: 2px;
        }
        .chart-legend {
            margin-top: 6px;
            font-size: 9px;
            color: #64748b;
            text-align: center;
        }
        .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            margin-right: 3px;
            vertical-align: middle;
        }
        .legend-primera { background: #255FA5; }
        .legend-segunda { background: #FB0006; }

        /* Barras verticales */
        .charts-title-isq {
            margin-top: 18px;
            margin-bottom: 8px;
        }
        .vbar-chart-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 6px 6px;
            background: #f8fafc;
            margin-top: 0;
        }
        .vbar-chart { width: 100%; border-collapse: collapse; }
        .vbar-chart > tbody > tr > td {
            width: 25%;
            vertical-align: bottom;
            text-align: center;
            padding: 0 2px;
        }
        .vbar-chart-7 > tbody > tr > td {
            width: 14.28%;
            vertical-align: bottom;
            text-align: center;
            padding: 0 1px;
        }
        .vbar-chart-7 .vbar-label {
            font-size: 8px;
        }
        .vbar-chart-7 .vbar-val {
            font-size: 8.5px;
        }
        .vbar-group {
            width: 46px;
            border-collapse: collapse;
            margin: 0 auto;
            table-layout: fixed;
        }
        .vbar-group td {
            width: 23px;
            vertical-align: bottom;
            text-align: center;
            padding: 0;
        }
        .vbar-area { height: 105px; vertical-align: bottom; }
        .vbar-val {
            font-size: 9px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 2px;
            line-height: 1.1;
        }
        .vbar {
            width: 20px;
            margin: 0;
            border-radius: 3px 3px 0 0;
            min-height: 2px;
            display: inline-block;
        }
        .vbar.bar-primera { background: #255FA5; }
        .vbar.bar-segunda { background: #FB0006; }
        .vbar-label {
            font-size: 9px;
            font-weight: 600;
            color: #475569;
            padding-top: 5px;
            line-height: 1.2;
            text-align: center;
        }
        .vbar-sublabel {
            font-size: 8px;
            color: #94a3b8;
            padding-top: 4px;
        }

        /* Cuadro reducción riesgo CV */
        .riesgo-cv-highlight {
            margin-top: 16px;
            border: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
            border-radius: 10px;
            background: #f8fafc;
            text-align: center;
            padding: 20px 16px;
        }
        .riesgo-cv-label {
            font-size: 13px;
            font-weight: 700;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .riesgo-cv-value {
            font-size: 42px;
            font-weight: 700;
            color: #255FA5;
            line-height: 1;
            margin: 4px 0;
        }
        .riesgo-cv-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 8px;
        }
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
                    @if($clinica->email ?? null)
                        | {{ $clinica->email }}
                    @endif
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
                <td style="padding-left: 10px;">
                    <div class="header-title">Reporte Final de Rehabilitación Cardiaca</div>
                    <div class="header-subtitle">Programa de Rehabilitación Cardiaca</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Registro</div>
                        <div class="header-badge-value">#{{ $paciente->registro }}</div>
                    </div>
                    <div class="header-date">{{ date('d/m/Y', strtotime($data->fecha_inicio)) }}</div>
                </td>
            </tr>
        </table>
    </div>
    <!-- PATIENT INFO -->
    <div class="patient-card">
        <div class="patient-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
        <table class="patient-table">
            <tr>
                <td width="50%"><span class="patient-label">Edad:</span> <span class="patient-value">{{ $paciente->edad }} años</span></td>
                <td width="50%"><span class="patient-label">Enviado a:</span> <span class="patient-value">Dr(a). {{ $paciente->envio }}</span></td>
            </tr>
            <tr>
                <td><span class="patient-label">Fecha de Ingreso:</span> <span class="patient-value">{{ date('d/m/Y', strtotime($data->fecha_inicio)) }}</span></td>
                <td><span class="patient-label">Fecha de Egreso:</span> <span class="patient-value">{{ date('d/m/Y', strtotime($data->fecha_final)) }}</span></td>
            </tr>
            <tr>
                <td colspan="2"><span class="patient-label">Núm. de Sesiones:</span> <span class="patient-value">{{ $numSesiones ?? 'N/D' }}</span></td>
            </tr>
        </table>
        @if($paciente->diagnostico)
        <div class="patient-diagnosis">
            <span class="patient-diagnosis-label">Diagnóstico:</span> {{ $paciente->diagnostico }}
        </div>
        @endif
    </div>
  <main class="ma-t-0">
    <p class="page-one-intro">Estimado(a): <strong>Dr(a). {{ $paciente->envio }}</strong> — Por medio de este conducto me permito informarle de los pormenores del programa de Rehabilitación Cardiaca.</p>
    <div class="page-one-method">
      <div class="page-one-method-title">Metodología</div>
      <p>Al ingreso se realizó la estratificación de riesgo cardiovascular correspondiente y se comenzó con la rehabilitación cardiaca, consistente en sesiones ergométricas intercalando diversos grupos musculares y de forma progresiva.</p>
      <p>Las sesiones fueron controladas mediante supervisión intensiva de la tensión arterial y trazo electrocardiográfico continuos. Durante el proceso el paciente aprendió adecuadamente la graduación de la intensidad del ejercicio mediante la escala de percepción del esfuerzo (Borg).</p>
      <p>Durante las sesiones no presentó complicaciones y ningún evento adverso que consignar. Al egreso se realizó una prueba de esfuerzo submáxima para evaluación del acondicionamiento cardiovascular y físico.</p>
      <p><strong>Los resultados se muestran en la siguiente tabla:</strong></p>
    </div>
    <div class="results-section">
      <div class="results-section-title">Resultados comparativos — Primera vs Segunda prueba</div>
      <div class="results-table-wrap">
        <table class="results-table">
          <thead>
            <tr>
              <th scope="col">Rubro</th>
              <th scope="col">Primera Prueba</th>
              <th scope="col">Segunda Prueba</th>
              <th scope="col">Variación (%)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row">Fecha</th>
              <td>{{date('d/m/Y',strtotime($data->fecha_inicio ))}}</td>
              <td>{{date('d/m/Y',strtotime($data->fecha_final ))}}</td>
              <td>{{$data->fecha}}</td>
            </tr>
            <tr>
              <th scope="row">Fc basal (lpm)*</th>
              <td>{{$esfuerzoUno->fcBasal }}</td>
              <td>{{$esfuerzoDos->fcBasal}}</td>
              <td>{{round($data->fc_basal,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Doble Producto basal**</th>
              <td>{{$esfuerzoUno->dapBasal}}</td>
              <td>{{$esfuerzoDos->dapBasal}}</td>
              <td>{{round($data->doble_pr_bas,1)}}</td>
            </tr>
            <tr>
              <th scope="row">FC máxima</th>
              <td>{{$esfuerzoUno->fcMax}}</td>
              <td>{{$esfuerzoDos->fcMax}}</td>
              <td>{{round($data->fc_maxima,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Doble Producto máximo</th>
              <td>{{$esfuerzoUno->dpMax}}</td>
              <td>{{$esfuerzoDos->dpMax}}</td>
              <td>{{round($data->doble_pr_max,1)}}</td>
            </tr>
            <tr>
              <th scope="row">FC Borg 12</th>
              <td>{{$esfuerzoUno->fcBorg12}}</td>
              <td>{{$esfuerzoDos->fcBorg12}}</td>
              <td>{{round($data->fc_borg12,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Doble Producto Borg 12</th>
              <td>{{$esfuerzoUno->dpBorg12 }}</td>
              <td>{{$esfuerzoDos->dpBorg12}}</td>
              <td>{{round($data->doble_pr_b12,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Carga máxima (METs)</th>
              <td>{{round($esfuerzoUno->mets_max,1)}}</td>
              <td>{{ round($esfuerzoDos->mets_max,1)}}</td>
              <td>{{round($data->carga_max,1)}}</td>
            </tr>
            <tr>
              <th scope="row">% METs alcanzado</th>
              <td>{{round($esfuerzoUno->vo2_alcanzado,1)}}</td>
              <td>{{round($esfuerzoDos->vo2_alcanzado,1)}}</td>
              <td>{{round($data->mets_por,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Tiempo de ejercicio (min)^</th>
              <td>{{round($esfuerzoUno->tiempoEsfuerzo,1)}}</td>
              <td>{{round($esfuerzoDos->tiempoEsfuerzo,1)}}</td>
              <td>{{round($data->tiempo_ejer,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Recuperación de la FC 1'(lpm)</th>
              <td>{{$esfuerzoUno->fcmax_fc1er}}</td>
              <td>{{$esfuerzoDos->fcmax_fc1er}}</td>
              <td>{{round($data->recup_fc,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Umbral Isquémico (METs)</th>
              <td>{{round($esfuerzoUno->mets_U_isq,1)}}</td>
              <td>{{round($esfuerzoDos->mets_U_isq,1)}}</td>
              <td>{{round($data->umbral_isq,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Umbral Isquémico (FC)</th>
              <td>{{round($esfuerzoUno->fc_U_isq,1)}}</td>
              <td>{{ round($esfuerzoDos->fc_U_isq,1)}}</td>
              <td>{{round($data->umbral_isq_fc,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Máximo Desnivel ST</th>
              <td>{{round($esfuerzoUno->MaxInfra,1)}}</td>
              <td>{{round($esfuerzoDos->MaxInfra,1)}}</td>
              <td>{{round($data->max_des_st,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Índice TA en esfuerzo</th>
              <td>{{sprintf("%.2f", floor($esfuerzoUno->indice_tas  * 100) / 100);}}</td>
              <td>{{sprintf("%.2f", floor($esfuerzoDos->indice_tas  * 100) / 100);}}</td>
              <td>{{round($data->indice_ta_es,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Recuperación de la TAS 1/3</th>
              <td>{{sprintf("%.2f", floor($esfuerzoUno->recup_tas  * 100) / 100);}}</td>
              <td>{{sprintf("%.2f", floor($esfuerzoDos->recup_tas   * 100) / 100);}}</td>
              <td>{{round($data->recup_tas,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Resp. Cronotrópica (lpm/MET)</th>
              <td>{{round($esfuerzoUno->resp_crono,1)}}</td>
              <td>{{round($esfuerzoDos->resp_crono,1)}}</td>
              <td>{{round($data->resp_crono,1)}}</td>
            </tr>
            <tr>
              <th scope="row">IEM***</th>
              <td>{{round($esfuerzoUno->iem,1)}}</td>
              <td>{{round($esfuerzoDos->iem,1)}}</td>
              <td>{{round($data->iem,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Poder Cardiaco en ejercicio</th>
              <td>{{round($esfuerzoUno->pce)}}</td>
              <td>{{round($esfuerzoDos->pce)}}</td>
              <td>{{round($data->pod_car_eje,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Puntuación de Duke</th>
              <td>{{round($esfuerzoUno->duke,1)}}</td>
              <td>{{round($esfuerzoDos->duke,1)}}</td>
              <td>{{round($data->duke,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Puntuación de Veteranos</th>
              <td>{{round($esfuerzoUno->veteranos,1)}}</td>
              <td>{{round($esfuerzoDos->veteranos,1)}}</td>
              <td>{{round($data->veteranos,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Score de Angor</th>
              <td>{{round($esfuerzoUno->scoreAngina)}}</td>
              <td>{{round($esfuerzoDos->scoreAngina)}}</td>
              <td>{{round($data->score_ang,1)}}</td>
            </tr>
            <tr>
              <th scope="row">Ectopia Ventricular Frecuente</th>
              <td>{{$esfuerzoUno->ectopia_ventricular===1?"si":"no"}}</td>
              <td>{{$esfuerzoDos->ectopia_ventricular===1?"si":"no"}}</td>
              <td></td>
            </tr>
          </tbody>
        </table>
        <div class="table-footnotes">
          <p>* Frecuencia cardiaca (FC). &nbsp; ** Doble producto = (TA sist) × (FC). &nbsp; *** Índice de Eficiencia Miocárdica (IEM). &nbsp; ^ Tiempo de ejercicio corregido para protocolo de Bruce.</p>
        </div>
      </div>
    </div>

  @php
    $chartMets1 = round($esfuerzoUno->mets_max, 1);
    $chartMets2 = round($esfuerzoDos->mets_max, 1);
    $chartPct1 = round($esfuerzoUno->vo2_alcanzado, 1);
    $chartPct2 = round($esfuerzoDos->vo2_alcanzado, 1);
    $chartFcMax1 = (float) $esfuerzoUno->fcMax;
    $chartFcMax2 = (float) $esfuerzoDos->fcMax;
    $chartDpMax1 = (float) $esfuerzoUno->dpMax;
    $chartDpMax2 = (float) $esfuerzoDos->dpMax;

    $maxMetsChart = max($chartMets1, $chartMets2, 0.1);
    $maxPctChart = max($chartPct1, $chartPct2, 0.1);
    $maxFcMaxChart = max($chartFcMax1, $chartFcMax2, 0.1);
    $maxDpMaxChart = max($chartDpMax1, $chartDpMax2, 0.1);

    $barMets1 = round(($chartMets1 / $maxMetsChart) * 100, 1);
    $barMets2 = round(($chartMets2 / $maxMetsChart) * 100, 1);
    $barPct1 = round(($chartPct1 / $maxPctChart) * 100, 1);
    $barPct2 = round(($chartPct2 / $maxPctChart) * 100, 1);
    $barFcMax1 = round(($chartFcMax1 / $maxFcMaxChart) * 100, 1);
    $barFcMax2 = round(($chartFcMax2 / $maxFcMaxChart) * 100, 1);
    $barDpMax1 = round(($chartDpMax1 / $maxDpMaxChart) * 100, 1);
    $barDpMax2 = round(($chartDpMax2 / $maxDpMaxChart) * 100, 1);

    $isqMets1 = round($esfuerzoUno->mets_U_isq, 1);
    $isqMets2 = round($esfuerzoDos->mets_U_isq, 1);
    $isqFc1 = round($esfuerzoUno->fc_U_isq, 1);
    $isqFc2 = round($esfuerzoDos->fc_U_isq, 1);
    $maxSt1 = round($esfuerzoUno->MaxInfra, 1);
    $maxSt2 = round($esfuerzoDos->MaxInfra, 1);
    $angina1 = round($esfuerzoUno->scoreAngina);
    $angina2 = round($esfuerzoDos->scoreAngina);

    $vbarMaxH = 95;
    $maxIsqMets = max($isqMets1, $isqMets2, 0.1);
    $maxIsqFc = max($isqFc1, $isqFc2, 0.1);
    $maxStChart = max($maxSt1, $maxSt2, 0.1);
    $maxAngina = max($angina1, $angina2, 0.1);

    $vhIsqMets1 = round(($isqMets1 / $maxIsqMets) * $vbarMaxH);
    $vhIsqMets2 = round(($isqMets2 / $maxIsqMets) * $vbarMaxH);
    $vhIsqFc1 = round(($isqFc1 / $maxIsqFc) * $vbarMaxH);
    $vhIsqFc2 = round(($isqFc2 / $maxIsqFc) * $vbarMaxH);
    $vhMaxSt1 = round(($maxSt1 / $maxStChart) * $vbarMaxH);
    $vhMaxSt2 = round(($maxSt2 / $maxStChart) * $vbarMaxH);
    $vhAngina1 = round(($angina1 / $maxAngina) * $vbarMaxH);
    $vhAngina2 = round(($angina2 / $maxAngina) * $vbarMaxH);

    $indicesPronosticos = [
        ['label' => 'Resp. Cronotrópica', 'sub' => 'lpm/MET', 'v1' => round($esfuerzoUno->resp_crono, 1), 'v2' => round($esfuerzoDos->resp_crono, 1)],
        ['label' => 'Recup. FC 1 min', 'sub' => 'lpm', 'v1' => (float) $esfuerzoUno->fcmax_fc1er, 'v2' => (float) $esfuerzoDos->fcmax_fc1er],
        ['label' => 'Poder Cardiaco', 'sub' => 'ejercicio', 'v1' => round($esfuerzoUno->pce), 'v2' => round($esfuerzoDos->pce)],
        ['label' => 'Recup. TAS', 'sub' => '1/3', 'v1' => round(floor($esfuerzoUno->recup_tas * 100) / 100, 2), 'v2' => round(floor($esfuerzoDos->recup_tas * 100) / 100, 2)],
        ['label' => 'Índice TA', 'sub' => 'esfuerzo', 'v1' => round(floor($esfuerzoUno->indice_tas * 100) / 100, 2), 'v2' => round(floor($esfuerzoDos->indice_tas * 100) / 100, 2)],
        ['label' => 'Puntuación', 'sub' => 'Duke', 'v1' => round($esfuerzoUno->duke, 1), 'v2' => round($esfuerzoDos->duke, 1)],
        ['label' => 'Puntuación', 'sub' => 'Veteranos', 'v1' => round($esfuerzoUno->veteranos, 1), 'v2' => round($esfuerzoDos->veteranos, 1)],
    ];

    foreach ($indicesPronosticos as $idx => $item) {
        $maxVal = max($item['v1'], $item['v2'], 0.1);
        $indicesPronosticos[$idx]['h1'] = round(($item['v1'] / $maxVal) * $vbarMaxH);
        $indicesPronosticos[$idx]['h2'] = round(($item['v2'] / $maxVal) * $vbarMaxH);
    }

    $riesgoCvReduccion = sprintf('%.2f', floor(($esfuerzoDos->mets_max - $esfuerzoUno->mets_max) * 12));
  @endphp

  <div class="charts-section">
    <div class="charts-title"> Evaluación de la Adaptacion Cardiovascular al Ejercicio</div>

    <table class="charts-row">
      <tr>
        <td>
          <div class="chart-box">
            <div class="chart-box-title">Carga máxima (METs)</div>
            <div class="bar-row">
              <div class="bar-row-label">Primera prueba</div>
              <div class="bar-row-track">
                <div class="bar-row-fill bar-primera" style="width: {{ $barMets1 }}%;"></div>
              </div>
              <div class="bar-row-value">{{ $chartMets1 }} METs</div>
            </div>
            <div class="bar-row">
              <div class="bar-row-label">Segunda prueba</div>
              <div class="bar-row-track">
                <div class="bar-row-fill bar-segunda" style="width: {{ $barMets2 }}%;"></div>
              </div>
              <div class="bar-row-value">{{ $chartMets2 }} METs</div>
            </div>
          </div>
        </td>
        <td>
          <div class="chart-box">
            <div class="chart-box-title">% METs alcanzado</div>
            <div class="bar-row">
              <div class="bar-row-label">Primera prueba</div>
              <div class="bar-row-track">
                <div class="bar-row-fill bar-primera" style="width: {{ $barPct1 }}%;"></div>
              </div>
              <div class="bar-row-value">{{ $chartPct1 }}%</div>
            </div>
            <div class="bar-row">
              <div class="bar-row-label">Segunda prueba</div>
              <div class="bar-row-track">
                <div class="bar-row-fill bar-segunda" style="width: {{ $barPct2 }}%;"></div>
              </div>
              <div class="bar-row-value">{{ $chartPct2 }}%</div>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="chart-box">
            <div class="chart-box-title">Frecuencia cardiaca máxima (lpm)</div>
            <div class="bar-row">
              <div class="bar-row-label">Primera prueba</div>
              <div class="bar-row-track">
                <div class="bar-row-fill bar-primera" style="width: {{ $barFcMax1 }}%;"></div>
              </div>
              <div class="bar-row-value">{{ $chartFcMax1 }} lpm</div>
            </div>
            <div class="bar-row">
              <div class="bar-row-label">Segunda prueba</div>
              <div class="bar-row-track">
                <div class="bar-row-fill bar-segunda" style="width: {{ $barFcMax2 }}%;"></div>
              </div>
              <div class="bar-row-value">{{ $chartFcMax2 }} lpm</div>
            </div>
          </div>
        </td>
        <td>
          <div class="chart-box">
            <div class="chart-box-title">Doble producto máximo</div>
            <div class="bar-row">
              <div class="bar-row-label">Primera prueba</div>
              <div class="bar-row-track">
                <div class="bar-row-fill bar-primera" style="width: {{ $barDpMax1 }}%;"></div>
              </div>
              <div class="bar-row-value">{{ $chartDpMax1 }}</div>
            </div>
            <div class="bar-row">
              <div class="bar-row-label">Segunda prueba</div>
              <div class="bar-row-track">
                <div class="bar-row-fill bar-segunda" style="width: {{ $barDpMax2 }}%;"></div>
              </div>
              <div class="bar-row-value">{{ $chartDpMax2 }}</div>
            </div>
          </div>
        </td>
      </tr>
    </table>

    <div class="chart-legend">
      <span class="legend-dot legend-primera"></span> Primera prueba &nbsp;&nbsp;
      <span class="legend-dot legend-segunda"></span> Segunda prueba
    </div>

    <div class="charts-title charts-title-isq">Evaluación de Umbral Isquémico</div>
    <div class="vbar-chart-wrap">
      <table class="vbar-chart">
        <tr>
          <td>
            <table class="vbar-group">
              <tr>
                <td class="vbar-area">
                  <div class="vbar-val">{{ $isqMets1 }}</div>
                  <div class="vbar bar-primera" style="height: {{ $vhIsqMets1 }}px;"></div>
                </td>
                <td class="vbar-area">
                  <div class="vbar-val">{{ $isqMets2 }}</div>
                  <div class="vbar bar-segunda" style="height: {{ $vhIsqMets2 }}px;"></div>
                </td>
              </tr>
            </table>
            <div class="vbar-label">Umbral Isquémico<br>(METs)</div>
          </td>
          <td>
            <table class="vbar-group">
              <tr>
                <td class="vbar-area">
                  <div class="vbar-val">{{ $isqFc1 }}</div>
                  <div class="vbar bar-primera" style="height: {{ $vhIsqFc1 }}px;"></div>
                </td>
                <td class="vbar-area">
                  <div class="vbar-val">{{ $isqFc2 }}</div>
                  <div class="vbar bar-segunda" style="height: {{ $vhIsqFc2 }}px;"></div>
                </td>
              </tr>
            </table>
            <div class="vbar-label">Umbral Isquémico<br>(FC lpm)</div>
          </td>
          <td>
            <table class="vbar-group">
              <tr>
                <td class="vbar-area">
                  <div class="vbar-val">{{ $maxSt1 }}</div>
                  <div class="vbar bar-primera" style="height: {{ $vhMaxSt1 }}px;"></div>
                </td>
                <td class="vbar-area">
                  <div class="vbar-val">{{ $maxSt2 }}</div>
                  <div class="vbar bar-segunda" style="height: {{ $vhMaxSt2 }}px;"></div>
                </td>
              </tr>
            </table>
            <div class="vbar-label">Máximo Desnivel<br>ST (mm)</div>
          </td>
          <td>
            <table class="vbar-group">
              <tr>
                <td class="vbar-area">
                  <div class="vbar-val">{{ $angina1 }}</div>
                  <div class="vbar bar-primera" style="height: {{ $vhAngina1 }}px;"></div>
                </td>
                <td class="vbar-area">
                  <div class="vbar-val">{{ $angina2 }}</div>
                  <div class="vbar bar-segunda" style="height: {{ $vhAngina2 }}px;"></div>
                </td>
              </tr>
            </table>
            <div class="vbar-label">Score<br>Angina</div>
          </td>
        </tr>
      </table>
      <div class="vbar-sublabel text-ctr">
        <span class="legend-dot legend-primera"></span> 1ª prueba &nbsp;&nbsp;
        <span class="legend-dot legend-segunda"></span> 2ª prueba
      </div>
    </div>

    <div class="charts-title charts-title-isq"> Evaluación de Índices Pronósticos</div>
    <div class="vbar-chart-wrap">
      <table class="vbar-chart vbar-chart-7">
        <tr>
          @foreach($indicesPronosticos as $indice)
          <td>
            <table class="vbar-group">
              <tr>
                <td class="vbar-area">
                  <div class="vbar-val">{{ $indice['v1'] }}</div>
                  <div class="vbar bar-primera" style="height: {{ $indice['h1'] }}px;"></div>
                </td>
                <td class="vbar-area">
                  <div class="vbar-val">{{ $indice['v2'] }}</div>
                  <div class="vbar bar-segunda" style="height: {{ $indice['h2'] }}px;"></div>
                </td>
              </tr>
            </table>
            <div class="vbar-label">{{ $indice['label'] }}<br>({{ $indice['sub'] }})</div>
          </td>
          @endforeach
        </tr>
      </table>
      <div class="vbar-sublabel text-ctr">
        <span class="legend-dot legend-primera"></span> 1ª prueba &nbsp;&nbsp;
        <span class="legend-dot legend-segunda"></span> 2ª prueba
      </div>
    </div>

    <div class="riesgo-cv-highlight">
      <div class="riesgo-cv-label">Reducción de Riesgo Cardiovascular</div>
      <div class="riesgo-cv-value">{{ $riesgoCvReduccion }}%</div>
      <div class="riesgo-cv-sub">Estimado comparativo entre la primera y segunda prueba de esfuerzo (origen cardiovascular)</div>
    </div>
  </div>

  <div class="page-letter">
    <div class="letter-title">Reporte Final del Programa Rehabilitación Cardiaca</div>
    @if(!empty($clinicaLogo))
    <div class="logo-container" style="margin-bottom: 6px;"><img src="{{ $clinicaLogo }}" alt="logo clínica"></div>
    @endif
    <p class="letter-greeting">Estimado(a): <span style="font-weight:normal;">Dr(a). {{ $paciente->envio }}</span></p>

    <div class="letter-patient-card">
      <table class="letter-patient-table">
        <tr>
          <td width="50%"><strong>Nombre:</strong> {{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</td>
          <td width="50%" style="text-align:right;"><strong>Fecha de Ingreso:</strong> {{ date('d/m/Y', strtotime($data->fecha_inicio)) }}</td>
        </tr>
        <tr>
          <td><strong>Edad:</strong> {{ $paciente->edad }} años</td>
          <td style="text-align:right;"><strong>Fecha de Egreso:</strong> {{ date('d/m/Y', strtotime($data->fecha_final)) }}</td>
        </tr>
        <tr>
          <td colspan="2"><strong>Diagnóstico:</strong> {{ $paciente->diagnostico }}</td>
        </tr>
        <tr>
          <td colspan="2"><strong>Núm. de Sesiones:</strong> {{ $numSesiones ?? 'N/D' }}</td>
        </tr>
      </table>
    </div>

    <div class="letter-body">
      <p>Como parte del programa y con el fin de supervisar esta etapa promoviendo el apego al método, se llevará la asesoría intermitente de la realización de la terapia ergométrica del paciente por medio de la programación de refuerzos (en los siguientes seis meses) para así disminuir la incidencia de deserción del paciente durante la parte domiciliaria del programa. Esta supervisión se realizará siempre en estrecha colaboración con su médico tratante.</p>

      <div class="letter-section-title">Conclusiones</div>
      <p>Después de un periodo inicial de acondicionamiento intensivo la evolución del paciente es satisfactoria, con un aumento importante de su capacidad física y de su tolerancia al ejercicio, así como una reducción de Riesgo CV del {{ $riesgoCvReduccion }}% de origen cardiovascular. Aunque el logro es muy importante todavía se espera que mejore aún más la tolerancia al esfuerzo con el transcurrir de los meses, con el beneficio que conlleva el ejercicio; ya por demás demostrado en los programas de Rehabilitación Cardiaca.</p>

      <p>Agradeciendo su confianza y preferencia. Aprovecho para enviarle un cordial saludo y quedo a sus órdenes.</p>
    </div>

    <p class="letter-elaboro"><strong>Elaboró:</strong>
      @if(isset($autor) && $autor)
        {{ $autor->nombre_completo }}
        @if($autor->cedula)
          | Cédula: {{ $autor->cedula }}
        @endif
      @else
        {{ $user->nombre_con_titulo }}
      @endif
    </p>

    @if(isset($esAutor) && $esAutor && isset($firmaBase64) && $firmaBase64)
    <div class="letter-signature-block">
      <p class="letter-sign-atentamente">Atentamente,</p>
      <div class="signature">
        <img src="{{ $firmaBase64 }}" alt="Firma Digital">
      </div>
      <p class="letter-sign-name">{{ $user->nombre_con_titulo }}</p>
      <p class="letter-sign-role">Rehabilitación Cardiaca</p>
    </div>
    @endif
  </div>
  </main>
  </div><!-- End content-wrapper -->
  </body>
</html>