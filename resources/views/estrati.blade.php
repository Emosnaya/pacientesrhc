
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
    body {
        font-family: 'Arial', sans-serif; /* Cambia 'Arial' por una fuente que soporte Unicode si es necesario */
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
    </style>
  </head>
  <body>
    <header class=" mb-0">
        <div class="paciente mt-0">
          <p class="f-bold f-15 text-center mb-0 mt-0">Estratificación de riesgo CardioVascular</p>
          <img src="img/logo.png" alt="cercap logo" style="height: 90px" class="">
          <div class="medio">
            <p class="text-sm texto-izquierda mb-0 f-bold">Fecha: {{ $data->estrati_fecha}} </p> <span class="ml-5 text-right texto-derecha f-bold">Registro: {{$paciente->registro}}</span>
          </div>
          <br>
            <p  class="f-bold f-15 ">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre}}</span>
            <span class="f-bold">  Peso : <span class="f-normal">{{$paciente->peso}}</span></span> <span class=f-bold"">  Talla: <span  class="f-normal">{{$paciente->talla}}</span></span>
            <span class=f-bold"">  Edad: <span  class="f-normal">{{$paciente->edad}}</span></span> <span class=f-bold"">  IMC: <span  class="f-normal">{{round($paciente->imc,2)}}</span></span>
            <span class=f-bold"">  Género: <span  class="f-normal">{{($paciente->genero==1?"Hombre":"Mujer")}}</span></span></p>
        </div>
    </header>
    <main class="mt-0">
      <div class="contenedor ">
        <h2 class="h5 titulo mt-1">Tabla de Riesgo Cardiovascular</h2>
        <div class="linea-t"></div>
      </div>
        <table class="tabla text-lft border-t table-striped m-t-1">
            <thead class="border-t">
              <tr>
                <th scope="col border-t">Rubro</th>
                <th scope="col border-t">Valor</th>
                <th scope="col border-t">Bajo</th>
                <th scope="col border-t">Medio</th>
                <th scope="col border-t">Alto</th>
              </tr>
            </thead >
            <tbody class="text-lft border-t ">
              <tr>
                <th scope="row border-r">IMC</th>
                <td class="border-l border-r text-ctr">{{round($paciente->imc,2)}}</td>
                <td class="border-r text-ctr @if(round($paciente->imc,2) <= 25) bg-success @else  @endif">@if(round($paciente->imc,2) <= 25) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-r text-ctr @if(round($paciente->imc,2) > 25) bg-warning @else  @endif">@if(round($paciente->imc,2) > 25) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="text-ctr  @if(round($paciente->imc,2) >= 30) bg-danger @else  @endif">@if(round($paciente->imc,2) >= 30) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
              </tr>
              <tr class="">
                <th scope="row ">Perimetro de cintura</th>
                <td class="border-l border-r text-ctr">{{$paciente->cintura}}</td>
                <td class="border-r text-ctr @if(($paciente->genero === 0 && $paciente->cintura<88) || ($paciente->genero === 1 && $paciente->cintura<102)) bg-success @else  @endif">@if(($paciente->genero === 0 && $paciente->cintura<88) || ($paciente->genero === 1 && $paciente->cintura<102)) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-r text-ctr"></td>
                <td class="border-r text-ctr @if(($paciente->genero === 0 && $paciente->cintura>88) || ($paciente->genero === 1 && $paciente->cintura>102)) bg-danger @else  @endif">@if(($paciente->genero === 0 && $paciente->cintura>88) || ($paciente->genero === 1 && $paciente->cintura>102)) <img src="img/check-solid.svg" alt="" style="height: 13px" class="">  @else &nbsp; @endif</td>
              </tr>
              <tr>
                <th scope="row">Sintomatologia</th>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->sintomatologia === "bajo") bg-success @else  @endif"> @if($data->sintomatologia === "bajo") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->sintomatologia === "medio") bg-warning @else  @endif">@if($data->sintomatologia === "medio") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-l border-r text-ctr @if($data->sintomatologia === "alto") bg-danger @else  @endif">@if($data->sintomatologia === "alto") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
              </tr>
              <tr>
                <th scope="row">Infarto, ACTP o CRVC complicado</th>
                <td class="border-l border-r text-ctr">{{($data->imComplicado === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr @if($data->imComplicado === 0) bg-success @else  @endif">@if($data->imComplicado === 0) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->imComplicado === 1) bg-danger @else  @endif">@if($data->imComplicado === 1) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Depresión Clinica</th>
                <td class="border-l border-r text-ctr">{{($data->depresion === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr @if($data->depresion === 0) bg-success @else  @endif">@if($data->depresion === 0) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->depresion === 1) bg-danger @else  @endif">@if($data->depresion === 1) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">FEVI</th>
                <td class="border-l border-r text-ctr">{{$data->fevi}}</td>
                <td class="border-l border-r text-ctr @if($data->fevi>=50) bg-success @else  @endif">@if($data->fevi>=50) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-l border-r text-ctr @if($data->fevi>=36 && $data->fevi<=49) bg-warning @else  @endif">@if($data->fevi>=36 && $data->fevi<=49) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-l border-r text-ctr @if($data->fevi<=35) bg-danger @else  @endif">@if($data->fevi<=35) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
              </tr>
              <tr>
                <th scope="row">Enfermedad Coronaria</th>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->enf_coronaria === "bajo") bg-success @else  @endif"> @if($data->enf_coronaria === "bajo") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->enf_coronaria === "medio") bg-warning @else  @endif">@if($data->enf_coronaria === "medio") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-l border-r text-ctr @if($data->enf_coronaria === "alto") bg-danger @else  @endif">@if($data->enf_coronaria === "alto") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
              </tr>
              <tr>
                <th scope="row">Sobreviviente de Reanimacion Pulmonar</th>
                <td class="border-l border-r text-ctr">{{($data->reanimacion_cardio === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr @if($data->reanimacion_cardio === 0) bg-success @else  @endif">@if($data->reanimacion_cardio === 0) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->reanimacion_cardio === 1) bg-danger @else  @endif">@if($data->reanimacion_cardio === 1) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Insuficiencia Cardiaca Congestiva</th>
                <td class="border-l border-r text-ctr">{{($data->icc === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr @if($data->icc === 0) bg-success @else  @endif">@if($data->icc === 0) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->icc === 1) bg-danger @else  @endif">@if($data->icc === 1) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Falla para realizar ejercicio</th>
                <td class="border-l border-r text-ctr">{{($data->falla_entrenar === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr @if($data->falla_entrenar === 0) bg-success @else  @endif">@if($data->falla_entrenar === 0) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->falla_entrenar === 1) bg-danger @else  @endif">@if($data->falla_entrenar === 1) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Holter</th>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->holter === "bajo") bg-success @else  @endif"> @if($data->holter === "bajo") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->holter === "medio") bg-warning @else  @endif">@if($data->holter === "medio") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-l border-r text-ctr @if($data->holter === "alto") bg-danger @else  @endif">@if($data->holter === "alto") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
              </tr>
              <tr>
                <th scope="row">Isquemia en MN</th>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->isquemia === "bajo") bg-success @else  @endif"> @if($data->isquemia === "bajo") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->isquemia === "medio") bg-warning @else  @endif">@if($data->isquemia === "medio") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-l border-r text-ctr @if($data->isquemia === "alto") bg-danger @else  @endif">@if($data->isquemia === "alto") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
              </tr>
              <tr>
                <th scope="row">Puntuacion ATP2000</th>
                <td class="border-l border-r text-ctr">{{$data->puntuacion_atp2000}}%</td>
                <td class="border-l border-r text-ctr @if($data->puntuacion_atp2000/100<=0.05) bg-success @else  @endif">@if(($data->puntuacion_atp2000/100)<=0.05) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-l border-r text-ctr @if(($data->puntuacion_atp2000/100)>0.05 && ($data->puntuacion_atp2000/100)<0.2) bg-warning @else  @endif">@if(($data->puntuacion_atp2000/100)>0.05 && ($data->puntuacion_atp2000/100)<0.2) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-l border-r text-ctr @if(($data->puntuacion_atp2000/100)>=0.2) bg-danger @else  @endif">@if(($data->puntuacion_atp2000/100)>=0.2) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
              </tr>
              <tr>
                <th scope="row">HeartScore</th>
                <td class="border-l border-r text-ctr">{{$data->heart_score}}%</td>
                <td class="border-l border-r text-ctr @if(($data->heart_score/100)<=0.05) bg-success @else  @endif">@if(($data->heart_score/100)<=0.05) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-l border-r text-ctr @if(($data->heart_score/100)>0.05 && ($data->heart_score/100)<0.2) bg-warning @else  @endif">@if(($data->heart_score/100)>0.05 && ($data->heart_score/100)<0.2) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
                <td class="border-l border-r text-ctr @if(($data->heart_score/100)>=0.2) bg-danger @else  @endif">@if(($data->heart_score/100)>=0.2) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif</td>
              </tr>
            </tbody>
        </table>
        <div class="contenedor ">
          <h2 class="h5 titulo mt-1">Prueba de Esfuerzo</h2>
          <div class="linea-p"></div>
        </div>
        <table class="tabla border-t table-striped m-t-1">
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
                <td class="border-l border-r text-ctr @if($data->pe_capacidad === 1) bg-success @else  @endif">@if($data->pe_capacidad === 1) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->pe_capacidad === 0) bg-danger @else  @endif">@if($data->pe_capacidad === 0) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Tolerancia al esfuerzo</th>
                <td class="border-l border-r text-ctr">{{$data->tolerancia_max_esfuerzo}}</td>
                <td class="border-l border-r text-ctr @if($data->tolerancia_max_esfuerzo>10.7) bg-success @else  @endif">@if($data->tolerancia_max_esfuerzo>10.7) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->tolerancia_max_esfuerzo<=10.7 && $data->tolerancia_max_esfuerzo>=5) bg-warning @else  @endif">@if($data->tolerancia_max_esfuerzo<=10.7 && $data->tolerancia_max_esfuerzo>=5) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->tolerancia_max_esfuerzo<5) bg-danger @else  @endif">@if($data->tolerancia_max_esfuerzo<5) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Extopia ventricular frecuente</th>
                <td class="border-l border-r text-ctr">{{($data->ectopia_ventricular === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr @if($data->ectopia_ventricular === 0) bg-success @else  @endif">@if($data->ectopia_ventricular === 0) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->ectopia_ventricular === 1) bg-danger @else  @endif">@if($data->ectopia_ventricular === 1) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Umbral isquémico</th>
                <td class="border-l border-r text-ctr">{{($data->umbral_isquemico === "true")?"si":"no"}}</td>
                <td class="border-l border-r text-ctr @if($data->umbral_isquemico === "false") bg-success @else  @endif">@if($data->umbral_isquemico === "false") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->umbral_isquemico === "true") bg-danger @else  @endif">@if($data->umbral_isquemico === "true") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Supradesnivel del segmento ST</th>
                <td class="border-l border-r text-ctr">{{($data->supranivel_st === 0)?"n":"s"}}</td>
                <td class="border-l border-r text-ctr @if($data->supranivel_st === 0) bg-success @else  @endif">@if($data->ectopia_ventricular === 0) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->supranivel_st === 1) bg-danger @else  @endif">@if($data->ectopia_ventricular === 1) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Infradesnivel del segmento ST (>= 2mm)-fc</th>
                <td class="border-l border-r text-ctr ">{{($data->infra_st_mayor2_135 === "false")?"n":"s"}}</td>
                <td class="border-l border-r text-ctr @if($data->infra_st_mayor2_135 === "false") bg-success @else  @endif">@if($data->infra_st_mayor2_135 === "false") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->infra_st_mayor2_135 === "m_135") bg-warning @else  @endif"> Fc>135 @if($data->infra_st_mayor2_135 === "m_135") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->infra_st_mayor2_135 === "me_135") bg-danger @else  @endif"> Fc=135 @if($data->infra_st_mayor2_135 === "me_135") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Infradesnivel del segmento ST (>= 2mm)-METs</th>
                <td class="border-l border-r text-ctr">{{($data->infra_st_mayor2_5mets === "false")?"n":"s"}}</td>
                <td class="border-l border-r text-ctr @if($data->infra_st_mayor2_5mets === "false") bg-success @else  @endif">No @if($data->infra_st_mayor2_5mets === "false") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->infra_st_mayor2_5mets === "m_5") bg-warning @else  @endif"> Fc>135 @if($data->infra_st_mayor2_5mets === "m_5") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->infra_st_mayor2_5mets === "me_5") bg-danger @else  @endif"> @if($data->infra_st_mayor2_5mets === "me_5") <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Respuesta presora</th>
                <td class="border-l border-r text-ctr">{{$data->respuesta_presora}}</td>
                <td class="border-l border-r text-ctr @if($data->respuesta_presora>=7) bg-success @else  @endif">@if($data->respuesta_presora>=7) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->respuesta_presora<7 && $data->respuesta_presora>=0) bg-warning @else  @endif">@if($data->respuesta_presora<7 && $data->respuesta_presora>=0) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->respuesta_presora<0) bg-danger @else  @endif"> @if($data->respuesta_presora<0) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Índice TA en esfuerzo </th>
                <td class="border-l border-r text-ctr">{{$data->indice_ta_esf}}</td>
                <td class="border-l border-r text-ctr @if($data->indice_ta_esf>=1.22) bg-success @else  @endif">@if($data->indice_ta_esf>=1.22) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->indice_ta_esf<1.22) bg-danger @else  @endif">@if($data->indice_ta_esf<1.22) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">% alcanzado de la FC predicha</th>
                <td class="border-l border-r text-ctr">{{$data->porc_fc_pre_alcanzado}}</td>
                <td class="border-l border-r text-ctr @if($data->porc_fc_pre_alcanzado>=85) bg-success @else  @endif">@if($data->porc_fc_pre_alcanzado>=85) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->porc_fc_pre_alcanzado<85) bg-danger @else  @endif">@if($data->porc_fc_pre_alcanzado<85) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Respuesta cronotrópica</th>
                <td class="border-l border-r text-ctr">{{$data->r_cronotr * 0.1}}</td>
                <td class="border-l border-r text-ctr @if($data->r_cronotr*0.1>=0.8) bg-success @else  @endif">@if($data->r_cronotr*0.1>=0.8) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->r_cronotr*0.1<0.8) bg-danger @else  @endif">@if($data->r_cronotr*0.1<0.8) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Poder cardiaco en ejercicio</th>
                <td class="border-l border-r text-ctr">{{$data->porder_cardiaco}}</td>
                <td class="border-l border-r text-ctr @if($data->porder_cardiaco>=9000) bg-success @else  @endif">@if($data->porder_cardiaco>=9000) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->porder_cardiaco<9000 && $data->respuesta_presora>=5000) bg-warning @else  @endif"> @if($data->porder_cardiaco<9000 && $data->respuesta_presora>=5000) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->porder_cardiaco<5000) bg-danger @else  @endif"> @if($data->porder_cardiaco<5000) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Recupración de la TA sistólica</th>
                <td class="border-l border-r text-ctr">{{$data->recuperacion_tas}}</td>
                <td class="border-l border-r text-ctr @if($data->recuperacion_tas<=0.95) bg-success @else  @endif">@if($data->recuperacion_tas<=0.95) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->recuperacion_tas>0.95) bg-danger @else  @endif">@if($data->recuperacion_tas>0.95) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Recuperación de la FC</th>
                <td class="border-l border-r text-ctr">{{$data->recuperacion_fc}}</td>
                <td class="border-l border-r text-ctr @if($data->recuperacion_fc>12) bg-success @else  @endif">@if($data->recuperacion_fc>12) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr"></td>
                <td class="border-l border-r text-ctr @if($data->recuperacion_fc<=12) bg-danger @else  @endif">@if($data->recuperacion_fc<=12) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Puntuación de Duke</th>
                <td class="border-l border-r text-ctr">{{$data->duke}}</td>
                <td class="border-l border-r text-ctr @if($data->duke>5) bg-success @else  @endif">@if($data->duke>5) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->duke>(-11) && $data->duke<5) bg-warning @else  @endif"> @if($data->duke>(-11) && $data->duke<5) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->duke<(-11)) bg-danger @else  @endif"> @if($data->duke<(-11)) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr>
                <th scope="row">Puntuación de veteranos</th>
                <td class="border-l border-r text-ctr">{{$data->veteranos}}</td>
                <td class="border-l border-r text-ctr @if($data->veteranos<(-2)) bg-success @else  @endif">@if($data->veteranos<(-2)) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->veteranos>=(-2) && $data->duke<=2) bg-warning @else  @endif"> @if($data->veteranos>=(-2) && $data->duke<=2) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="border-l border-r text-ctr @if($data->veteranos>2) bg-danger @else  @endif"> @if($data->veteranos>2) <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
              <tr class="bg-dark text-white">
                <th scope="row ">Riesgo Global</th>
                <td></td>
                <td class="b-w border-t text-ctr @if($data->riesgo_global==='bajo') bg-success @else  @endif">@if($data->riesgo_global==='bajo') <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="b-w border-t text-ctr @if($data->riesgo_global==='medio') bg-warning @else  @endif">@if($data->riesgo_global==='medio') <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
                <td class="b-w border-t text-ctr @if($data->riesgo_global==='alto') bg-danger @else  @endif">@if($data->riesgo_global==='alto') <img src="img/check-solid.svg" alt="" style="height: 13px" class="font-light"> @else &nbsp; @endif </td>
              </tr>
            </tbody>
        </table>
        <div class="contenedor ">
          <h2 class="h5 titulo mt-1">Parámetros Iniciales</h2>
          <div class="linea-pu"></div>
        </div>
        <div class="container-g f-10 m-t-1">
            <div class="table-container-g">
              <p class="mb-0 ml-5">Grupo</p>
                <table class="table-g">  
                <thead class="thead-striped">
                  <tr>
                    <th scope="col">A: <span>@if($data->grupo==='a') <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;" class="font-light"> @else &nbsp; @endif</span></th>
                    <th scope="col">B: <span>@if($data->grupo==='b') <img src="img/check-solid.svg" alt="" style="height: 14px" class="font-light"> @else &nbsp; @endif</span></th>
                    <th scope="col">C: <span>@if($data->grupo==='c') <img src="img/check-solid.svg" alt="" style="height: 14px" class="font-light"> @else &nbsp; @endif</span></th>
                    <th scope="col">D: <span>@if($data->grupo==='d') <img src="img/check-solid.svg" alt="" style="height: 14px" class="font-light"> @else &nbsp; @endif</span></th>
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
                      <th scope="col">1: <span>@if($data->semanas===1) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;" class="font-light"> @else &nbsp; @endif</span></th>
                      <th scope="col">2: <span>@if($data->semanas===2) <img src="img/check-solid.svg" alt="" style="height: 14px" class="font-light"> @else &nbsp; @endif</span></th>
                      <th scope="col">4: <span>@if($data->semanas===4) <img src="img/check-solid.svg" alt="" style="height: 14px" class="font-light"> @else &nbsp; @endif</span></th>
                      <th scope="col">6: <span>@if($data->semanas===6) <img src="img/check-solid.svg" alt="" style="height: 14px" class="font-light"> @else &nbsp; @endif</span></th>
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
                        <th scope="col">8: <span>@if($data->borg===8) <img src="img/check-solid.svg" alt="" style="height: 14px ;margin-top:1px;" class="font-light"> @else &nbsp; @endif</span></th>
                      <th scope="col">10: <span>@if($data->borg===10) <img src="img/check-solid.svg" alt="" style="height: 14px" class="font-light"> @else &nbsp; @endif</span></th>
                      <th scope="col">12: <span>@if($data->borg===12) <img src="img/check-solid.svg" alt="" style="height: 14px" class="font-light"> @else &nbsp; @endif</span></th>
                      <th scope="col">14: <span>@if($data->borg===14) <img src="img/check-solid.svg" alt="" style="height: 14px" class="font-light"> @else &nbsp; @endif</span></th>
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
                <p class="mb-1 f-bold">Fc Diana :<span class="ml-1 f-normal">{{$data->fc_borg_12}} lpm</span> <span class="ml-1 f-bold">Dp Diana: <span class="f-normal">{{$data->dp_diana}} mmHg*lpm</span> </span></p>
                <p class="f-bold mb-1">{{$data->fc_diana_str}}:<span class="ml-1 mr-2 f-normal">Método(Borg,Karvonen,Blakburn,Narita)</span></p>
                <p class="f-bold">Carga Inicial: <span class="f-normal">{{$data->carga_inicial}} Watts</span></p>
            </div>
        </div>
        <br>
        <br>
        <div class="medio">
          <p class="texto-izquierda mt-5 f-10 mb-0 f-bold">Comentarios : <span class="f-normal">{{$data->comentarios}}</span></p>
        </div>
        <div class="signature">
          <div class="line"></div>
      </div>
      <div class="text mt-0">
        <span class="txt">Dr.  {{$user->nombre . "   " . $user->apellidoPat}}</span>
      </div>

    </main>

  </body>
</html>