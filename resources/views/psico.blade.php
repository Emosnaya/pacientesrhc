
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
        .text-jst{
            text-align: justify;
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
  .linea-mo {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 10.5rem; /* Comienza desde el borde izquierdo del contenedor */
    right: 0;
    top: -0.2rem; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-bottom: 3px solid black; /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .linea-t {
    position: absolute; /* Posicionamiento absoluto con respecto al contenedor */
    left: 0; /* Comienza desde el borde izquierdo del contenedor */
    right: 0; /* Termina en el borde derecho del contenedor */ /* Posiciona en el centro verticalmente */ /* Ajusta verticalmente para alinear con el texto */
    border-top: 2px solid rgb(83, 78, 78); /* Línea sólida negra */
    z-index: 0; /* Detrás del título */
  }
  .backgr-black{
    background-color: #000
  }
  .bck-gray{
    background-color: #DDDEE1  ;
  }
  .paciente-container {
    width: 100%;
    overflow: hidden; /* Limpia flotantes */
}

.paciente-info {
    float: left; /* Columna izquierda */
    width: 30%; /* Ocupa el 30% del contenedor */
    font-weight: bold;
}

.paciente-content {
    float: left; /* Columna derecha */
    width: 70%; /* Ocupa el 70% restante */
    text-align: justify;
}

/* Opcional: Limpiar flotantes después del contenedor */
.paciente-container::after {
    content: "";
    display: table;
    clear: both;
}
/* Estilo para la tabla */
.tabla-cuestionario {
    width: 100%;
    border-collapse: collapse; /* Para que las celdas compartan borde */
    font-family: Arial, sans-serif;
    font-size: 12px; /* Reducir aún más el tamaño de la fuente */
}

.marg-8{
  margin-top: 12rem;
}

.tabla-cuestionario th, .tabla-cuestionario td {
    border: 1px solid #ccc;
    padding: 2px 4px; /* Reducir aún más el padding */
    text-align: left;
}

.tabla-cuestionario th {
    background-color: #f4f4f4;
    font-weight: bold;
}

.tabla-cuestionario td {
    background-color: #f9f9f9;
}

.tabla-cuestionario td[rowspan] {
    background-color: #e9e9e9;
    font-weight: bold;
}

/* Asegura que las filas con rowspan no se vean cortadas */
.tabla-cuestionario tr td:last-child {
    border-right: 2px solid #ccc;
}

html {
  margin-top: 0;
  padding-top: 0;
}
.marg-final{
    margin-top: 7rem
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
        <p class="f-bold f-15 text-center mb-0 mt-0">Nota Psicológica</p>
        <img src="{{ $clinicaLogo }}" alt="logo clínica" style="height: 90px" class="">
        <div class="medio">
          <p class=" texto-izquierda mb-0 f-bold">Fecha: {{ date('d/m/Y',strtotime($data->created_at))}} </p> <span class="ml-5 text-right texto-derecha f-bold">Registro: {{$paciente->registro}}</span>
        </div>
        <br>
          <p  class="f-bold mt-2 mb-0 text-xl ">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre}}</span><span class="f-bold ml-2">  Peso : <span class="f-normal">{{$paciente->peso}}</span></span> <span class=f-bold"">  Talla: <span  class="f-normal">{{$paciente->talla}}</span></span>
            <span class="f-bold ml-2">  Edad: <span  class="f-normal">{{$paciente->edad}}</span></span> <span class="f-bold">  Correo: <span  class="f-normal">{{$paciente->email}}</span></span>
            <span class="f-bold ml-2">  Género: <span  class="f-normal">{{($paciente->genero==1?"Hombre":"Mujer")}}</span></span><span class="f-bold ml-2">  Teléfono: <span  class="f-normal">{{($paciente->telefono)}}</span></span></p>
          <p class="f-bold mt-0 mb-0">  Medicamentos: <span  class="f-normal">{{$paciente->medicamentos}}</span> </p>
          <div class="linea-t mt-1"></div>
      </div>
    </header>
    <main class="mt-3">
      <div class="paciente paciente-container mt-1 mb-1">
        <!-- Columna izquierda: Título -->
        <div class="paciente-info">
            <h2 class="h6 titulo mb-1">Motivo de Consulta:</h2>
        </div>
    
        <!-- Columna derecha: Contenido -->
        <div class="paciente-content">
            <p class="mt-1 mb-0 text-justify">
                {{$data->motivo_consulta ?? 'Sin Observaciones registradas.'}}
            </p>
        </div>
      </div>
      <div class="linea-t mt-0 mb-0"></div>
      <div class="paciente paciente-container mt-1 mb-1">
        <!-- Columna izquierda: Título -->
        <div class="paciente-info">
            <h2 class="h6 titulo mb-1">Antecedentes Médicos:</h2>
        </div>
    
        <!-- Columna derecha: Contenido -->
        <div class="paciente-content">
            <p class="mt-1 mb-0 text-justify">
                {{$data->antecedentes_medicos ?? 'Sin Observaciones registradas.'}}
            </p>
        </div>
      </div>
      <div class="linea-t mt-0 mb-0"></div>
      <div class="paciente paciente-container mt-1 mb-1">
        <!-- Columna izquierda: Título -->
        <div class="paciente-info">
            <h2 class="h6 titulo mb-1">Cirugías Previas:</h2>
        </div>
    
        <!-- Columna derecha: Contenido -->
        <div class="paciente-content">
            <p class="mt-1 mb-0 text-justify">
                {{$data->cirugias_previas ?? 'Sin Observaciones registradas.'}}
            </p>
        </div>
      </div>
      <div class="linea-t mt-0 mb-0"></div>
      <div class="paciente paciente-container mt-1 mb-1">
        <!-- Columna izquierda: Título -->
        <div class="paciente-info">
            <h2 class="h6 titulo mb-1">Tratamiento Actual:</h2>
        </div>
    
        <!-- Columna derecha: Contenido -->
        <div class="paciente-content">
            <p class="mt-1 mb-0 text-justify">
                {{$data->tratamiento_actual ?? 'Sin Observaciones registradas.'}}
            </p>
        </div>
      </div>
      <div class="linea-t mt-0 mb-0"></div>
      <div class="paciente paciente-container mt-1 mb-1">
        <!-- Columna izquierda: Título -->
        <div class="paciente-info">
            <h2 class="h6 titulo mb-1">Antecedentes Familiares:</h2>
        </div>
    
        <!-- Columna derecha: Contenido -->
        <div class="paciente-content">
            <p class="mt-1 mb-0 text-justify">
                {{$data->antecedentes_familiares ?? 'Sin Observaciones registradas.'}}
            </p>
        </div>
      </div>
      <div class="linea-t mt-0 mb-0"></div>
      <div class="paciente paciente-container mt-1 mb-1">
        <!-- Columna izquierda: Título -->
        <div class="paciente-info">
            <h2 class="h6 titulo mb-1">Aspectos Sociales:</h2>
        </div>
    
        <!-- Columna derecha: Contenido -->
        <div class="paciente-content">
            <p class="mt-1 mb-0 text-justify">
                {{$data->aspectos_sociales ?? 'Sin Observaciones registradas.'}}
            </p>
        </div>
      </div>
      <div class="linea-t mt-0 mb-0"></div>
      <div class="paciente paciente-container mt-1 mb-1">
        <!-- Columna izquierda: Título -->
        <div class="paciente-info">
            <h2 class="h6 titulo mb-1">Escalas Utilizadas:</h2>
        </div>
    
        <!-- Columna derecha: Contenido -->
        <div class="paciente-content">
            <p class="mt-1 mb-0 text-justify">
                {{$data->escalas_utilizadas ?? 'Sin Observaciones registradas.'}}
            </p>
        </div>
      </div>
      <div class="linea-t mt-0 mb-0"></div>
      <div class="paciente paciente-container mt-1 mb-1">
        <!-- Columna izquierda: Título -->
        <div class="paciente-info">
            <h2 class="h6 titulo mb-1">Síntomas Actuales:</h2>
        </div>
    
        <!-- Columna derecha: Contenido -->
        <div class="paciente-content">
            <p class="mt-1 mb-0 text-justify">
                {{$data->sintomas_actuales ?? 'Sin Observaciones registradas.'}}
            </p>
        </div>
      </div>
      <div class="linea-t mt-0 mb-0"></div>
      <div class="paciente paciente-container mt-1 mb-1">
        <!-- Columna izquierda: Título -->
        <div class="paciente-info">
            <h2 class="h6 titulo mb-1">Plan de Tratamiento:</h2>
        </div>
    
        <!-- Columna derecha: Contenido -->
        <div class="paciente-content">
            <p class="mt-1 mb-0 text-justify">
                {{$data->plan_tratamiento ?? 'Sin Observaciones registradas.'}}
            </p>
        </div>
      </div>
      <div class="linea-t mt-0 mb-0"></div>
      <div class="paciente paciente-container mt-1 mb-1">
        <!-- Columna izquierda: Título -->
        <div class="paciente-info">
            <h2 class="h6 titulo mb-1">Seguimiento:</h2>
        </div>
    
        <!-- Columna derecha: Contenido -->
        <div class="paciente-content">
            <p class="mt-1 mb-0 text-justify">
                {{$data->seguimiento ?? 'Sin Observaciones registradas.'}}
            </p>
        </div>
      </div>
      <div class="linea-t mt-0 mb-0"></div>
      <p class="f-bold f-15 text-center mb-0 marg-8">Evaluación de Calidad de vida</p> 
      <table class="tabla-cuestionario mt-5">
        <thead>
          <tr>
            <th>Sección</th>
            <th>Pregunta</th>
            <th>Respuesta</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td rowspan="4">Salud Física</td>
            <td>¿Cómo calificarías tu estado de salud general?</td>
            <td>@if($data->calif_salud === "1") <p class="f-bold mb-0 "> Excelente </p>
              @elseif ($data->calif_salud === "2")<p class="f-bold mb-0 "> Bueno </p>
              @elseif ($data->calif_salud === "3")<p class="f-bold mb-0 "> Regular </p>
              @elseif ($data->calif_salud === "4")<p class="f-bold mb-0 "> Malo </p>
               @endif</td>
          </tr>
          <tr>
            <td>¿Realizas ejercicio físico regularmente?</td>
            <td>@if($data->realizas_ejercicio === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td>Si es así, ¿con qué frecuencia?</td>
            <td>@if($data->ejercicio_frecuencia === "1") <p class="f-bold mb-0 "> Diariamente </p>
              @elseif ($data->ejercicio_frecuencia === "2")<p class="f-bold mb-0 "> 3-4 veces por semana </p>
              @elseif ($data->ejercicio_frecuencia === "3")<p class="f-bold mb-0 "> 1-2 veces por semana </p>
              @elseif ($data->ejercicio_frecuencia === "4")<p class="f-bold mb-0 "> Raramente </p>
              @else <p class="f-bold mb-0 "> N/A </p>
               @endif</td>
          </tr>
          <tr>
            <td>¿Tienes alguna condición médica crónica?</td>
            <td>@if($data->condicion_medica === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>

          <tr>
            <td rowspan="3">Alimentación</td>
            <td>¿Cómo describirías tu dieta diaria?</td>
            <td>@if($data->dieta_diaria === "1") <p class="f-bold mb-0 "> Equilibrada </p>
              @elseif ($data->dieta_diaria === "2")<p class="f-bold mb-0 "> Altas en grasas </p>
              @elseif ($data->dieta_diaria === "3")<p class="f-bold mb-0 "> Alta de azucares </p>
              @elseif ($data->dieta_diaria === "4")<p class="f-bold mb-0 "> Insuficiente </p>
              @else <p class="f-bold mb-0 "> N/A </p>
               @endif</td>
          </tr>
          <tr>
            <td>¿Comes frutas y verduras diariamente?</td>
            <td>@if($data->frutas_verduras === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td>¿Con qué frecuencia comes fuera de casa?</td>
            <td>@if($data->frecuencia_comida === "1") <p class="f-bold mb-0 "> Diariamente </p>
              @elseif ($data->frecuencia_comida === "2")<p class="f-bold mb-0 "> Semanalmente </p>
              @elseif ($data->frecuencia_comida === "3")<p class="f-bold mb-0 "> Raramente </p>
              @else <p class="f-bold mb-0 "> N/A </p>
               @endif</td>
          </tr>
          <tr>
            <td rowspan="3">Salud Mental y Emocional</td>
            <td>En una escala del 1 al 10, ¿cómo calificarías tu nivel de estrés actual?</td>
            <td>{{$data->estres_nivel ?? 'N/A'}}</td>
          </tr>
          <tr>
            <td>¿Te sientes feliz la mayor parte del tiempo?</td>
            <td>@if($data->feliz === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td>¿Tienes apoyo emocional de amigos o familiares?</td>
            <td>@if($data->apoyo_emocional === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td rowspan="3">Vida Social</td>
            <td>¿Con qué frecuencia te reúnes con amigos o familiares?</td>
            <td>@if($data->frecuencia_reuniones === "1") <p class="f-bold mb-0 "> Diariamente </p>
              @elseif ($data->frecuencia_reuniones === "2")<p class="f-bold mb-0 "> Semanalmente </p>
              @elseif ($data->frecuencia_reuniones === "3")<p class="f-bold mb-0 "> Mensualmente </p>
              @elseif ($data->frecuencia_reuniones === "4")<p class="f-bold mb-0 "> Raramente </p>
              @else <p class="f-bold mb-0 "> N/A </p>
               @endif</td>
          </tr>
          <tr>
            <td>¿Participas en actividades comunitarias o grupos sociales?</td>
            <td>@if($data->actividades_comunitarias === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td>¿Te sientes parte de tu comunidad?</td>
            <td>@if($data->comunidad === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td rowspan="3">Bienestar Financiero</td>
            <td>¿Cómo calificarías tu situación financiera actual?</td>
            <td>@if($data->situa_financiera === "1") <p class="f-bold mb-0 "> Excelente </p>
              @elseif ($data->situa_financiera === "2")<p class="f-bold mb-0 "> Bueno </p>
              @elseif ($data->situa_financiera === "3")<p class="f-bold mb-0 "> Regular </p>
              @elseif ($data->situa_financiera === "4")<p class="f-bold mb-0 "> Malo </p>
              @else <p class="f-bold mb-0 "> N/A </p>
               @endif</td>
          </tr>
          <tr>
            <td>¿Te sientes seguro económicamente?</td>
            <td>@if($data->seguro_economico === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td>¿Tienes suficientes ingresos para cubrir tus necesidades básicas?</td>
            <td>@if($data->ingresos_suficientes === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td rowspan="3">Trabajo y satisfacción laboral </td>
            <td>¿Estás satisfecho con tu trabajo actual?</td>
            <td>@if($data->trabajo_actual === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td>¿Consideras que tienes un buen equilibrio entre el trabajo y la vida personal? </td>
            <td>@if($data->equilibrio_trabajo === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td>¿Recibes reconocimiento por tu trabajo?</td>
            <td>@if($data->reconocimiento === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td rowspan="3">Consumo de Sustancias</td>
            <td>¿Fumas tabaco o utilizas productos de tabaco?</td>
            <td>@if($data->tabaco_consumo === 1) <p class="f-bold mb-0 "> Si </p> @else <p class="f-bold mb-0 "> No </p> @endif</td>
          </tr>
          <tr>
            <td>¿Consumes alcohol y, de ser así, con qué frecuencia y en qué cantidades?</td>
            <td>{{$data->alchol_consumo ?? 'N/A'}}</td>
          </tr>
          <tr>
            <td>¿Utilizas drogas recreativas o tienes un historial de abuso de sustancias?</td>
            <td>{{$data->drogas_recreativas ?? 'N/A'}}</td>
          </tr>
        </tbody>
      </table>
      <div class="paciente mt-5">
        <p  class="f-15 f-bold mb-0">Psicólogo: <span>{{$data->psicologo}}</span></p>
        <p  class="f-15 f-bold mb-0">Cédula: <span>{{$data->cedula_psicologo}}</span></p>
      </div>  
      <div class="medio marg-final">
      <p class=" texto-izquierda mb-0 f-bold f-15 txt-blue marg-final">CERCAP</p> <span class="ml-5 text-right texto-derecha f-bold marg-final">5526255547/<span class="f-normal txt-r marg-final">cercapmx</span></span>
    </div> 
    </main>
    
  </body>
</html>