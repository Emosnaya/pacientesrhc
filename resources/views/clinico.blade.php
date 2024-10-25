
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
        font-size: 8px;
        text-align: center;
        width: 100%; /* Espacio entre línea y texto */
    }
        .tabla{
            font-size: 7.5px;
            margin-bottom: 0;
            width: 100%
        }
        .f-10{
          font-size: 8.5px;
        }
        .f-15{
          font-size: 13px;
        }
        .paciente{
            font-size: 10px
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
  .m-t-1{
    margin-top: -1rem;
  }
  .m-t-2{
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
  .linea-irm {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 13rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.5rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-des {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 10rem; /* Comienza desde el borde izquierdo del contenedor */
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
  .linea-clase {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 8.7rem; /* Comienza desde el borde izquierdo del contenedor */
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
    left: 7rem; /* Comienza desde el borde izquierdo del contenedor */
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
  .linea-elec {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 11rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.5rem;  /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-eco {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 8.7rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: 0.5rem;  /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .bck-gray{
    background-color: #DDDEE1  ;
  }
  .text-lf{
    text-align: left
  }
    </style>
  </head>
  <body>
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <p class="f-bold f-15 text-center mb-0 mt-0">Expediente Clínico de Rehabilitación Cardiaca</p>
            <img src="img/logo.png" alt="cercap logo" style="height: 90px" class="">
            <div class="medio">
              <p class=" texto-izquierda mb-0 f-bold">Fecha: {{ date('d/m/Y',strtotime($data->fecha))}} </p> <span class="ml-5 text-right texto-derecha f-bold">Registro: {{$paciente->registro}}</span>
            </div>
            <br>
              <p  class="f-bold mt-2 mb-0 f-15">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre}}</span></p>
              <p class="f-bold mb-0">Diagnostico: <span  class="f-normal">{{$paciente->diagnostico}}</span> </p>
              <p class="mt-0 mb-0"> <span class="f-bold">  Edad: <span  class="f-normal">{{$paciente->edad}}</span></span><span class="f-bold ml-3">  Peso (Kg) : <span class="f-normal">{{$paciente->peso}}</span></span> 
                <span class="f-bold ml-3">  Talla (m): <span  class="f-normal">{{$paciente->talla}}</span></span> <span class="f-bold ml-3">  IMC (kg/m2):: <span  class="f-normal">{{round($paciente->imc,2)}}</span></span>
              <span class="f-bold ml-3">  Género: <span  class="f-normal">{{($paciente->genero==1?"Hombre":"Mujer")}}</span></span>  <span class="f-bold ml-3">  Estado Civil: <span  class="f-normal">{{$paciente->estadoCivil}}</span></span>
              <span class="f-bold ml-3">  Profesión: <span  class="f-normal">{{$paciente->profesion}}</span></span>  <span class="f-bold">  F.Nacimiento: <span  class="f-normal">{{$paciente->fechaNacimiento}}</span></span>
              <span class="f-bold ml-3">  Ingreso(1vez): <span  class="f-normal">{{date('d/m/Y',strtotime($data->fecha_1vez))}}</span></span> <span class="f-bold ml-3">  Estratificación: <span  class="f-normal">{{ date('d/m/Y',strtotime($data->estratificacion))}}</span></span></p>
              <p class="f-bold mt-0 mb-0"> Domicilio: <span  class="f-normal">{{$paciente->domicilio}}</span> <span class="f-bold ml-3">  Teléfono: <span  class="f-normal">{{$paciente->telefono}}</span></span></p>
          </div>
    </header>
    <main class="mt-0">
        <table class="tabla text-lft border-t text-center mt-1 table-striped bck-gray ">
            <tbody>
                <tr class="">
                  <td class="border-r">IM Anterior: <span class="f-bold">{{$data->imAnterior===null?"n": date('d/m/Y',strtotime($data->imAnterior))}}</span></td>
                  <td class="border-r">IM Septal: <span class="f-bold">{{$data->imSeptal===null?"n":date('d/m/Y',strtotime($data->imSeptal))}}</span></td>
                  <td class="border-r">IM Apical: <span class="f-bold">{{$data->imApical===null?"n":date('d/m/Y',strtotime($data->imApical))}}</span></td>
                  <td class="border-r">IM Lateral: <span class="f-bold">{{$data->imLateral===null?"n":date('d/m/Y',strtotime($data->imLateral))}}</span></td>
                </tr>
                <tr>
                  <td class="border-r">IM Inferior: <span class="f-bold">{{$data->imInferior===null?"n":date('d/m/Y',strtotime($data->imInferior))}}</span></td>
                  <td class="border-r">IM del VD: <span class="f-bold">{{$data->imdelVD===null?"n":date('d/m/Y',strtotime($data->imdelVD))}}</span></td>
                  <td class="border-r">A. Inestable: <span class="f-bold">{{$data->anginaInestabale===null?"n":$data->anginaInestabale}}</span></td>
                  <td class="border-r">A. Estable: <span class="f-bold">{{$data->anginaEstabale===null?"n":$data->anginaEstabale}}</span></td>
                </tr>
                <tr>
                  <td class="border-r">Ch. Cardiogénico: <span class="f-bold">{{$data->choque_card===null?"n":date('d/m/Y',strtotime($data->choque_card))}}</span></td>
                  <td class="border-r">Muerte Súbita: <span class="f-bold">{{$data->m_subita===null?"n":date('d/m/Y',strtotime($data->m_subita))}}</span></td>
                  <td class="border-r"></td>
                  <td class="border-r"></td>
                </tr>
                <tr>
                  <td class="border-r">Falla Cardiaca: <span class="f-bold">{{$data->falla_cardiaca===null||$data->falla_cardiaca===0?"n":"s"}}</span></td>
                  <td class="border-r">CRVC: <span class="f-bold">{{$data->crvc===null?"n":date('d/m/Y',strtotime($data->crvc))}}</span></td>
                  <td class="border-r">CRVC (HV): <span class="f-bold">{{$data->crvc_hemoductos===null?"n":$data->crvc_hemoductos}}</span></td>
                  <td class="border-r"></td>
                </tr>
                <tr>
                  <td class="border-r">I. Arterial Perif.: <span class="f-bold">{{$data->insuficiencia_art_per===null||$data->insuficiencia_art_per===0?"n":"s"}}</span></td>
                  <td class="border-r">V. Mitral: <span class="f-bold">{{$data->v_mitral===null||$data->v_mitral===0?"n":"s"}}</span></td>
                  <td class="border-r">V. Aórtica: <span class="f-bold">{{$data->v_aortica===null||$data->v_aortica===0?"n":"s"}}</span></td>
                  <td class="border-r">V. Tricúspide: <span class="f-bold">{{$data->v_tricuspide===null||$data->v_tricuspide===0?"n":"s"}}</span></td>
                </tr>
                <tr>
                  <td class="border-r" >V. Pulmonar: <span class="f-bold">{{$data->v_pulmonar===null||$data->v_pulmonar===0?"n":"s"}}</span></td>
                  <td class="border-r" >Congénitos: <span class="f-bold">{{$data->congenitos===null||$data->congenitos===0?"n":"s"}}</span></td>
                  <td class="border-r" ></td>
                  <td ></td>
                </tr>
              </tbody>
        </table>
        <div class="contenedor mt-2">
            <h2 class="h5 titulo">Factores de Riesgo</h2>
            <div class="linea-des"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr class="">
                  <td class="border-r">Hipercolesterolemia: <span class="f-bold">{{$data->hipercolesterolemia_y===null||$data->hipercolesterolemia_y==0?"n":$data->hipercolesterolemia_y}}</span></td>
                  <td class="border-r">Hipertensión: <span class="f-bold">{{$data->hipertension_años===null||$data->hipertension_años==0?"n":$data->hipertension_años}}</span></td>
                  <td class="border-r">Estrés: <span class="f-bold">{{$data->estres_years===null||$data->estres_years==0?"n":$data->estres_years}}</span></td>
                  <td class="border-r">Diabetes M: <span class="f-bold">{{$data->diabetes_y===null||$data->diabetes_y==0?"n":$data->diabetes_y}}</span></td>
                </tr>
                <tr>
                  <td class="border-r">Hipertrigliceridemia: <span class="f-bold">{{$data->hipertrigliceridemia_y===null||$data->hipertrigliceridemia_y==0?"n":$data->hipertrigliceridemia_y}}</span></td>
                  <td class="border-r">Depresión: <span class="f-bold">{{$data->depresion_years===null||$data->depresion_years==0?"n":$data->depresion_years}}</span></td>
                  <td class="border-r">Ansiedad: <span class="f-bold">{{$data->ansiedad_years===null||$data->ansiedad_years==0?"n":$data->ansiedad_years}}</span></td>
                  <td class="border-r">Tabaquismo: <span class="f-bold">{{$data->tabaquismo===null||$data->tabaquismo===0?"n":"s"}}</span></td>
                </tr>
                <tr>
                <td class="border-r">Cigarros/dia: <span class="f-bold">{{$data->cig_dia===null||$data->cig_dia===0?"n":$data->cig_dia}}</span></td>
                  <td class="border-r">Años fumando: <span class="f-bold">{{$data->cig_years===null||$data->cig_years===0?"n":$data->cig_years}}</span></td>
                  <td class="border-r">Abdonó Cig: <span class="f-bold">{{$data->cig_abandono===0||$data->cig_abandono===null?"n":"s"}}</span></td>
                  <td class="border-r">Años de abandono: <span class="f-bold">{{$data->cig_años_abandono===0||$data->cig_años_abandono===null?"n":$data->cig_años_abandono}}</span></td>
                </tr>
                <tr>
                    <td class="border-r">Actividad física: <span class="f-bold">{{$data->actividad_fis===0||$data->actividad_fis===null?"n":"s"}}</span></td>
                  <td class="border-r">Tipo A.F.: <span class="f-bold">{{$data->tipo_actividad===null||$data->tipo_actividad==0?"n":$data->tipo_actividad}}</span></td>
                  <td class="border-r">Hrs/sm: <span class="f-bold">{{$data->actividad_hrs_smn===null||$data->actividad_hrs_smn==0?"n":$data->actividad_hrs_smn}}</span></td>
                  <td class="border-r">Años practicando: <span class="f-bold">{{$data->actividad_years===null||$data->actividad_years==0?"n":$data->actividad_years}}</span></td>
                </tr>
              </tbody>
        </table>
        <div class="paciente mt-2">
            <div class="contenedor ">
              <h2 class="h5 titulo">Tratamiento</h2>
              <div class="linea-pu"></div>
            </div>
            <p  class="f-bold m-t-0 f-10 mb-1 bck-gray">Tratamiento: <span class="f-normal">{{ $paciente->medicamentos}}</span></p>
        </div>
        <div class="paciente mt-2 mb-0">
            <div class="contenedor ">
              <h2 class="h5 titulo">Clase Funcional</h2>
              <div class="linea-clase"></div>
            </div>
            <p  class="f-bold m-t-0 f-10 mb-0">NYHA: <span class="f-normal">{{ $data->cf_nyha}}</span> <span class="f-bold ml-5">  CCS: <span class="f-normal">{{$data->clase_f_ccs}}</span></span>
                <span class="f-bold ml-5">  DASI(METs): <span class="f-normal">{{round($data->dasi,2)}}</span></span></p>
        </div>
        <div class="contenedor mt-1">
            <h2 class="h5 titulo">Laboratorios</h2>
            <div class="linea-pu"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr class="">
                  <td class="border-r">Fecha BH: <span class="f-bold">{{$data->bh_fecha===null?"no tiene":date('d/m/Y',strtotime($data->bh_fecha))}}</span></td>
                  <td class="border-r">Hb: <span class="f-bold">{{$data->hb===null||$data->hb==0?"n":$data->hb}}</span></td>
                  <td class="border-r">Leuc: <span class="f-bold">{{$data->leucos===null||$data->leucos==0?"n":$data->leucos}}</span></td>
                  <td class="border-r">Plaq: <span class="f-bold">{{$data->plaquetas===null||$data->plaquetas==0?"n":$data->plaquetas}}</span></td>
                </tr>
                <tr>
                  <td class="border-r">Fecha QS: <span class="f-bold">{{$data->qs===null?"n":date('d/m/Y',strtotime($data->qs))}}</span></td>
                  <td class="border-r">Gluc.: <span class="f-bold">{{$data->glucosa===null||$data->glucosa==0?"n":$data->glucosa}}</span></td>
                  <td class="border-r">Cr.: <span class="f-bold">{{$data->creatinina===null||$data->creatinina==0?"n":$data->creatinina}}</span></td>
                  <td class="border-r">A. Úr.: <span class="f-bold">{{$data->ac_unico===null||$data->ac_unico===0?"n":$data->ac_unico}}</span></td>
                </tr>
                <tr>
                <td class="border-r">Col. : <span class="f-bold">{{$data->colesterol===null||$data->colesterol===0?"n":$data->colesterol}}</span></td>
                  <td class="border-r">LDL: <span class="f-bold">{{$data->ldl===null||$data->ldl===0?"n":$data->ldl}}</span></td>
                  <td class="border-r">HDL: <span class="f-bold">{{$data->hdl===0||$data->hdl===null?"n":$data->hdl}}</span></td>
                  <td class="border-r">TP: <span class="f-bold">{{$data->tp===0||$data->tp===null?"n":$data->tp}}</span></td>
                </tr>
                <tr>
                    <td class="border-r">INR: <span class="f-bold">{{$data->inr===0||$data->inr===null?"n":$data->inr}}</span></td>
                  <td class="border-r">TPT: <span class="f-bold">{{$data->tpt===null||$data->tpt==0?"n":$data->tpt}}</span></td>
                  <td class="border-r">Tg: <span class="f-bold">{{$data->trigliceridos===null||$data->trigliceridos==0?"n":$data->trigliceridos}}</span></td>
                  <td class="border-r">PCRas: <span class="f-bold">{{$data->pcras===null||$data->pcras==0?"n":$data->pcras}}</span></td>
                </tr>
                <tr>
                    <td class="border-r">Otros: <span class="f-bold">{{$data->otros_lab===0||$data->otros_lab===null?"n":$data->otros_lab}}</span></td>
                    <td class="border-r"></td>
                    <td class="border-r"></td>
                    <td class="border-r"></td>
                </tr>
              </tbody>
        </table>
        <div class="contenedor mt-2">
            <h2 class="h5 titulo">Electrocardiograma</h2>
            <div class="linea-elec"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr class="">
                  <td class="border-r">Fecha: <span class="f-bold">{{$data->ecg_fecha===null?"no tiene": date('d/m/Y',strtotime($data->ecg_fecha))}}</span></td>
                  <td class="border-r">Ritmo: <span class="f-bold">{{$data->ritmo===null?"n":$data->ritmo}}</span></td>
                  <td class="border-r">aP: <span class="f-bold">{{$data->aP===null||$data->aP==0?"n":$data->aP}}</span></td>
                  <td class="border-r">PR: <span class="f-bold">{{$data->pr===null||$data->pr===0?"n":$data->pr}} ms</span></td>
                </tr>
                <tr>
                  <td class="border-r"><span class="text-lft">Ondas Q:</span>Anterior/Septal: <span class="f-bold">{{$data->q_as===null||$data->q_as===0?"n":"s"}}</span></td>
                  <td class="border-r">FC: <span class="f-bold">{{$data->fc_ecog===null||$data->fc_ecog==0?"n":round($data->fc_ecog)}} lpm</span></td>
                  <td class="border-r">aQRS: <span class="f-bold">{{$data->aQRS===null||$data->aQRS==0?"n":$data->aQRS}}</span></td>
                  <td class="border-r">QRS: <span class="f-bold">{{$data->duracion_qrs===null||$data->duracion_qrs==0?"n":$data->duracion_qrs}} ms</span></td>
                </tr>
                <tr>
                <td class="border-r">Inferior: <span class="f-bold">{{$data->q_inf===null||$data->q_inf===0?"n":"s"}}</span></td>
                <td class="border-t"></td>
                  <td class="border-r">aT: <span class="f-bold">{{$data->aT===0||$data->aT===null?"n":$data->aT}}</span></td>
                  <td class="border-r">QTm: <span class="f-bold">{{$data->qtm===null||$data->qtm===0?"n":sprintf("%.2f", floor($data->qtm * 100) / 100)}} ms</span></td>
                </tr>
                <tr>
                    <td class="border-r">Lateral: <span class="f-bold">{{$data->q_lat===0||$data->q_lat===null?"n":"s"}}</span></td>
                    <td class="border-r"></td>
                  <td class="border-r"></td>
                  <td class="border-r">QTc: <span class="f-bold">{{$data->qtc===0||$data->qtc===null?"n":sprintf("%.2f", floor($data->qtc * 100) / 100);}} ms</span></td>
                </tr>
              </tbody>
        </table>
        <p class="f-bold f-10 mb-0">Otros: <span class="f-normal">{{$data->otros_ecg}}</span></p>
        <div class="contenedor mt-1">
            <h2 class="h5 titulo">Ecocardiografia</h2>
            <div class="linea-eco"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr class="">
                  <td class="border-r">Fecha: <span class="f-bold">{{$data->eco_fecha===null?"no tiene": date('d/m/Y',strtotime($data->eco_fecha))}}</span></td>
                  <td class="border-r">FE: <span class="f-bold">{{$data->fe_por===null?"n":$data->fe_por}}%</span></td>
                  <td class="border-r">Tapse: <span class="f-bold">{{$data->rel_e_a===null||$data->rel_e_a==0?"n":$data->rel_e_a}} mm</span></td>
                  <td class="border-r">SGL: <span class="f-bold">{{$data->dd_por===null||$data->dd_por==0?"n":$data->dd_por}} %</span></td>
                </tr>
                <tr>
                  <td class="border-r">Movilidad: <span class="f-bold">{{$data->trivi_por===null||$data->trivi_por===0?"n":$data->trivi_por}}</span></td>
                  <td class="border-r">PSAP: <span class="f-bold">{{$data->ds_por===null||$data->ds_por==0?"n":$data->ds_por}} mmHg</span></td>
                  <td class="border-r">Valvulopatía: <span class="f-bold">{{$data->valvulopatia===0||$data->valvulopatia===null?"n":"s"}}</span> </td>
                  <td class="border-r"></td>
                </tr>
              </tbody>
        </table>
        <p class="f-bold f-10 mb-0">Otros: <span class="f-normal">{{$data->otros_eco}}</span></p>
        <div class="contenedor mt-1">
            <h2 class="h5 titulo">Medicina Nuclear/IRM</h2>
            <div class="linea-irm"></div>
        </div>
        <p  class="f-bold m-t-0 f-10 mb-0">Fecha: <span class="f-normal">{{ $data->mn_fecha===null?"no tiene":date('d/m/Y',strtotime($data->mn_fecha))}}</span> <span class="f-bold ml-5">  FE: <span class="f-normal">{{$data->fe_por_mn}}</span></span>
            <span class="f-bold ml-5">  VRIE: <span class="f-normal">{{$data->vrie===null||$data->vrie===0?"n":"s"}}</span></span> <span class="f-bold ml-5">  VRIE Fecha: <span class="f-normal">{{$data->vrie_fcha===null?"n":date('d/m/Y',strtotime($data->vrie_fcha))}}</span></span></p>
            <table class="tabla text-lft border-t text-center mt-0">
                <thead class="border-t">
                  <tr>
                    <th></th>
                    <th class=" border-t">Pared Anterior</th>
                    <th class=" border-t">Pared Septal</th>
                    <th class=" border-t">Pared Inferior</th>
                    <th class="border-t">Pared Lateral</th>
                    <th>FEVI basal:</th>
                    <th>{{$data->fevi_basal===null?0:$data->fevi_basal}}%</th>
                  </tr>
                </thead >
                <tbody class="text-lft">
                  <tr>
                    <td scope=" border-b">IM</td>
                    <td class="border-t border-r text-ctr">{{$data->ant_im===null||$data->ant_im===0?"n":"s"}}</td>
                    <td class="border-t text-ctr">{{$data->sept_im===null||$data->sept_im===0?"n":"s"}}</td>
                    <td class="border-t text-ctr">{{$data->inf_im===null||$data->inf_im===0?"n":"s"}}</td>
                    <td class="border-t text-ctr">{{$data->lat_im===null||$data->lat_im===0?"n":"s"}}</td>
                    <td>FEVI basal:</td>
                    <td>{{$data->fevi_10_dobuta===null?0:$data->fevi_10_dobuta}}%</td>
                  </tr>
                  <tr>
                    <td scope="  border-b">Isquemia</td>
                    <td class="border-t border-r text-ctr">{{$data->ant_isq===null||$data->ant_isq===0?"n":"s"}}</td>
                    <td class="border-t text-ctr">{{$data->sept_isq===null||$data->sept_isq===0?"n":"s"}}</td>
                    <td class="border-t text-ctr">{{$data->inf_isq===null||$data->inf_isq===0?"n":"s"}}</td>
                    <td class="border-t text-ctr">{{$data->lat_isq===null||$data->lat_isq===0?"n":"s"}}</td>
                    <td>Reserv. Inot (abs):</td>
                    <td>{{$data->reserva_inot_absolut===null?0:$data->reserva_inot_absolut}}%</td>
                  </tr>
                  <tr>
                    <td scope=" border-b">R. Reversa</td>
                    <td class="border-t border-r text-ctr">{{$data->ant_rr===null||$data->ant_rr===0?"n":"s"}}</td>
                    <td class="border-t text-ctr">{{$data->sept_rr===null||$data->sept_rr===0?"n":"s"}}</td>
                    <td class="border-t text-ctr">{{$data->inf_rr===null||$data->inf_rr===0?"n":"s"}}</td>
                    <td class="border-t text-ctr">{{$data->lat_rr===null||$data->lat_rr===0?"n":"s"}}</td>
                    <td>Reserv. Inot (Relat):</td>
                    <td>{{$data->reserva_inot_relat===null?0:$data->reserva_inot_relat}}%</td>
                  </tr>
                </tbody>
            </table>
            <div class="contenedor mt-1">
                <h2 class="h5 titulo">Cateterismo</h2>
                <div class="linea-pu"></div>
            </div>
            <p  class="f-bold m-t-0 f-10 mb-0 bck-gray">Fecha: <span class="f-normal">{{ $data->catet_fecha===null?"no tiene":date('d/m/Y',strtotime($data->catet_fecha))}}</span> <span class="f-bold ml-5">  FE: <span class="f-normal">{{$data->catet_fe===null?0:$data->catet_fe}}</span></span>
                <span class="f-bold ml-5">  D2VI: <span class="f-normal">{{$data->catet_d2vi===null||$data->catet_d2vi===0?"n":$data->catet_d2vi}}</span></span> <span class="f-bold ml-5">  Tronco: <span class="f-normal">{{$data->catet_tco===null?0:$data->catet_tco}}</span></span></p>
                <table class="tabla text-lft border-t text-center mt-0 bck-gray">
                    <tbody class="text-lft ">
                      <tr>
                        <td scope=" border-b">Descendiente anterior</td>
                        <td class="border-t border-r text-ctr">DA(prox): {{$data->catet_da_prox===null||$data->catet_da_prox===0?"n":$data->catet_da_prox}}%</td>
                        <td class="border-t text-ctr">DA(1/2): {{$data->catet_da_med===null||$data->catet_da_med===0?"n":$data->catet_da_med}}%</td>
                        <td class="border-t text-ctr">DA(distal): {{$data->catet_da_dist===null||$data->catet_da_dist===0?"n":$data->catet_da_dist}}%</td>
                        <td class="border-t text-ctr">1aD: {{$data->catet_1a_d===null||$data->catet_1a_d===0?"n":$data->catet_1a_d}}%</td>
                        <td class="border-t text-ctr">2aD: {{$data->catet_1a_d===null||$data->catet_1a_d===0?"n":$data->catet_1a_d}}%</td>
                        <td class="border-t text-ctr">Otro: {{$data->catet_otros}}</td>
                      </tr>
                      <tr>
                        <td scope="  border-b">Circunfleja</td>
                        <td class="border-t border-r text-ctr">Cx(prox): {{$data->catet_cx_prox===null||$data->catet_cx_prox===0?"n":$data->catet_cx_prox}}%</td>
                        <td class="border-t text-ctr">Cx(distal): {{$data->catet_cx_dist===null||$data->catet_cx_dist===0?"n":$data->catet_cx_dist}}%</td>
                        <td class="border-t text-ctr">OM: {{$data->catet_om===null||$data->catet_om===0?"n":$data->catet_om}}%</td>
                        <td class="border-t text-ctr">PL: {{$data->catet_pl===null||$data->catet_pl===0?"n":$data->catet_pl}}%</td>
                        <td class="border-t text-ctr"></td>
                        <td class="border-t text-ctr"></td>
                      </tr>
                      <tr>
                        <td scope=" border-b">Coronaria Derecha</td>
                        <td class="border-t border-r text-ctr">CD(prox): {{$data->catet_cd_aprox===null||$data->catet_cd_aprox===0?"n":$data->catet_cd_aprox}}%</td>
                        <td class="border-t text-ctr">CD(1/2): {{$data->catet_cd_med===null||$data->catet_cd_med===0?"n":$data->catet_cd_med}}%</td>
                        <td class="border-t text-ctr">CD(distal): {{$data->catet_cd_dist===null||$data->catet_cd_dist===0?"n":$data->catet_cd_dist}}%</td>
                        <td class="border-t text-ctr">Ramo V Izq: {{$data->catet_r_vent_izq===null||$data->catet_r_vent_izq===0?"n":$data->catet_r_vent_izq}}%</td>
                        <td class="border-t text-ctr">DP: {{$data->catet_dp===null||$data->catet_dp===0?"n":$data->catet_dp}}%</td>
                        <td class="border-t text-ctr"></td>
                      </tr>
                    </tbody>
                </table>
                <p class="f-bold f-15 mb-0">Estudios: <span class="f-normal">{{$data->estudios}}</span></p>
                <p class="f-bold f-15 mb-0">Plan: <span class="f-normal">{{$data->plan}}</span></p>
                <span class="f-bold mt-0 mb-0 f-15">Realizó:</span><span class="f-15 ml-2">Dr.  {{$user->nombre . "   " . $user->apellidoPat}}</span>
    </main>
  </body>   
</html>