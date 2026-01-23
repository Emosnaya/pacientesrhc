
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
        font-size: 9.5px;
        text-align: center;
        width: 100%; /* Espacio entre línea y texto */
    }
        .tabla{
            font-size: 8.5px;
            margin-bottom: 0;
            width: 100%
        }
        .f-10{
          font-size: 10px;
        }
        .f-15{
          font-size: 15px;
        }
        .f-7{
          font-size: 7px;
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
  .m-t-07{
    margin-top: -0.7rem;
  }
  .m-t-0{
    margin-top: -1rem;
  }

  .linea-bott{
    border-bottom: 1px solid black
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
    top: -0.2rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-pu {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 7.2rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: -0.2rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
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
  .backgr-black{
    background-color: #000
  }
  .bck-gray{
    background-color: #DDDEE1  ;
  }
        .container {
            width: 800px;
            margin-top: 15px;
            position: relative;
            background: #fff;
            padding-left: 60px; /* Aumenta el espacio a la izquierda */
            padding-right: 60px; /* Aumenta el espacio a la derecha */
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .section {
            border: 1px solid #000;
            position: absolute;
            padding: 5px;
            background: #fff;
            margin-bottom: 20px; /* Espacio entre cajas */
        }

        /* Líquidos */
        .liquidos {
            top: 10px;
            left:  0;
            width: 220px;
            height: 130px;
            bottom: 15px;
        }

        /* Presión Arterial */
        .presion {
            top: 10px;
            right: 20px;
            width: 220px;
            height: 90px;
        }

        /* Comidas */
        .comidas {
            top: 160px;
            left: 0;
            width: 220px;
            height: 60px;
        }

        /* Cintura */
        .cintura {
            top: 120px;
            right: 20px;
            width: 220px;
            height: 70px;
        }

        /* Actividad Física */
        .actividad {
            top: 160px;
            left: 0;
            width: 220px;
            height: 130px;
        }

        /* IMC */
        .imc {
            top: 210px;
            right: 20px;
            width: 220px;
            height: 130px;
        }

        /* Medicamentos */
        .medicamentos {
            top: 310px;
            left: 0;
            width: 220px;
            height: 160px;
        }

        /* Indicadores adicionales */
        .indicadores {
            top: 360px;
            right: 20px;
            width: 220px;
            height: 180px;
        }

        /* Icono central */
        .icono-central {
            position: absolute;
            margin: 0 auto;
            height: 6px;
            width: 4px;
            left: 240px;
            top: 240px

        }


        h3 {
            font-size: 14px;
            margin: 5px 0;
            text-align: center;
            background-color: #007bff;
            color: #fff;
        }

        p {
            font-size: 12px;
            margin: 5px 0;
        }
        .container-diag {
          margin-top: 52rem;
          width: 90%;
}

/* Sección con título */
.section-diag {
    margin-bottom: 12px; /* Espacio entre secciones */
    border: 1px solid #ccc;
}

.section-title {
    background-color: #3b82f6; /* Azul */
    color: white;
    font-weight: bold;
    padding: 1px;
    text-align: left;
}

/* Líneas entre los párrafos */
.section-content p {
    margin: 10px 0;
    text-align: justify;
    border-bottom: 1px solid #ccc;
    padding-bottom: 5px;
}

/* Último párrafo sin línea */
.section-content p:last-child {
    border-bottom: none;
}
.diet-section {
    margin: 20px 0; /* Espacio arriba y abajo */
    overflow: hidden; /* Asegura que los flotantes no rompan el contenedor */
    border: 1px solid #ccc;
}

.diet-title {
    background-color: #3b82f6; /* Azul similar */
    color: white;
    font-weight: bold;
    padding: 10px;
    text-align: left;
    margin-top: 20px;
}

.marg-5{
  margin-top: 20px;
}

.diet-item {
    overflow: hidden;
    margin: 10px 0;
    border-bottom: 1px solid #ccc; /* Línea separadora */
    padding-bottom: 10px;
}

.diet-item:last-child {
    border-bottom: none; /* Quitar línea del último elemento */
}

.diet-icon img {
    max-width: 80%; /* Tamaño de la imagen */
    max-height: 80%;
}

.diet-content {
    overflow: hidden; /* Evita que el texto se rompa con el flotante */
    font-size: 14px;
}

.diet-content strong {
    font-weight: bold;
    color: #333;
}

.paciente-container {
    width: 100%;
    overflow: hidden;
    border-bottom: 1px solid #ccc; /* Limpia flotantes */
}

.paciente-info {
    float: left; /* Columna izquierda */
    width: 30%; /* Ocupa el 30% del contenedor */
}

.paciente-content {
    float: left; /* Columna derecha */
    width: 68%; /* Ocupa el 70% restante */
    text-align: justify;
}

/* Opcional: Limpiar flotantes después del contenedor */
.paciente-container::after {
    content: "";
    display: table;
    clear: both;
}
.marg-final{
    margin-top: 12rem
  }
  .medio{
      position: relative;
    }

    .txt-blue{
    color: #255FA5;
  }
  .txt-r{
    color: #FB0006;
  }
    </style>
  </head>
  <body>
    <header class="mb-0">
      <div class="paciente ma-t-0 mb-0">
        <p class="f-bold f-15 text-center mb-0 mt-0">Valoración Nutricional</p>
        <img src="{{ $clinicaLogo }}" alt="logo clínica" style="height: 90px" class="">
        <div class="medio">
          <p class=" texto-izquierda mb-0 f-bold">Fecha: {{ date('d/m/Y',strtotime($data->created_at))}} </p>
        </div>
          <p class="f-bold mt-4 mb-0">Registro: {{$paciente->registro}}</p>
          <p  class="f-bold mt-0 mb-0 text-2xl">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre}}</span>
            <span class="f-bold ml-2">  Edad: <span  class="f-normal">{{$paciente->edad}}</span></span>
            <span class="f-bold ml-2">  Género: <span  class="f-normal">{{($paciente->genero==1?"Hombre":"Mujer")}}</span></span>
            <span class="f-bold">  Correo: <span  class="f-normal">{{$paciente->email}}</span></span>
            <span class="f-bold ml-2">  Teléfono: <span  class="f-normal">{{($paciente->telefono)}}</span></span>
          </p>
          <p class=" texto-izquierda mb-0 f-bold mt-0">Diagnóstico: <span class="f-normal">{{$paciente->diagnostico}}</span> </p>
      </div>
    </header>
    <div class="container mt-4">
      <!-- Bloques de información -->
      <div class="section liquidos">
          <h3>Recordatorio de 24h</h3>
          <p>{{$data->recomendacion ?? 'Sin recordatorio.'}}</p>
      </div>

      <div class="section presion">
          <h3>Presión arterial</h3>
          <p class="ml-2 mt-2 font-bold"><span class="linea-bott">{{$data->sistolica}}</span> / <span class="linea-bott mr-2">{{$data->diastolica}}</span>  mmHG</p>
      </div>

      <div class="section cintura">
          <h3>Cintura</h3>
          <p class="ml-2 mt-2"><span  class="f-normal linea-bott">{{$paciente->cintura}}</span> cm</p>
      </div>

      <div class="section actividad">
          <h3>Actividad</h3>
          <p>Actividad física: <span class="linea-bott">{{$data->actividad ?? 'No'}}</span></p>
          <p>Días por semana: <span class="linea-bott">{{$data->actividadDias ?? 0}}</span></p>
          <p>Minutos al día: <span class="linea-bott">{{$data->minutosDia ?? 0}}</span></p>
          <p>Fórmula: <span class="linea-bott">{{$data->formula ?? 'N/A'}}</span></p>
      </div>

      <div class="section imc">
          <h3>Índice de masa corporal</h3>
          <p>Peso: <span  class="f-normal linea-bott">{{$paciente->peso}}</span> kg</p>
          <p>Talla: <span class="f-normal linea-bott">{{$paciente->talla}}</span> cm</p>
          <p>IMC: <span  class="f-normal linea-bott">{{round($paciente->imc,2)}}</span></p>
          <p>Estado: <span class="linea-bott">{{$data->estado ?? 'N/A'}}</p>
      </div>

      <div class="section medicamentos">
          <h3>Medicamentos</h3>
          <p>Control de glucosa: @if ($data->Controlglucosa === "1" || $data->Controlglucosa === "true") <span class="f-normal linea-bott">Si</span> @else <span class="f-normal linea-bott">No</span> @endif</p>
          <p>Control de lípidos: @if ($data->lipidos === "1" || $data->lipidos === "true") <span class="f-normal linea-bott">Si</span> @else <span class="f-normal linea-bott">No</span> @endif</p>
          <p>Control de peso: @if ($data->controlPeso === "1" || $data->controlPeso === "true") <span class="f-normal linea-bott">Si</span> @else <span class="f-normal linea-bott">No</span> @endif</p>
          <p>Control de presión: @if ($data->controlPresion === "1" || $data->controlPresion === "true") <span class="f-normal linea-bott">Si</span> @else <span class="f-normal linea-bott">No</span> @endif</p>
      </div>

      <div class="section indicadores">
          <h3>Indicadores</h3>
          <p>Glucosa: <span  class="f-normal linea-bott">{{$data->glucosa ?? 0}}</span></p>
          <p>Triglicéridos: <span  class="f-normal linea-bott">{{$data->trigliceridos ?? 0}}</span></p>
          <p>HDL: <span  class="f-normal linea-bott">{{$data->hdl ?? 0}}</span></p>
          <p>Colesterol total: <span  class="f-normal linea-bott">{{$data->colesterol ?? 0}}</span></p>
          <p>LDL: <span  class="f-normal linea-bott">{{$data->ldl ?? 0}}</span></p>
          <p>Otro: @if ($data->otro) <span class="f-normal">{{$data->otro}}</span>  @else <span class="f-normal">N/A</span> @endif</p>
      </div>

      @if ($paciente->genero==1)
      <img src="img/person-solid.svg" class="icono-central" alt="">
      @else
      <img src="img/person-dress-solid.svg" class="icono-central" alt="">
      @endif
  </div>
  <div class="container-diag ml-4">
    <!-- Sección Diagnóstico -->
    <div class="section-diag">
        <div class="section-title text-xl">Diagnóstico</div>
        <div class="section-content text-md">
            <p class="ml-2">@if ($data->diagnostico==="1")
              <img src="img/check-solid-black.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
            @else __  
            @endif Paciente en Obesidad que cumple los criterios armonizados para Síndrome Metabólico.</p>
            <p class="ml-2">@if ($data->diagnostico==="2")
              <img src="img/check-solid-black.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
            @else __  
            @endif Paciente en Sobrepeso que cumple los criterios armonizados para Síndrome Metabólico. Paciente en Sobrepeso sin Síndrome Metabólico.</p>
            <p  class="ml-2">@if ($data->diagnostico==="3")
              <img src="img/check-solid-black.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
            @else __ 
            @endif Paciente en Sobrepeso sin Síndrome Metabólico.</p>
            <p  class="ml-2">@if ($data->diagnostico==="4")
              <img src="img/check-solid-black.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
            @else __  
            @endif Paciente en Obesidad sin Síndrome Metabólico.</p>
            <p  class="ml-2">@if ($data->diagnostico==="5")
              <img src="img/check-solid-black.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
            @else __
            @endif Paciente en Normopeso.</p>
            <p  class="ml-2">@if ($data->diagnostico==="6")
              <img src="img/check-solid-black.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
            @else __ 
            @endif Paciente en Normopeso que cumple los criterios armonizados para Síndrome Metabólico.</p>
            <p  class="ml-2">@if ($data->diagnostico==="7")
              <img src="img/check-solid-black.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
            @else __ 
            @endif Paciente en Infrapeso, se recomienda visita subsecuente con Nutrición para descartar desnutrición.</p>
            <p  class="ml-2">@if ($data->diagnostico==="8")
              <img src="img/check-solid-black.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
            @else __  
            @endif Paciente en Obesidad Morbida.</p>
        </div>
    </div>

    <div class="section-diag">
      <div class="section-title text-xl">Observaciones</div>
      <div class="section-content ">
          <p class="ml-1 p-2">{{$data->observaciones ?? 'Sin Observaciones.'}}</p>
      </div>
  </div>

    <!-- Sección Recomendaciones específicas -->
    <div class="section-diag">
      <div class="section-title">Recomendaciones específicas</div>
      @php
          // Decodifica las recomendaciones almacenadas como JSON
          $recomendaciones = json_decode($data->recomendaciones);
      @endphp
      <div class="section-content">
          <!-- Recomendación 1 -->
          <p class="ml-2">
              @if (isset($recomendaciones[0]) && $recomendaciones[0] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              Evitar el consumo de azúcares (azúcar de mesa, mascabado, mieles, mermeladas, cajeta, lechera) así como productos con azúcares simples (jugos, refrescos, yogurt con frutas, postres, etc.).
          </p>
  
          <!-- Recomendación 2 -->
          <p class="ml-2">
              @if (isset($recomendaciones[1]) && $recomendaciones[1] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              Evitar el consumo de bebidas azucaradas e intercambiarlo por agua simple.
          </p>
  
          <!-- Recomendación 3 -->
          <p class="ml-2">
              @if (isset($recomendaciones[2]) && $recomendaciones[2] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              Formar horarios para las tres principales comidas del día.
          </p>
  
          <!-- Recomendación 4 -->
          <p class="ml-2">
              @if (isset($recomendaciones[3]) && $recomendaciones[3] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              Realizar 3 comidas principales al día.
          </p>
  
          <!-- Recomendación 5 -->
          <p class="ml-2">
              @if (isset($recomendaciones[4]) && $recomendaciones[4] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              Aumentar el consumo de agua simple (la sed es la señal más confiable de que necesita agua).
          </p>
  
          <!-- Recomendación 6 -->
          <p class="ml-2">
              @if (isset($recomendaciones[5]) && $recomendaciones[5] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              Mantener actividad física.
          </p>
  
          <!-- Recomendación 7 -->
          <p class="ml-2">
              @if (isset($recomendaciones[6]) && $recomendaciones[6] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              Iniciar actividad física moderada-ligera (caminar, trotar, bicicleta, natación, etc.) por al menos 30 minutos al día 5 días a la semana o el equivalente a 150 minutos.
          </p>
  
          <!-- Recomendación 8 -->
          <p class="ml-2">
              @if (isset($recomendaciones[7]) && $recomendaciones[7] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              Aumentar actividad física moderada-ligera (caminata, trotar, bicicleta, natación, etc.) por al menos 30 minutos al día 5 días a la semana o el equivalente a 150 minutos a la semana o 75 minutos a la semana de actividad vigorosa (correr, tenis, natación continua, bicicleta de subida) a la semana.
          </p>
  
          <!-- Recomendación 9 -->
          <p class="ml-2">
              @if (isset($recomendaciones[8]) && $recomendaciones[8] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              A la hora de la comida elegir una opción entre arroz, frijoles, pasta o papa como acompañamiento.
          </p>
  
          <!-- Recomendación 10 -->
          <p class="ml-2">
              @if (isset($recomendaciones[9]) && $recomendaciones[9] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              Disminuir la cantidad de cereales (arroz, tortilla, pan, papa, pasta).
          </p>
  
          <!-- Recomendación 11 -->
          <p class="ml-2">
              @if (isset($recomendaciones[10]) && $recomendaciones[10] === true)
                  <img src="img/check-solid.svg" alt="" style="height: 10px" class="font-light linea-bott mr-2">
              @else
                  __
              @endif
              Siempre y cuando realice 1 hora o más de ejercicio: consumir una colación antes de la actividad y otra después.
          </p>
      </div>
  </div>
    <br>
    <br>
    <br>
    <div class="">
    <div class="diet-title">Una dieta balanceada contiene</div>
    <div class="paciente paciente-container mb-1">
      <!-- Columna izquierda: Título -->
      <div class="paciente-info">
          <img src="img/icon-proteina.png" alt="Proteína">
      </div>
  
      <!-- Columna derecha: Contenido -->
      <div class="paciente-content">
          <p class="mb-0 text-justify">
            <strong>Proteína:</strong> Variedad de productos proteicos: todo tipo de carnes, leguminosas (frijol, garbanzo, lenteja, soya, habas), bajo en grasas, es decir, sin freír ni empanizar.
          </p>
      </div>
    </div>
    <div class="paciente paciente-container mt-1 mb-1">
      <!-- Columna izquierda: Título -->
      <div class="paciente-info">
        <div class="diet-icon">
          <img src="img/icon-lacteos.png" alt="lacteos">
      </div>
      </div>
  
      <!-- Columna derecha: Contenido -->
      <div class="paciente-content">
          <p class="mt-1 mb-0 text-justify">
            <strong>Lácteos:</strong> Libres o bajos en grasa.
          </p>
      </div>
    </div>
    <div class="paciente paciente-container mt-1 mb-1">
      <!-- Columna izquierda: Título -->
      <div class="paciente-info">
        <div class="diet-icon">
          <img src="img/icon-aceites.png" alt="Proteína">
      </div>
      </div>
  
      <!-- Columna derecha: Contenido -->
      <div class="paciente-content">
          <p class="mt-1 mb-0 text-justify">
            <strong>Aceites y grasas:</strong> Vegetales evitando freír los alimentos.
          </p>
      </div>
    </div>
    <div class="paciente paciente-container mt-1 mb-1">
      <!-- Columna izquierda: Título -->
      <div class="paciente-info">
        <div class="diet-icon">
          <img src="img/icon-cereales.png" alt="Proteína">
      </div>
      </div>
  
      <!-- Columna derecha: Contenido -->
      <div class="paciente-content">
          <p class="mt-1 mb-0 text-justify">
            <strong>Cereales:</strong> Granos y derivados (pan, pasta, avena, arroz, cereal, tortilla, galletas sin azúcar) en cantidades moderadas.
          </p>
      </div>
    </div>
    <div class="paciente paciente-container mt-1 mb-1">
      <!-- Columna izquierda: Título -->
      <div class="paciente-info">
        <div class="diet-icon">
          <img src="img/icon-frutas.png" alt="Proteína">
      </div>
      </div>
  
      <!-- Columna derecha: Contenido -->
      <div class="paciente-content">
          <p class="mt-1 mb-0 text-justify">
            <strong>Frutas:</strong> Especialmente frutas frescas y variadas en color.
          </p>
      </div>
    </div>
    <div class="paciente paciente-container mt-1 mb-1">
      <!-- Columna izquierda: Título -->
      <div class="paciente-info">
        <div class="diet-icon">
          <img src="img/icon-vegetales.png" alt="Proteína">
      </div>
      </div>
  
      <!-- Columna derecha: Contenido -->
      <div class="paciente-content">
          <p class="mt-1 mb-0 text-justify">
            <strong>Vegetales:</strong> Frescos preferentemente en todas sus variedades, hojas verdes, rojos, naranjas, fibrosos, etc.
          </p>
      </div>
    </div>
  </div>

  <div class="paciente mt-5">
    <p  class="f-15 f-bold mb-0">Nutriólogo: <span>{{$data->nutriologo}}</span></p>
    <p  class="f-15 f-bold mb-0">Cédula: <span>{{$data->cedula_nutriologo}}</span></p>
  </div>  
  <div class="medio marg-final">
  <p class=" texto-izquierda mb-0 f-bold f-15 txt-blue marg-final">CERCAP</p> <span class="ml-5 text-right texto-derecha f-bold marg-final">5526255547/<span class="f-normal txt-r marg-final">cercapmx</span></span>
  </div> 


  </body>
</html>