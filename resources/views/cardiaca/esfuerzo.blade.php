
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reporte de Prueba Ergométrica</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #1e293b;
            background: #ffffff;
            padding: 10px 20px;
        }

        /* === HEADER === */
        .header {
            width: 100%;
            background: #0A1628;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 8px 12px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .header-logo-cell {
            width: 60px;
            padding-right: 12px !important;
        }

        .header-logo {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 6px;
            padding: 5px;
            text-align: center;
        }

        .header-logo img {
            max-height: 35px;
            max-width: 35px;
        }

        .header-title {
            font-size: 16px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            font-size: 9px;
            color: #94a3b8;
        }

        .header-meta-cell {
            text-align: right;
            width: 120px;
        }

        .header-badge {
            background: rgba(255,255,255,0.15);
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 4px;
        }

        .header-badge-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
        }

        .header-badge-value {
            font-size: 12px;
            font-weight: 700;
            color: white;
        }

        .header-date {
            font-size: 9px;
            color: #94a3b8;
        }

        /* === PATIENT INFO === */
        .patient-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }

        .patient-table {
            width: 100%;
            border-collapse: collapse;
        }

        .patient-table td {
            padding: 2px 6px;
            font-size: 10px;
        }

        .patient-name {
            font-size: 13px;
            font-weight: 700;
            color: #0A1628;
            margin-bottom: 6px;
        }

        .patient-label {
            color: #64748b;
            font-size: 9px;
        }

        .patient-value {
            font-weight: 600;
            color: #334155;
        }

        .patient-diagnosis {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
        }

        .patient-diagnosis-label {
            font-size: 9px;
            color: #64748b;
            font-weight: 600;
        }

        /* === PAGE FOOTER === */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 6px 20px;
            background: white;
            border-top: 2px solid #0A1628;
            font-size: 9px;
        }

        .page-footer-table {
            width: 100%;
        }

        .page-footer .clinic-name {
            font-weight: 700;
            color: #ef4444;
        }

        .page-footer .clinic-contact {
            text-align: right;
            color: #64748b;
        }

        .content-wrapper {
            padding-bottom: 35px;
        }

        /* === EXISTING STYLES === */
        .tabla {
            font-size: 8.5px;
            margin-bottom: 0;
            width: 100%;
        }
        .f-10 { font-size: 10px; }
        .f-15 { font-size: 15px; }
        .f-7 { font-size: 7px; }
        .paciente { font-size: 11px; }
        .text-right { text-align: right; }
        .f-bold { font-weight: bold; }
        .f-normal { font-weight: normal; }
        .text-lft { text-align: left; }
        .text-ctr { text-align: center; }
        .border-t { border: 1px solid black; }
        .border-l { border-left: 1px solid black; }
        .border-r { border-right: 1px solid black; }
        .border-b { border-bottom: 1px solid black; }
        .backgr-black { background-color: #000; color: white; }
        .bck-gray { background-color: #DDDEE1; padding: 4px 8px; }
        .ma-t-0 { margin-top: 0px; }
        .m-t-2 { margin-top: 2px; }
        .m-t-3 { margin-top: 0; }
        .m-t-07 { margin-top: 2px; }
        .m-t-0 { margin-top: 2px; }
        .mt-0 { margin-top: 0; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .ml-1 { margin-left: 4px; }
        .ml-2 { margin-left: 8px; }
        .ml-3 { margin-left: 12px; }
        .ml-5 { margin-left: 20px; }
        .mr-2 { margin-right: 8px; }
        
        .h5 { font-size: 12px; font-weight: 700; }

        .container-g { width: 100%; }
        .table-container-g { width: 40%; float: right; }
        .text-container-g { width: 50%; float: left; }

        .contenedor {
            position: relative;
            margin-bottom: 4px;
            margin-top: 8px;
        }
        
        .titulo {
            display: inline-block;
            position: relative;
            z-index: 1;
            background: white;
            padding-right: 8px;
        }

        .linea {
            position: absolute;
            left: 4rem;
            right: 0;
            top: 0.5rem;
            border-bottom: 2px solid #0A1628;
            z-index: 0;
        }
        .linea-des {
            position: absolute;
            left: 6.2rem;
            right: 0;
            top: 0.5rem;
            border-bottom: 2px solid #0A1628;
            z-index: 0;
        }
        .linea-med {
            position: absolute;
            left: 16rem;
            right: 0;
            top: 0.5rem;
            border-bottom: 2px solid #0A1628;
            z-index: 0;
        }
        .linea-is {
            position: absolute;
            left: 5rem;
            right: 0;
            top: 0.5rem;
            border-bottom: 2px solid #0A1628;
            z-index: 0;
        }
        .linea-ar {
            position: absolute;
            left: 5.5rem;
            right: 0;
            top: 0.5rem;
            border-bottom: 2px solid #0A1628;
            z-index: 0;
        }
        .linea-pu {
            position: absolute;
            left: 7.2rem;
            right: 0;
            top: 0.5rem;
            border-bottom: 2px solid #0A1628;
            z-index: 0;
        }
        .linea-t {
            position: absolute;
            left: 0;
            right: 0;
            border-bottom: 2px solid #0A1628;
            z-index: 0;
        }
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
                    <div class="header-title">Prueba Ergométrica {{ isset($data->tipo_esfuerzo) && $data->tipo_esfuerzo === 'pulmonar' ? 'Pulmonar' : 'Cardíaca' }}</div>
                    <div class="header-subtitle">Reporte de evaluación funcional</div>
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

    <!-- PATIENT INFO -->
    <div class="patient-card">
        <div class="patient-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
        <table class="patient-table">
            <tr>
                <td><span class="patient-label">Peso:</span> <span class="patient-value">{{ $paciente->peso }} kg</span></td>
                <td><span class="patient-label">Talla:</span> <span class="patient-value">{{ $paciente->talla }} cm</span></td>
                <td><span class="patient-label">Edad:</span> <span class="patient-value">{{ $paciente->edad }} años</span></td>
                <td><span class="patient-label">IMC:</span> <span class="patient-value">{{ round($paciente->imc, 2) }}</span></td>
                <td><span class="patient-label">Género:</span> <span class="patient-value">{{ $paciente->genero == 1 ? 'Masculino' : 'Femenino' }}</span></td>
            </tr>
        </table>
        @if($paciente->medicamentos)
        <div class="patient-diagnosis">
            <span class="patient-diagnosis-label">Medicamentos:</span> {{ $paciente->medicamentos }}
        </div>
        @endif
        @if($paciente->diagnostico)
        <div class="patient-diagnosis">
            <span class="patient-diagnosis-label">Diagnóstico:</span> {{ $paciente->diagnostico }}
        </div>
        @endif
    </div>

    <main class="ma-t-0">
      <div class="paciente ma-t-0-0">
        <div class="contenedor ">
          <h2 class="h5 titulo">Prueba</h2>
          <div class="linea"></div>
        </div>
          <p  class="f-bold mb-0 m-t-2">Banda: <span class="f-normal">@if($data->banda===1) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;" class="font-light"> @else  <img src="img/x-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @endif</span>
          <span class="f-bold ml-2">  Cicloergómetro : <span class="f-normal">@if($data->ciclo===1) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;color:green;" class="font-light"> @else <img src="img/x-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @endif</span></span> <span class=f-bold"">  VO2(directo): <span  class="f-normal">@if($data->medicionGases===1) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;color:green;" class="font-light"> @else <img src="img/x-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @endif</span></span>
          <span class="f-bold ml-2">  Bruce: <span  class="f-normal">@if($data->bruce===1) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;color:green;" class="font-light"> @else <img src="img/x-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @endif</span></span> <span class=f-bold"">  Balke: <span  class="f-normal">@if($data->balke===1) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;color:green;" class="font-light"> @else <img src="img/x-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @endif</span></span>@if(isset($data->tipo_esfuerzo) && $data->tipo_esfuerzo === 'pulmonar') <span class="f-bold ml-2">  Naughton: <span  class="f-normal">@if(isset($data->naughton) && $data->naughton===1) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;color:green;" class="font-light"> @else <img src="img/x-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @endif</span></span>@endif
          <span class="f-bold ml-2">  Prueba Submáxima: <span  class="f-normal">@if($data->pba_submax===1) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;color:green;" class="font-light"> @else <img src="img/x-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @endif</span></span></p> <p class="mt-0 mb-0">  <span class="f-bold ">  1a vez: <span  class="f-normal">@if($data->pruebaIngreso===1) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;color:green;" class="font-light"> @else <img src="img/x-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @endif</span></span>
          <span class="f-bold ml-5">  Fase II: <span  class="f-normal">@if($data->pruebaFinFase2===1) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;color:green;" class="font-light"> @else <img src="img/x-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @endif</span></span>  <span class="f-bold ml-5">  Fase III: <span  class="f-normal">@if($data->pruebaFinFase3===1) <img src="img/check-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @else <img src="img/x-solid.svg" alt="" style="height: 12px ;margin-top:1px;color:green;" class="font-light"> @endif</span></span></p>
          <p class="mt-0 mb-0"> <span class="f-bold">  FCmax(teórica): <span  class="f-normal">{{$data->fc_max_calc}}</span></span>  <span class="f-bold ml-5">  FC(85%): <span  class="f-normal">{{round($data->fc_85)}}</span></span>
          <span class="f-bold ml-5">  % FCmax alcanzado: <span  class="f-normal">{{round($data->fc_max_alcanzado)}}</span></span></p>
      </div>
      <table class="tabla text-lft border-t table-striped text-center mt-2">
        <thead class="border-t">
          <tr>
            <th scope="col border-t">Etapa</th>
            <th scope="col border-t">METS</th>
            <th scope="col border-t">FC</th>
            <th scope="col border-t">TAS</th>
            <th scope="col border-t">TAD</th>
            <th scope="col border-t">Borg</th>
            <th scope="col border-t">Doble Producto</th>
          </tr>
        </thead >
        <tbody class="text-lft">
          <tr>
            <td scope=" border-b">Basal</td>
            <td class="border-t border-r text-ctr">1.0</td>
            <td class="border-t text-ctr">{{$data->fcBasal}}</td>
            <td class="border-t text-ctr">{{$data->tasBasal}}</td>
            <td class="border-t text-ctr">{{$data->tadBasal}}</td>
            <td class="border-t text-ctr">-</td>
            <td class="text-ctr border-t">{{$data->dapBasal}}</td>
          </tr>
          <tr>
            <td scope="  border-b">Borg 12</td>
            <td class="border-t text-ctr">{{round($data->mets_borg_12,1)}}</td>
            <td class="border-t text-ctr">{{$data->fcBorg12}}</td>
            <td class="border-t text-ctr">{{$data->tasBorg12}}</td>
            <td class="border-t text-ctr">{{$data->tadBorg12}}</td>
            <td class="border-t text-ctr">-</td>
            <td class="border-t text-ctr">{{$data->dpBorg12}}</td>
          </tr>
          <tr>
            <td scope=" border-b">Max. Esf.3</td>
            <td class="border-t text-ctr">{{round($data->mets_max,1)}}</td>
            <td class="border-t text-ctr">{{$data->fcMax}}</td>
            <td class="border-t text-ctr">{{$data->tasMax}}</td>
            <td class="border-t text-ctr">{{$data->tadMax}}</td>
            <td class="border-t text-ctr">{{$data->borgMax}}</td>
            <td class="border-t text-ctr">{{$data->dpMax}}</td>
          </tr>
          <tr>
            <th scope=" ">Recuperación</th>
            <td class="backgr-black"></td>
            <td class="backgr-black"></td>
            <td class="backgr-black"></td>
            <td class="backgr-black"></td>
            <td class="backgr-black"></td>
            <td class="backgr-black"></td>
          </tr>
          <tr>
            <td scope="row border-b">1er min</td>
            <td class="border-t text-ctr">-</td>
            <td class="border-t text-ctr">{{$data->fc_1er_min}}</td>
            <td class="border-t text-ctr">{{$data->tas_1er_min}}</td>
            <td class="border-t text-ctr">{{$data->tad_1er_min}}</td>
            <td class="border-t text-ctr">{{$data->borg_1er_min}}</td>
            <td class="border-t text-ctr">{{$data->fc_1er_min*$data->tas_1er_min}}</td>
          </tr>
          <tr>
            <td scope="row">3er min</td>
            <td class="border-t text-ctr">-</td>
            <td class="border-t text-ctr">{{$data->fc_3er_min}}</td>
            <td class="border-t text-ctr">{{$data->tas_3er_min}}</td>
            <td class="border-t text-ctr">{{$data->tad_3er_min}}</td>
            <td class="border-t text-ctr">{{$data->borg_3er_min}}</td>
            <td class="border-t text-ctr">{{$data->fc_3er_min*$data->tas_3er_min}}</td>
          </tr>
          <tr>
            <td scope="row">5to min</td>
            <td class="border-t text-ctr">-</td>
            <td class="border-t text-ctr">{{$data->fc_5to_min}}</td>
            <td class="border-t text-ctr">{{$data->tas_5to_min}}</td>
            <td class="border-t text-ctr">{{$data->tad_5to_min}}</td>
            <td class="border-t text-ctr">-</td>
            <td class="border-t text-ctr">{{$data->fc_5to_min*$data->tas_5to_min}}</td>
          </tr>
          <tr>
            <td scope="row">8vo min</td>
            <td class="border-t text-ctr">-</td>
            <td class="border-t text-ctr">{{$data->fc_8vo_min}}</td>
            <td class="border-t text-ctr">{{$data->tas_8vo_min}}</td>
            <td class="border-t text-ctr">{{$data->tad_8vo_min}}</td>
            <td class="border-t text-ctr">-</td>
            <td class="border-t text-ctr">{{$data->fc_8vo_min*$data->tas_8vo_min}}</td>
          </tr>
          <tr>
            <th scope="row">Umbral Isq4</th>
            <td class="border-t text-ctr">{{round($data->mets_banda_U_isq,1)}}</td>
            <td class="border-t text-ctr">{{$data->fc_U_isq}}</td>
            <td class="border-t text-ctr">{{$data->tas_U_isq}}</td>
            <td class="border-l border-r text-ctr">{{$data->tad_U_isq}}</td>
            <td class="border-l border-r text-ctr">{{$data->borg_U_isq}}</td>
            <td class="border-l border-r text-ctr">{{$data->fc_U_isq*$data->tas_U_isq}}</td>
          </tr>
        </tbody>
    </table>
    <div class="bck-gray">
    <div class="paciente mt-1">
      <div class="contenedor ">
        <h2 class="h5 titulo">Desempeño</h2>
        <div class="linea-des"></div>
      </div>
        <p  class="f-bold mb-0 m-t-2">Tiempo de esfuerzo: <span class="f-normal">{{ round($data->tiempoEsfuerzo,2)}}  min (tiempo calculado correspondiente a protocolo de @if(isset($data->tipo_esfuerzo) && $data->tipo_esfuerzo == 'pulmonar' && isset($data->naughton) && $data->naughton == 1) Naughton @elseif($data->bruce == 1) Bruce @elseif($data->balke == 1) Balke @else Bruce @endif) </span>
      <span class="f-bold">  Suspensión de la prueba : <span class="f-normal">{{$data->motivoSuspension}}</span></span></p> <p class="mt-0 mb-0"> <span class="f-bold">  METs Teórico: <span  class="f-normal">{{round($data->mets_teorico_general,2)}}</span></span>
      <span class="f-bold ml-3">  %METS max alcanzado: <span  class="f-normal">@if($data->medicionGases===1 && $data->vo2_max_percent !== null){{ round($data->vo2_max_percent, 2) }}@else{{ $data->mets_teorico_general != 0 ? round($data->mets_max/$data->mets_teorico_general*100,2) : '—' }}@endif</span></span> <span class="f-bold ml-3">  R. Pres: <span  class="f-normal">{{round($data->resp_presora,2)}}</span></span>
      <span class="f-bold ml-3">  MVo2(METs): <span  class="f-normal">{{round($data->mvo2/3.5*0.1,2)}}</span></span></p> <p class="mt-0 mb-0">  <span class="f-bold">  R. Cron: <span  class="f-normal">{{round($data->resp_crono,2)}}</span></span>
      <span class="f-bold ml-3">  TASmax/TASbasal: <span  class="f-normal">{{sprintf("%.2f", floor($data->indice_tas * 100) / 100);}}</span></span>  <span class="f-bold ml-3">  IEM: <span  class="f-normal">{{sprintf("%.2f", floor($data->iem * 100) / 100);}}</span></span>
      <span class="f-bold ml-3">  Recup. FC al 1er min (lpm): <span  class="f-normal">{{$data->fcmax_fc1er}}</span></span>  <span class="f-bold ml-3">  Rec TAS (3/1): <span  class="f-normal">{{ $data->tas_1er_min != 0 ? sprintf("%.2f", floor(($data->tas_3er_min/$data->tas_1er_min) * 100) / 100) : '—' }}</span></span>
      <span class="f-bold ml-5">  PCE (mmHg%): <span  class="f-normal">{{round($data->pce)}}</span></span></p>
  </div>
</div>
  <div class="paciente mt-2">
    <div class="contenedor ">
      <h2 class="h5 titulo">Medición de Gases Espirados</h2>
      <div class="linea-med"></div>
    </div>
    <p class="f-bold m-t-2">VO2max (mlO2/Kg/min): <span class="f-normal">{{ round($data->vo2_max_gases,2)}}</span>
    <span class="f-bold ml-2">VO2pico (mlO2/Kg/min): <span class="f-normal">{{round($data->vo2_pico_gases,2)}}</span></span>
    <span class="f-bold ml-2">R/Q(max.esf): <span class="f-normal">{{round($data->r_qmax,2)}}</span></span>
    <span class="f-bold ml-2">Umbral A/An(mlO2/Kg/min): <span class="f-normal">{{$data->umbral_aeer_anaer==null?0:$data->umbral_aeer_anaer}}</span></span>
    <span class="f-bold ml-2">%PO 2 teórico: <span class="f-normal">{{$data->po2_teor==null?0:$data->po2_teor}}</span></span></p>
  </div>

  <div class="bck-gray">
    <div class="contenedor">
      <h2 class="h5 titulo">Isquemia</h2>
      <div class="linea-is"></div>
    </div>
    <p class="f-bold mb-1">Indice Angina: <span class="f-normal">{{$data->scoreAngina}}</span>
    <span class="f-bold ml-3">Depresión max ST (mm): <span class="f-normal">{{$data->MaxInfra}}</span></span></p>
  </div>

  <div class="paciente mt-1">
    <p class="f-bold">Tipo de cambio: <span class="f-normal">{{$data->tipoCambioElectrico}}</span></p>
    <div class="contenedor">
      <h2 class="h5 titulo">Arritmias</h2>
      <div class="linea-ar"></div>
    </div>
    <p class="f-bold">Arritmias: <span class="f-normal">{{ $data->tipoArritmias}}</span></p>
  </div>

  <div class="bck-gray">
    <div class="contenedor">
      <h2 class="h5 titulo">Puntuaciones</h2>
      <div class="linea-pu"></div>
    </div>
    <p class="f-bold mb-0">Duke: <span class="f-normal">{{ round($data->duke,2)}}</span>
    <span class="f-bold ml-3">Veteranos (VA): <span class="f-normal">{{round($data->veteranos,2)}}</span></span></p>
  </div>

  <div class="contenedor">
    <div class="linea-t"></div>
  </div>

  <div class="paciente mt-1 mb-1">
    <p  class="f-bold">Conclusiones: <span class="f-normal">{{ $data->conclusiones}}</span></p> 
    <p class="m-t-0 mb-0"><span class="f-bold">  Riesgo general de la prueba: <span class="f-normal">{{$data->riesgo}}</span></span></p>
    {{-- Siempre mostrar quién elaboró --}}
    @if(isset($autor) && $autor)
    <p class="mt-0 mb-0"><span class="f-bold">Elaboró: <span class="f-normal">{{ $autor->nombre_completo }}</span></span></p>
    @if($autor->cedula)
    <p class="mt-0 mb-0" style="font-size: 9px; color: #64748b;">Cédula Profesional: {{ $autor->cedula }}</p>
    @endif
    @endif
  </div>
  {{-- Solo mostrar firma si es el autor --}}
  @if(isset($esAutor) && $esAutor && isset($firmaBase64) && $firmaBase64)
  <div style="margin-top: 8px; text-align: center; page-break-inside: avoid;">
    <img src="{{ $firmaBase64 }}" alt="Firma" style="height: 50px; width: auto;"><br>
    <div style="border-top: 1px solid #333; width: 150px; margin: 2px auto 0 auto;"></div>
    <span style="font-size: 9px;">{{ $autor->nombre_completo ?? $user->nombre_con_titulo }}</span>
  </div>
  @endif
  <div class="contenedor">
    <h2 class="h5 titulo"></h2>
    <div class="linea-t"></div>
  </div>
  <div class="paciente mt-1">
    <p  class="f-bold mt-0 mb-0">Para diagnóstico de cardiopatía isquémica:
      <span class="f-bold">  Confusor: <span class="f-normal">{{$data->confusor}}</span></span>
      <span class="f-bold">  Prob pre-prueba: <span class="f-normal">{{$data->prevalencia*100}}%</span></span>
      <span class="f-bold">  Sensibilidad: <span class="f-normal">{{$data->sensibilidad*100}}%</span></span></p>
      <p><span class="f-bold  mt-0 mb-0">  Especificidad: <span class="f-normal">{{$data->especificidad*100}}%</span></span>
      <span class="f-bold">  V.Predictivo: <span class="f-normal">{{round($data->vpp*100)}}%</span></span></p>
  </div>

    </main>
    </div><!-- End content-wrapper -->
  </body>
</html>