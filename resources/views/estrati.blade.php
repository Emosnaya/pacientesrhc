
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
        width: 20%;
        float: left;
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
        top: 0;
        left: 0;
    }
    .txt{
      width: 20%;
      margin-left: 1.5rem;
      margin-right: 5rem;
    }
    </style>
  </head>
  <body>
    <header class=" mb-0">
        <div class="paciente mt-0">
          <img src="img/logo.png" alt="cercap logo" style="height: 90px" class="">
            <p class="f-bold text-right mb-0 mt-0">Estratificación de riesgo CardioVascular</p>
            <p class="text-sm text-right mb-0 f-bold">Fecha: {{ $data->estrati_fecha}}</p>
            <p class="text-xl text-right mb-0 f-bold">Registro: {{$paciente->registro}}</p>
            <h2 class="h5 mt-0">Paciente</h2>
            <p  class="f-bold">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre}}</span>
            <span class="f-bold">  Peso : <span class="f-normal">{{$paciente->peso}}</span></span> <span class=f-bold"">  Talla: <span  class="f-normal">{{$paciente->talla}}</span></span>
            <span class=f-bold"">  Edad: <span  class="f-normal">{{$paciente->edad}}</span></span> <span class=f-bold"">  IMC: <span  class="f-normal">{{round($paciente->imc,2)}}</span></span>
            <span class=f-bold"">  Género: <span  class="f-normal">{{($paciente->genero==1?"Masculino":"Femenino")}}</span></span>  <span class=f-bold"">  CSE: <span  class="f-normal">{{$data->CSE}}</span></span></p>
        </div>
    </header>
    <main class="mt-0">
      <p class="f-bold mb-0 mt-0">Tabla de riesgo Cardiovascular</p>
        <table class="tabla text-lft border-t">
            <thead class="border-t">
              <tr>
                <th scope="col border-t">Rubro</th>
                <th scope="col border-t">Valor</th>
                <th scope="col border-t">Bajo</th>
                <th scope="col border-t">Medio</th>
                <th scope="col border-t">Alto</th>
              </tr>
            </thead >
            <tbody class="text-lft border-t">
              <tr>
                <th scope="row border-r">IMC</th>
                <td class="border-l border-r text-ctr">{{round($paciente->imc,2)}}</td>
                <td class="border-r text-ctr">{{(round($paciente->imc,2)<=25?"xxx":"")}}</td>
                <td class="border-r text-ctr">{{(round($paciente->imc,2)>25?"xxx":"")}}</td>
                <td class="text-ctr">{{(round($paciente->imc,2)>=30?"xxx":"")}}</td>
              </tr>
              <tr>
                <th scope="row">Perimetro de cintura</th>
                <td class="border-l border-r text-ctr">{{$paciente->cintrua}}</td>
                <td class="border-r text-ctr">{{($paciente->genero === 0 && $paciente->cintura<88) || ($paciente->genero === 1 && $paciente->cintura<102)?"xxx":""}}</td>
                <td class="border-r text-ctr"></td>
                <td class="border-r text-ctr">{{($paciente->genero === 0 && $paciente->cintura>88) || ($paciente->genero === 1 && $paciente->cintura>102)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Sintomatologia</th>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->sintomatologia === "bajo")?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->sintomatologia === "medio")?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->sintomatologia === "alto")?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Infarto, ACTP o CRVC complicado</th>
                <td class="border-l border-r text-ctr">{{($data->imComplicado === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr">{{($data->imComplicado === 0)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->imComplicado === 1)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Depresión Clinica</th>
                <td class="border-l border-r text-ctr">{{($data->depresion === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr">{{($data->depresion === 0)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->depresion === 1)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">FEVI</th>
                <td class="border-l border-r text-ctr">{{$data->fevi}}</td>
                <td class="border-l border-r text-ctr">{{($data->fevi>=50)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->fevi>=36 && $data->fevi<=49)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->fevi<=35)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Enfermedad Coronaria</th>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->enf_coronaria === "bajo")?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->enf_coronaria === "medio")?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->enf_coronaria === "alto")?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Sobreviviente de Reanimacion Pulmonar</th>
                <td class="border-l border-r text-ctr">{{($data->reanimacion_cardio === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr">{{($data->reanimacion_cardio === 0)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->reanimacion_cardio === 1)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Insuficiencia Cardiaca Congestiva</th>
                <td class="border-l border-r text-ctr">{{($data->icc === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr">{{($data->icc === 0)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->icc === 1)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Falla para realizar ejercicio</th>
                <td class="border-l border-r text-ctr">{{($data->falla_entrenar === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr">{{($data->falla_entrenar === 0)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->falla_entrenar === 1)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Holter</th>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->holter === "bajo")?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->holter === "medio")?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->holter === "alto")?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Isquemia en MN</th>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->isquemia === "bajo")?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->isquemia === "medio")?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->isquemia === "alto")?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Puntuacion ATP2000</th>
                <td class="border-l border-r text-ctr">{{$data->puntuacion_atp2000}}%</td>
                <td class="border-l border-r text-ctr">{{($data->puntuacion_atp2000/100)<=0.05?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->puntuacion_atp2000/100)>0.05 && ($data->puntuacion_atp2000/100)<0.2 ?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->puntuacion_atp2000/100)>=0.2?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">HeartScore</th>
                <td class="border-l border-r text-ctr">{{$data->heart_score}}%</td>
                <td class="border-l border-r text-ctr">{{($data->heart_score/100)<=0.05?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->heart_score/100)>0.05 && ($data->heart_score/100)<0.2 ?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{($data->heart_score/100)>=0.2?"xxx":""}}</td>
              </tr>
            </tbody>
        </table>
        <p class="f-bold mb-0">Prueba de esfuerzo</p>
        <table class="tabla border-t">
            <thead class="border-t">
              <tr>
                <th scope="col">Rubro</th>
                <th scope="col">Valor</th>
                <th scope="col">Bajo</th>
                <th scope="col">Medio</th>
                <th scope="col">Alto</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th scope="row">Capacidad para realizar prueba de esfuerzo</th>
                <td class="border-l border-r text-ctr">{{($data->pe_capacidad === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr">{{($data->pe_capacidad === 1)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->pe_capacidad === 0)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Tolerancia al esfuerzo</th>
                <td class="border-l border-r text-ctr">{{$data->tolerancia_max_esfuerzo}}</td>
                <td class="border-l border-r text-ctr">{{$data->tolerancia_max_esfuerzo>10.7?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{$data->tolerancia_max_esfuerzo<=10.7 && $data->tolerancia_max_esfuerzo>=5?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{$data->tolerancia_max_esfuerzo<5?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Extopia ventricular frecuente</th>
                <td class="border-l border-r text-ctr">{{($data->ectopia_ventricular === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr">{{($data->ectopia_ventricular === 0)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->ectopia_ventricular === 1)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Umbral isquémico</th>
                <td class="border-l border-r text-ctr">{{($data->umbral_isquemico === "true")?"si":"no"}}</td>
                <td class="border-l border-r text-ctr">{{($data->umbral_isquemico === "false")?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->umbral_isquemico === "true")?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Supradesnivel del segmento ST</th>
                <td class="border-l border-r text-ctr">{{($data->supranivel_st === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr">{{($data->supranivel_st === 0)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{($data->supranivel_st === 1)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Infradesnivel del segmento ST (>= 2mm)-fc</th>
                <td class="border-l border-r text-ctr">{{($data->infra_st_mayor2_135 === "false")?"n":"s"}}</td>
                <td class="border-l border-r text-ctr">No {{($data->infra_st_mayor2_135 === "false")?"x":""}}</td>
                <td class="border-l border-r text-ctr">Fc>135 {{($data->infra_st_mayor2_135 === "m_135")?"x":""}}</td>
                <td class="border-l border-r text-ctr">Fc<=135{{($data->infra_st_mayor2_135 === "me_135")?"x":""}}</td>
              </tr>
              <tr>
                <th scope="row">Infradesnivel del segmento ST (>= 2mm)-METs</th>
                <td class="border-l border-r text-ctr">{{($data->infra_st_mayor2_5mets === "false")?"n":"s"}}</td>
                <td class="border-l border-r text-ctr">No {{($data->infra_st_mayor2_5mets === "false")?"x":""}}</td>
                <td class="border-l border-r text-ctr">Fc>135 {{($data->infra_st_mayor2_5mets === "m_5")?"x":""}}</td>
                <td class="border-l border-r text-ctr">Fc<=135{{($data->infra_st_mayor2_5mets === "me_5")?"x":""}}</td>
              </tr>
              <tr>
                <th scope="row">Respuesta presora</th>
                <td class="border-l border-r text-ctr">{{$data->respuesta_presora}}</td>
                <td class="border-l border-r text-ctr">{{$data->respuesta_presora>=7?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{$data->respuesta_presora<7 && $data->respuesta_presora>=0?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{$data->respuesta_presora<0?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Índice TA en esfuerzo </th>
                <td class="border-l border-r text-ctr">{{$data->indice_ta_esf}}</td>
                <td class="border-l border-r text-ctr">{{$data->indice_ta_esf>=1.22?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{$data->indice_ta_esf<1.22?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">% alcanzado de la FC predicha</th>
                <td class="border-l border-r text-ctr">{{$data->porc_fc_pre_alcanzado}}</td>
                <td class="border-l border-r text-ctr">{{$data->porc_fc_pre_alcanzado>=85?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{$data->porc_fc_pre_alcanzado<85?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Respuesta cronotrópica</th>
                <td class="border-l border-r text-ctr">{{$data->r_cronotr * 0.1}}</td>
                <td class="border-l border-r text-ctr">{{$data->r_cronotr*0.1>=0.8?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{$data->r_cronotr*0.1<0.8?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Poder cardiaco en ejercicio</th>
                <td class="border-l border-r text-ctr">{{$data->porder_cardiaco}}</td>
                <td class="border-l border-r text-ctr">{{$data->porder_cardiaco>=9000?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{$data->porder_cardiaco<9000 && $data->respuesta_presora>=5000?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{$data->porder_cardiaco<5000?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Recupración de la TA sistólica</th>
                <td class="border-l border-r text-ctr">{{$data->recuperacion_tas}}</td>
                <td class="border-l border-r text-ctr">{{$data->recuperacion_tas<=0.95?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{$data->recuperacion_tas>0.95?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Recuperación de la FC</th>
                <td class="border-l border-r text-ctr">{{$data->recuperacion_fc}}</td>
                <td class="border-l border-r text-ctr">{{$data->recuperacion_fc>12?"xxx":""}}</td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr">{{$data->recuperacion_fc<=12?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Puntuación de Duke</th>
                <td class="border-l border-r text-ctr">{{$data->duke}}</td>
                <td class="border-l border-r text-ctr">{{$data->duke>5?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{$data->duke>(-11) && $data->duke<5?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{$data->duke<(-11)?"xxx":""}}</td>
              </tr>
              <tr>
                <th scope="row">Puntuación de veteranos</th>
                <td class="border-l border-r text-ctr">{{$data->veteranos}}</td>
                <td class="border-l border-r text-ctr">{{$data->veteranos<(-2)?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{$data->veteranos>=(-2) && $data->duke<=2?"xxx":""}}</td>
                <td class="border-l border-r text-ctr">{{$data->veteranos>2?"xxx":""}}</td>
              </tr>
              <tr class="b-dark">
                <th scope="row">Riesgo Global</th>
                <td></td>
                <td class="b-w border-t text-ctr">{{$data->riesgo_global==='bajo'?"xxx":""}}</td>
                <td class="b-w border-t text-ctr">{{$data->riesgo_global==='medio'?"xxx":""}}</td>
                <td class="b-w border-t text-ctr">{{$data->riesgo_global==='alto'?"xxx":""}}</td>
              </tr>
            </tbody>
        </table>
        <p class="mb-1">Parámetros Iniciales</p>
        <div class="container-g f-10">
            <div class="table-container-g">
              <p class="mb-0 ml-5">Grupo</p>
                <table class="table-g">  
                <thead class="thead-striped">
                  <tr>
                    <th scope="col">A: <span>{{$data->grupo==="a"?"xx":""}}</span></th>
                    <th scope="col">B: <span>{{$data->grupo==="b"?"xx":""}}</span></th>
                    <th scope="col">C: <span>{{$data->grupo==="c"?"xx":""}}</span></th>
                    <th scope="col">D: <span>{{$data->grupo==="d"?"xx":""}}</span></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                </tbody>
                </table>
            </div>
            <div class="table-container-g">
              <p class="mb-0 ml-5">Semanas</p>
                <table class="table-g">
                    <!-- Contenido de la segunda tabla -->
                    <thead class="">
                      <tr>
                        <th scope="col">1: <span>{{$data->semanas===1?"xx":""}}</span></th>
                        <th scope="col">2: <span>{{$data->semanas===2?"xx":""}}</span></th>
                        <th scope="col">4: <span>{{$data->semanas===4?"xx":""}}</span></th>
                        <th scope="col">6: <span>{{$data->semanas===6?"xx":""}}</span></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                      </tr>
                    </tbody>

                </table>
            </div>
            <div class="table-container-g">
              <p class="mb-0 ml-5">Borg</p>
                <table class="table-g">
                    <!-- Contenido de la tercera tabla -->
                    <thead class="">
                      <tr>
                        <th scope="col">8: <span>{{$data->borg===8?"xx":""}}</span></th>
                        <th scope="col">10: <span>{{$data->borg===10?"xx":""}}</span></th>
                        <th scope="col">12: <span>{{$data->borg===12?"xx":""}}</span></th>
                        <th scope="col">14: <span>{{$data->borg===14?"xx":""}}</span></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row"></th>
                        <td></td>
                        <td></td>
                        <td></td>
                      </tr>
                    </tbody>

                </table>
            </div>
            <div class=" ml-2 text-container-g">
                <p class="mb-1">Fc Diana :<span class="ml-1">{{$data->fc_borg_12}} lpm</span> <span class="ml-1 f-bold">Dp Diana: <span class="f-normal">{{$data->dp_diana}} mmHg*lpm</span> </span></p>
                <p class="f-bold mb-1">{{$data->fc_diana_str}}:<span class="ml-1 mr-2 f-normal">Método(Borg,Karvonen,Blakburn,Narita)</span></p>
                <p class="f-bold">Carga Inicial: <span class="f-normal">{{$data->carga_inicial}} Watts</span></p>
            </div>
        </div>
        <p class="coments mt-5 f-10 mb-0">Comentarios : {{$data->comentarios}}</p>
        <div class="signature">
          <div class="line"></div>
          <div class="line "></div>
      </div>
      <div class="text mt-0">
        <span class="txt">Dr {{$user->nombre . " " . $user->apellidoPat}}</span>
        <span class="txt">Enfermera</span>
      </div>

    </main>

  </body>
</html>