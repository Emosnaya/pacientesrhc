
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- Bootstrap CSS -->
    <style>
        /* Estilo para la línea de firma */
        .signature {
        text-align: center;
        width: 100%;
        margin-top: 5rem
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
          font-size: 15px;
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
        width: 100%;
    }
    .table-container-g {
        width: 40%;
        float: right;
    }
    .text-container-g {
        width: 50%;
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
        top: 0;
        left: 0;
    }
    .txt{
      width: 20%;
      margin-left: 1.5rem;
      margin-right: 5rem;
    }
    .ma-t-0{
      margin-top: 0px;
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
  .m-t-2{
    margin-top: -1rem;
  }
  .m-t-3{
    margin-top: -2rem;
  }
  .m-t-0{
    margin-top: -0.7rem;
  }
  
  .linea {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 4rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.5rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-des {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 6.2rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.5rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-med {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 16rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.5rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-is {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 5rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.5rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-ar {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 5.5rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.5rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-pu {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 7.2rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.5rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-t {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 0; /* Comienza desde el borde izquierdo del contenedor */
    right: 0; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
    </style>
  </head>
  <body>
    <header class="mb-0">
      <div class="paciente ma-t-0 mb-0">
        <p class="f-bold f-15 text-center mb-0 mt-0">Reporte de Prueba Ergométrica</p>
        <img src="img/logo.png" alt="cercap logo" style="height: 90px" class="">
        <div class="medio">
          <p class=" texto-izquierda mb-0 f-bold">Fecha: {{ $data->fecha}} </p> <span class="ml-5 text-right texto-derecha f-bold">Registro: {{$paciente->registro}}</span>
        </div>
        <br>
          <p  class="f-bold mt-2 mb-0 f-15">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre}}</span></p>
          <p class="mt-0 mb-0"><span class="f-bold">  Peso : <span class="f-normal">{{$paciente->peso}}</span></span> <span class=f-bold"">  Talla: <span  class="f-normal">{{$paciente->talla}}</span></span>
          <span class="f-bold ml-3">  Edad: <span  class="f-normal">{{$paciente->edad}}</span></span> <span class="f-bold">  IMC: <span  class="f-normal">{{round($paciente->imc,2)}}</span></span>
          <span class="f-bold ml-3">  Género: <span  class="f-normal">{{($paciente->genero==1?"Hombre":"Mujer")}}</span></span></p>
          <p class="f-bold mt-0 mb-1">  Diagnostico: <span  class="f-normal">{{$paciente->diagnostico}}</span>  <span class="f-bold ml-3">  Medicamentos: <span  class="f-normal">{{$paciente->medicamentos}}</span></span></p>
      </div>
    </header>
    <main class="ma-t-0">
      <div class="paciente ma-t-0-0">
        <div class="contenedor ">
          <h2 class="h5 titulo">Prueba</h2>
          <div class="linea"></div>
        </div>
          <p  class="f-bold mb-0 m-t-2">Banda: <span class="f-normal">{{ $data->banda}}</span>
          <span class="f-bold ml-2">  Cicloergómetro : <span class="f-normal">{{$data->ciclo}}</span></span> <span class=f-bold"">  VO2(directo): <span  class="f-normal">{{$data->medicionGases}}</span></span>
          <span class="f-bold ml-2">  Bruce: <span  class="f-normal">{{$data->bruce}}</span></span> <span class=f-bold"">  Balke: <span  class="f-normal">{{$data->balke}}</span></span>
          <span class="f-bold ml-2">  Prueba Submáxima: <span  class="f-normal">{{$data->pba_submax}}</span></span></p> <p class="mt-0 mb-0">  <span class="f-bold ">  1a vez: <span  class="f-normal">{{$data->pruebaIngreso}}</span></span>
          <span class="f-bold ml-5">  Fase II: <span  class="f-normal">{{$data->pruebaFinFase2}}</span></span>  <span class="f-bold ml-5">  Fase III: <span  class="f-normal">{{$data->pruebaFinFase3}}</span></span></p>
          <p class="mt-0 mb-0"> <span class="f-bold">  FCmax(teórica): <span  class="f-normal">{{$data->fc_max_calc}}</span></span>  <span class="f-bold ml-5">  FC(85%): <span  class="f-normal">{{$data->fc_85}}</span></span>
          <span class="f-bold ml-5">  % FCmax alcanzado: <span  class="f-normal">{{round($data->fc_max_alcanzado)}}</span></span></p>
      </div>
      <table class="tabla text-lft border-t text-center mt-2">
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
            <td class="border-l border-r text-ctr"></td>
            <td class="border-l border-r text-ctr"></td>
            <td class="border-l border-r text-ctr"></td>
            <td class="border-l border-r text-ctr"></td>
            <td class="border-l border-r text-ctr"></td>
            <td class="border-l border-r border-b text-ctr"></td>
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
            <td scope="row">6to min</td>
            <td class="border-t text-ctr">-</td>
            <td class="border-t text-ctr">{{$data->fc_8vo_min}}</td>
            <td class="border-t text-ctr">{{$data->tas_8vo_min}}</td>
            <td class="border-t text-ctr">{{$data->tad_8vo_min}}</td>
            <td class="border-t text-ctr">-</td>
            <td class="border-t text-ctr">{{$data->fc_8vo_min*$data->tas_8vo_min}}</td>
          </tr>
          <tr>
            <th scope="row">Umbral Isq4</th>
            <td class="border-t text-ctr">{{$data->mets_banda_U_isq}}</td>
            <td class="border-t text-ctr">{{$data->fc_U_isq}}</td>
            <td class="border-t text-ctr">{{$data->tas_U_isq}}</td>
            <td class="border-l border-r text-ctr">{{$data->tad_U_isq}}</td>
            <td class="border-l border-r text-ctr">{{$data->borg_U_isq}}</td>
            <td class="border-l border-r text-ctr">{{$data->fc_U_isq*$data->tas_U_isq}}</td>
          </tr>
        </tbody>
    </table>
    <div class="paciente mt-1">
      <div class="contenedor ">
        <h2 class="h5 titulo">Desempeño</h2>
        <div class="linea-des"></div>
      </div>
      <p  class="f-bold mb-0 m-t-2">Tiempo de esfuerzo: <span class="f-normal">{{ round($data->tiempoEsfuerzo,2)}}  min (tiempo calculado correspondiente a protocolo de Bruce) </span>
      <span class="f-bold">  Suspensión de la prueba : <span class="f-normal">{{$data->motivoSuspension}}</span></span></p> <p class="mt-0 mb-0"> <span class="f-bold">  METs Teórico: <span  class="f-normal">{{round($data->mets_teorico_general,2)}}</span></span>
      <span class="f-bold ml-3">  %METS max alcanzado: <span  class="f-normal">{{round($data->mets_max/$data->mets_teorico_general*100,2)}}</span></span> <span class="f-bold ml-3">  R. Pres: <span  class="f-normal">{{round($data->resp_presor,2)}}</span></span>
      <span class="f-bold ml-3">  MVo2(METs): <span  class="f-normal">{{$data->mvo2/3.5*0.1}}</span></span></p> <p class="mt-0 mb-0">  <span class="f-bold">  R. Cron: <span  class="f-normal">{{round($data->resp_crono,2)}}</span></span>
      <span class="f-bold">  TASmax/TASbasal: <span  class="f-normal">{{round($data->indice_tas,2)}}</span></span>  <span class="f-bold">  IEM: <span  class="f-normal">{{round($data->iem,2)}}</span></span>
      <span class="f-bold">  Recup. FC al 1er min (lpm): <span  class="f-normal">{{$data->fcmax_fc1er}}</span></span>  <span class="f-bold">  Rec TAS (3/1): <span  class="f-normal">{{round($data->tas_3er_min/$data->tas_1er_min,2)}}</span></span>
      <span class="f-bold">  PCE (mmHg%): <span  class="f-normal">{{round($data->pce)}}</span></span></p>
  </div>
  <div class="paciente mt-2">
    <div class="contenedor ">
      <h2 class="h5 titulo">Medición de Gases Aspirados</h2>
      <div class="linea-med"></div>
    </div>
    <p  class="f-bold m-t-2">VO2max (mlO2/Kg/min): <span class="f-normal">{{ round($data->vo2_max_gases,2)}}</span>
    <span class="f-bold">  VO2pico (mlO2/Kg/min): <span class="f-normal">{{round($data->vo2_pico_gases,2)}}</span></span> <span class=f-bold"">  R/Q(max.esf): <span  class="f-normal">{{round($data->r_qmax,2)}}</span></span>
    <span class=f-bold"">  Umbral A/An(mlO2/Kg/min): <span  class="f-normal">{{$data->umbral_aeer_anaer}}</span></span> <span class=f-bold"">  %PO 2 teórico: <span  class="f-normal">{{$data->po2_teor}}</span></span></p>
  </div>
  <div class="container-g f-10 mt-0">
    <div class="contenedor ">
      <h2 class="h5 titulo">Isquemia</h2>
      <div class="linea-is"></div>
    </div>
    <div class="table-container-g ml-2">
      <table class="table-g">  
        <thead class="thead-striped">
          <tr>
            <th scope="col" class="f-bold">Ind. tobillo/brazo(ITB)</th>
            <th scope="col">Basal</th>
            <th scope="col">Post-esfuerzo</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th scope="row">Pie derecho</th>
            <td>NV</td>
            <td>NV</td>
          </tr>
          <tr>
            <th scope="row">Pie Izquierdo</th>
            <td>NV</td>
            <td>NV</td>
          </tr>
        </tbody>
        </table>  
      </div>
      <div class=" ml-2 text-container-g mr-5">
        <p class="mb-2 f-bold">Indice Angina:<span class="ml-1 f-normal">{{$data->scoreAngina}} lpm</span></p>
        <p class="f-bold mb-1"> Depresión max ST (mm): <span class="ml-1 mr-2 f-normal">{{$data->MaxInfra}}</span></p>
      </div>
  </div>
  <br>
  <br>
  <br>
  <div class="paciente mt-0">
    <p class="f-bold text-sm">Tipo de isquemia: <span class="f-normal">{{$data->tipoCambioElectrico}}</span></p>
    <div class="contenedor ">
      <h2 class="h5 titulo">Arritmias</h2>
      <div class="linea-ar"></div>
    </div>
    <p  class="f-bold m-t-0">Arritimias: <span class="f-normal">{{ $data->tipoArritmias}}</span></p>
  </div>
  <div class="paciente mt-0">
    <div class="contenedor ">
      <h2 class="h5 titulo">Puntuaciones</h2>
      <div class="linea-pu"></div>
    </div>
    <p  class="f-bold m-t-0">Duke: <span class="f-normal">{{ round($data->duke,2)}}</span>
      <span class="f-bold">  Veranos (VA): <span class="f-normal">{{round($data->veteranos,2)}}</span></span></p>
  </div>
  <div class="contenedor ">
    <h2 class="h5 titulo"></h2>
    <div class="linea-t"></div>
  </div>
  <div class="paciente mt-1 mb-1">
    <p  class="f-bold">Conclusiones: <span class="f-normal">{{ $data->conclusiones}}</span></p> <p class="m-t-0 mb-0">
      <span class="f-bold">  Riesgo general de la prueba: <span class="f-normal">{{$data->riesgo}}</span></span>
      <span class="f-bold">  Realizó: <span class="f-normal">Dr {{" ". $user->nombre . " " . $user->apellidoPat}}</span></span></p>
  </div>
  <div class="contenedor">
    <h2 class="h5 titulo"></h2>
    <div class="linea-t"></div>
  </div>
  <div class="paciente mt-1">
    <p  class="f-bold mt-0 pt-0">Para diagnóstico de cardiopatía isquémica:
      <span class="f-bold">  Confusor: <span class="f-normal">{{$data->confusor}}</span></span>
      <span class="f-bold">  Prob pre-prueba: <span class="f-normal">{{$data->prevalencia}}%</span></span>
      <span class="f-bold">  Sensibilidad: <span class="f-normal">{{$data->sensibilidad}}%</span></span>
      <span class="f-bold">  Especificidad: <span class="f-normal">{{$data->especificidad}}%</span></span>
      <span class="f-bold">  V.Predictivo: <span class="f-normal">{{round($data->vpp,2)}}%</span></span></p>
  </div>

  
      
    </main>
  </body>
</html>