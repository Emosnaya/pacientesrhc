
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- Bootstrap CSS -->
    <style>
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
        body {
            font-size: 9px;
            margin: 0;
            padding: 5px;
        }
        .signature {
            text-align: right;
            width: 100%;
            margin-top: 0rem;
            padding-right: 2rem;
        }
    .line {
        display: inline-block;
        border-top: 1px solid black;
        width: 20%;
        margin-top: 3rem;
        margin-right: 2rem;
        margin-left: 2rem;
        padding: 1rem;
    }
    .text {
        font-size: 9px;
        text-align: center;
        width: 100%;
    }
        .tabla{
            font-size: 7.5px;
            margin-bottom: 0;
            width: 100%;
        }
        .f-10{
          font-size: 9px;
        }
        .f-15{
          font-size: 13px;
        }
        .f-7{
          font-size: 7px;
        }
        .paciente{
            font-size: 10px;
        }
        .text-right{
            text-align: right;
        }
        .f-bold{
            font-weight: bold;
        }
        .f-normal{
            font-weight: normal;
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
    .bck-gray{
      background-color: #DDDEE1;
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
    text-align: left;
    position: absolute;
    left: 0;
  }
  
  .texto-derecha {
    text-align: right;
    position: absolute;
    right: 0;
  }
  .contenedor {
    position: relative;
    text-align: justify;
    margin-bottom: 0;
    margin-top: 0.5rem;
  }
  
  .titulo {
    display: inline-block;
    position: relative;
    z-index: 1;
    padding-right: 0.5rem;
    font-size: 14px;
  }
  .m-t-2{
    margin-top: -1rem;
  }
  .m-t-0{
    margin-top: -0.5rem;
  }
  
  .linea {
    position: absolute;
    left: 0;
    right: 0;
    top: 0.6rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-heredo {
    position: absolute;
    left: 12.5rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-inm {
    position: absolute;
    left: 6rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-act {
    position: absolute;
    left: 7rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-med {
    position: absolute;
    left: 9.5rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-med-2 {
    position: absolute;
    left: 15rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-fac {
    position: absolute;
    left: 8.5rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-sin {
    position: absolute;
    left: 9rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-exp {
    position: absolute;
    left: 9rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-fun {
    position: absolute;
    left: 7.5rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-eval {
    position: absolute;
    left: 9rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-dia {
    position: absolute;
    left: 13.5rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-medic {
    position: absolute;
    left: 10rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  .linea-ref {
    position: absolute;
    left: 9.5rem;
    right: 0;
    top: 0.3rem;
    border-bottom: 2px solid black;
    z-index: 0;
  }
  html {
    margin-top:0;
    padding-top:0;
  }
    </style>
  </head>
  <body>
    <header class="mb-0">
        <div class="paciente ma-t-0 mb-0">
            <p class="f-bold f-15 text-center mb-0 mt-0">Expediente de Rehabilitación Pulmonar</p>
            <div class="logo-container"><img src="{{ $clinicaLogo }}" alt="logo clínica"></div>
            <div class="medio mb-3">
              <p class=" texto-izquierda mb-0 f-bold">Fecha: {{ $data->fecha_consulta ? date('d/m/Y', strtotime($data->fecha_consulta)) : 'N/A' }} </p> 
              <span class="ml-5 text-right texto-derecha f-bold">Hora: {{ $data->hora_consulta ? date('H:i', strtotime($data->hora_consulta)) : 'N/A' }}</span>
            </div>
              <p  class="f-bold mb-0 mt-1">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre}}</span>  <span class="f-bold">  F.Nacimiento: <span  class="f-normal">{{$paciente->fechaNacimiento}}</span></span></p>
              <p class="f-bold mb-0 mt-0 ">Diagnostico: <span  class="f-normal">{{$paciente->diagnostico}}</span> </p>
              <p class="mt-0 mb-0"> <span class="f-bold">  Edad: <span  class="f-normal">{{$paciente->edad}}</span></span><span class="f-bold ml-3">  Peso (Kg) : <span class="f-normal">{{$paciente->peso}}</span></span> 
                <span class="f-bold ml-3">  Talla (m): <span  class="f-normal">{{$paciente->talla}}</span></span> <span class="f-bold ml-3">  IMC (kg/m2): <span  class="f-normal">{{round($paciente->imc,2)}}</span></span>
              <span class="f-bold ml-3">  Género: <span  class="f-normal">{{($paciente->genero==1?"Hombre":"Mujer")}}</span></span>  <span class="f-bold ml-3">  Estado Civil: <span  class="f-normal">{{$paciente->estadoCivil}}</span></span>
              <span class="f-bold ml-3">  Profesión: <span  class="f-normal">{{$paciente->profesion}}</span></span></p>
              <p class="f-bold mt-0 mb-0"> Domicilio: <span  class="f-normal">{{$paciente->domicilio}}</span> <span class="f-bold ml-3">  Teléfono: <span  class="f-normal">{{$paciente->telefono}}</span></span></p>
          </div>
    </header>
    <main class="mt-0">
        <!-- Antecedentes Heredo Familiares -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Antecedentes Heredo Familiares</h2>
            <div class="linea-heredo"></div>
        </div>
        <p class="f-bold m-t-0 f-10 mb-0 text-jst">{{ $data->antecedentes_heredo_familiares ?: 'No registrado' }}</p>

        <!-- Inmunizaciones -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Inmunizaciones</h2>
            <div class="linea-inm"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                  <td class="border-r f-bold">COVID-19</td>
                  <td class="border-r">Vacunado: <span class="f-bold">{{$data->covid19_si_no == 1 ? 'Sí' : ($data->covid19_si_no == 0 ? 'No' : 'N/A')}}</span></td>
                  <td class="border-r">Número de dosis: <span class="f-bold">{{$data->covid19_numero_dosis ?: 'N/A'}}</span></td>
                  <td>Última dosis: <span class="f-bold">{{$data->covid19_fecha_ultima_dosis ? date('d/m/Y', strtotime($data->covid19_fecha_ultima_dosis)) : 'N/A'}}</span></td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Influenza</td>
                  <td class="border-r">Vacunado: <span class="f-bold">{{$data->influenza_si_no == 1 ? 'Sí' : ($data->influenza_si_no == 0 ? 'No' : 'N/A')}}</span></td>
                  <td class="border-r">Año: <span class="f-bold">{{$data->influenza_ano ?: 'N/A'}}</span></td>
                  <td></td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Neumococo</td>
                  <td class="border-r">Vacunado: <span class="f-bold">{{$data->neumococo_si_no == 1 ? 'Sí' : ($data->neumococo_si_no == 0 ? 'No' : 'N/A')}}</span></td>
                  <td class="border-r">Año: <span class="f-bold">{{$data->neumococo_ano ?: 'N/A'}}</span></td>
                  <td></td>
                </tr>
              </tbody>
        </table>

        <!-- Actividad Física -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Actividad Física</h2>
            <div class="linea-act"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                  <td class="border-r">¿Realiza actividad física?: <span class="f-bold">{{$data->actividad_fisica_si_no == 1 ? 'Sí' : ($data->actividad_fisica_si_no == 0 ? 'No' : 'N/A')}}</span></td>
                  <td class="border-r">Tipo: <span class="f-bold">{{$data->actividad_fisica_tipo ?: 'N/A'}}</span></td>
                  <td class="border-r">Días/semana: <span class="f-bold">{{$data->actividad_fisica_dias_semana ?: 'N/A'}}</span></td>
                  <td>Tiempo/día (min): <span class="f-bold">{{$data->actividad_fisica_tiempo_dia ?: 'N/A'}}</span></td>
                </tr>
              </tbody>
        </table>

        <!-- Antecedentes Médicos -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Antecedentes Médicos</h2>
            <div class="linea-med"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                  <td class="border-r f-bold">Antecedentes Alérgicos</td>
                  <td colspan="3" class="text-lft">{{ $data->antecedentes_alergicos ?: 'No registrado' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Antecedentes Quirúrgicos</td>
                  <td colspan="3" class="text-lft">{{ $data->antecedentes_quirurgicos ?: 'No registrado' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Antecedentes Traumáticos</td>
                  <td colspan="3" class="text-lft">{{ $data->antecedentes_traumaticos ?: 'No registrado' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Exposicionales</td>
                  <td colspan="3" class="text-lft">{{ $data->antecedentes_exposicionales ?: 'No registrado' }}</td>
                </tr>
              </tbody>
        </table>

        <!-- Factores de Riesgo -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Factores de Riesgo</h2>
            <div class="linea-fac"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                  <td class="border-r f-bold">Tabaquismo</td>
                  <td class="border-r">¿Fuma o fumó?: <span class="f-bold">{{$data->tabaquismo_boolean == 1 ? 'Sí' : ($data->tabaquismo_boolean == 0 ? 'No' : 'N/A')}}</span></td>
                  <td colspan="2" class="text-lft">Detalles: {{ $data->tabaquismo_detalle ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Alcoholismo</td>
                  <td class="border-r">¿Consume alcohol?: <span class="f-bold">{{$data->alcoholismo_boolean == 1 ? 'Sí' : ($data->alcoholismo_boolean == 0 ? 'No' : 'N/A')}}</span></td>
                  <td colspan="2" class="text-lft">Detalles: {{ $data->alcoholismo_detalle ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Toxicomanías</td>
                  <td class="border-r">¿Consume drogas?: <span class="f-bold">{{$data->toxicomanias_boolean == 1 ? 'Sí' : ($data->toxicomanias_boolean == 0 ? 'No' : 'N/A')}}</span></td>
                  <td colspan="2" class="text-lft">Detalles: {{ $data->toxicomanias_detalle ?: 'N/A' }}</td>
                </tr>
              </tbody>
        </table>

        <!-- Enfermedades Crónico-Degenerativas -->
        @php
            $enfermedades = is_array($data->enfermedades_cronicas) 
                ? $data->enfermedades_cronicas 
                : (is_string($data->enfermedades_cronicas) ? json_decode($data->enfermedades_cronicas, true) : []);
            $enfermedades = $enfermedades ?: [];
        @endphp
        @if(count($enfermedades) > 0)
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Enfermedades Crónico-Degenerativas</h2>
            <div class="linea-med-2"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <thead>
                <tr class="b-dark">
                    <th class="border-r">Enfermedad</th>
                    <th class="border-r">Año Diagnóstico</th>
                    <th>Tratamiento</th>
                </tr>
            </thead>
            <tbody>
                @foreach($enfermedades as $enfermedad)
                <tr>
                  <td class="border-r">{{ $enfermedad['nombre'] ?? 'N/A' }}</td>
                  <td class="border-r">{{ $enfermedad['ano'] ?? 'N/A' }}</td>
                  <td>{{ $enfermedad['tratamiento'] ?? 'N/A' }}</td>
                </tr>
                @endforeach
              </tbody>
        </table>
        @endif

        <!-- Motivo de Referencia -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Motivo de Referencia</h2>
            <div class="linea-ref"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                  <td class="border-r f-bold">Médico que envía</td>
                  <td colspan="3" class="text-lft">{{ $data->medico_envia ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Motivo de envío</td>
                  <td colspan="3" class="text-lft text-jst">{{ $data->motivo_envio ?: 'No registrado' }}</td>
                </tr>
              </tbody>
        </table>

        <!-- Síntomas Principales -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Síntomas Principales</h2>
            <div class="linea-sin"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                  <td class="border-r f-bold">Disnea</td>
                  <td class="border-r">¿Presenta?: <span class="f-bold">{{$data->disnea_boolean == 1 ? 'Sí' : ($data->disnea_boolean == 0 ? 'No' : 'N/A')}}</span></td>
                  <td colspan="2" class="text-lft">Detalles: {{ $data->disnea_detalle ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Fatiga</td>
                  <td class="border-r">¿Presenta?: <span class="f-bold">{{$data->fatiga_boolean == 1 ? 'Sí' : ($data->fatiga_boolean == 0 ? 'No' : 'N/A')}}</span></td>
                  <td colspan="2" class="text-lft">Detalles: {{ $data->fatiga_detalle ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Tos</td>
                  <td class="border-r">¿Presenta?: <span class="f-bold">{{$data->tos_boolean == 1 ? 'Sí' : ($data->tos_boolean == 0 ? 'No' : 'N/A')}}</span></td>
                  <td colspan="2" class="text-lft">Detalles: {{ $data->tos_detalle ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Dolor</td>
                  <td class="border-r">¿Presenta?: <span class="f-bold">{{$data->dolor_boolean == 1 ? 'Sí' : ($data->dolor_boolean == 0 ? 'No' : 'N/A')}}</span></td>
                  <td colspan="2" class="text-lft">Detalles: {{ $data->dolor_detalle ?: 'N/A' }}</td>
                </tr>
              </tbody>
        </table>

        <!-- Estado Funcional -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Estado Funcional</h2>
            <div class="linea-fun"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                  <td class="border-r f-bold">Independencia en AVD</td>
                  <td class="text-lft text-jst">{{ $data->independencia_avd ?: 'No registrado' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Sueño</td>
                  <td class="text-lft">¿Presenta problemas?: <span class="f-bold">{{$data->sueno_boolean == 1 ? 'Sí' : ($data->sueno_boolean == 0 ? 'No' : 'N/A')}}</span></td>
                </tr>
                @if($data->sueno_detalle)
                <tr>
                  <td class="border-r f-bold">Detalles del sueño</td>
                  <td class="text-lft text-jst">{{ $data->sueno_detalle }}</td>
                </tr>
                @endif
                <tr>
                  <td class="border-r f-bold">Estado Emocional</td>
                  <td class="text-lft text-jst">{{ $data->estado_emocional ?: 'No registrado' }}</td>
                </tr>
              </tbody>
        </table>

        <!-- Exploración Física -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Exploración Física</h2>
            <div class="linea-exp"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                  <td class="border-r f-bold">Signos Vitales</td>
                  <td class="border-r">FC (lpm): <span class="f-bold">{{$data->fc ?: 'N/A'}}</span></td>
                  <td class="border-r">TA (mmHg): <span class="f-bold">{{$data->ta ?: 'N/A'}}</span></td>
                  <td class="border-r">SAT AA (%): <span class="f-bold">{{$data->sat_aa ?: 'N/A'}}</span></td>
                  <td class="border-r">SAT FIO2 (%): <span class="f-bold">{{$data->sat_fio2 ?: 'N/A'}}</span></td>
                  <td>FIO2 (%): <span class="f-bold">{{$data->fio2 ?: 'N/A'}}</span></td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Signos Vitales Iniciales - Evaluación</td>
                  <td colspan="2" class="border-r">SAT Inicial (%): <span class="f-bold">{{$data->sat_inicial ?: 'N/A'}}</span></td>
                  <td colspan="3">FC Inicial (lpm): <span class="f-bold">{{$data->fc_inicial ?: 'N/A'}}</span></td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Cabeza y cuello</td>
                  <td colspan="5" class="text-lft text-jst">{{ $data->cabeza_cuello ?: 'No registrado' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Tórax</td>
                  <td colspan="5" class="text-lft text-jst">{{ $data->torax ?: 'No registrado' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Extremidades</td>
                  <td colspan="5" class="text-lft text-jst">{{ $data->extremidades ?: 'No registrado' }}</td>
                </tr>
              </tbody>
        </table>

        <!-- Evaluación Funcional -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Evaluación Funcional</h2>
            <div class="linea-eval"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <tbody>
                <tr>
                  <td class="border-r f-bold" style="width: 15%;">Equilibrio</td>
                  <td class="text-lft text-jst" style="width: 35%;">{{ $data->equilibrio ?: 'No registrado' }}</td>
                  <td class="border-r f-bold" style="width: 15%;">Marcha</td>
                  <td class="text-lft text-jst" style="width: 35%;">{{ $data->marcha ?: 'No registrado' }}</td>
                </tr>
              </tbody>
        </table>

        <!-- Pruebas Funcionales -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Pruebas Funcionales</h2>
            <div class="linea-eval"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <thead>
                <tr>
                  <th class="border-r f-bold" style="width: 50%;">Prueba</th>
                  <th class="f-bold" style="width: 50%;">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                  <td class="border-r f-bold text-lft">Sit to Stand 5 rep (seg)</td>
                  <td>{{ $data->sit_to_stand_5rep ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold text-lft">Sit to Stand 30s (rep)</td>
                  <td>{{ $data->sit_to_stand_30seg ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold text-lft">Sit to Stand 60s (rep)</td>
                  <td>{{ $data->sit_to_stand_60seg ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold text-lft">Dinamometría Derecha (kg)</td>
                  <td>{{ $data->dinamometria_derecha ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold text-lft">Dinamometría Izquierda (kg)</td>
                  <td>{{ $data->dinamometria_izquierda ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold text-lft">Signos vitales iniciales</td>
                  <td class="text-lft">FC: {{ $data->fc_inicial ?: 'N/A' }} · SAT: {{ $data->sat_inicial ?: 'N/A' }} · Fio2: {{ $data->fio2_inicial ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold text-lft">Signos vitales finales</td>
                  <td class="text-lft">FC: {{ $data->fc_final ?: 'N/A' }} · SAT: {{ $data->sat_final ?: 'N/A' }} · Fio2: {{ $data->fio2_final ?? 'N/A' }}</td>
                </tr>
                @if($data->otros_exploracion)
                <tr>
                  <td class="border-r f-bold">Otros hallazgos</td>
                  <td class="text-lft text-jst">{{ $data->otros_exploracion }}</td>
                </tr>
                @endif
              </tbody>
        </table>

        <!-- Diagnósticos y Plan -->
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Diagnósticos y Plan de Tratamiento</h2>
            <div class="linea-dia"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped">
            <tbody>
                <tr>
                  <td class="border-r f-bold" style="width: 25%;">Diagnósticos Finales</td>
                  <td class="text-lft text-jst" style="width: 75%;">{{ $data->diagnosticos_finales ?: 'No registrado' }}</td>
                </tr>
                <tr>
                  <td class="border-r f-bold">Plan de Tratamiento</td>
                  <td class="text-lft text-jst">{{ $data->plan_tratamiento ?: 'No registrado' }}</td>
                </tr>
              </tbody>
        </table>

        <!-- Medicamentos Actuales - Movido al final -->
        @php
            $medicamentos = is_array($data->medicamentos_actuales) 
                ? $data->medicamentos_actuales 
                : (is_string($data->medicamentos_actuales) ? json_decode($data->medicamentos_actuales, true) : []);
            $medicamentos = $medicamentos ?: [];
        @endphp
        @if(count($medicamentos) > 0)
        <div class="contenedor mt-1">
            <h2 class="h8 titulo">Medicamentos Actuales</h2>
            <div class="linea-medic"></div>
        </div>
        <table class="tabla text-lft border-t text-center m-t-0 table-striped bck-gray">
            <thead>
                <tr class="b-dark">
                    <th class="border-r">Medicamento</th>
                    <th>Dosis</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicamentos as $medicamento)
                <tr>
                  <td class="border-r">{{ $medicamento['medicamento'] ?? 'N/A' }}</td>
                  <td>{{ $medicamento['dosis'] ?? 'N/A' }}</td>
                </tr>
                @endforeach
              </tbody>
        </table>
        @endif

        <!-- Firma al lado derecho al final -->
        <div style="margin-top: 0.5rem; text-align: right; padding-right: 2rem;">
            @if($firmaBase64)
                <img src="{{ $firmaBase64 }}" alt="Firma" style="max-height: 60px; max-width: 150px; display: block; margin-left: auto; margin-bottom: 0.5rem;">
            @endif
            <p class="f-bold mb-0" style="text-align: right; font-size: 10px;">Dr. {{ $user->nombre . ' ' . $user->apellidoPat }}</p>
            @if($user->cedula)
            <p class="f-7 mb-0" style="text-align: right;">Cédula Profesional: {{ $user->cedula }}</p>
            @endif
        </div>
    </main>
  </body>   
</html>
