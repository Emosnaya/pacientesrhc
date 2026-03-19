
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #1e293b;
            background: #ffffff;
            padding: 10px 20px;
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
        text-align: center;
        width: 100%;
    }
    .line {
        display: inline-block;
        border-top: 1px solid black;
        width: 20%;
        margin-top: 4rem;
        margin-right: 2rem;
        margin-left: 2rem;
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
            width: 100%;
        }
        .f-10{
          font-size: 8.5px;
        }
        .f-15{
          font-size: 13px;
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
    position: relative; /* Establece contexto de posición */
    text-align:justify; /* Alinea contenido al centro horizontalmente */
    margin-bottom: 0; /* Espacio opcional al final del contenedor */
  }
  
  .titulo {
    display: inline-block;/* Hace que el título sea un bloque en línea */ /* Opcional: fondo blanco detrás del título */ /* Espaciado opcional alrededor del título */
    position: relative; /* Establece contexto de posición */
    z-index: 1; /* Asegura que el título esté por encima de la línea */
    font-size: 12px; /* Tamaño de fuente para subtítulos */
    font-weight: bold; /* Negrita para los títulos */
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
        /* === HEADER MODERNO === */
        .header { width: 100%; background: #0A1628; border-radius: 8px; margin-bottom: 10px; padding: 8px 12px; }
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
        .patient-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 10px; }
        .patient-table { width: 100%; border-collapse: collapse; }
        .patient-table td { padding: 2px 6px; font-size: 10px; }
        .patient-name { font-size: 13px; font-weight: 700; color: #0A1628; margin-bottom: 6px; }
        .patient-label { color: #64748b; font-size: 9px; }
        .patient-value { font-weight: 600; color: #334155; }
        .patient-diagnosis { margin-top: 6px; padding-top: 6px; border-top: 1px solid #e2e8f0; font-size: 10px; }
        .patient-diagnosis-label { font-size: 9px; color: #64748b; font-weight: 600; }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 6px 20px; background: white; border-top: 2px solid #0A1628; font-size: 9px; }
        .page-footer-table { width: 100%; }
        .page-footer .clinic-name { font-weight: 700; color: #ef4444; }
        .page-footer .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 35px; }
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
                        @else
                            <span style="font-size: 24px;">❤️</span>
                        @endif
                    </div>
                </td>
                <td style="padding-left: 10px;">
                    <div class="header-title">Expediente de Rehabilitación Pulmonar</div>
                    <div class="header-subtitle">Historia clínica pulmonar</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Registro</div>
                        <div class="header-badge-value">#{{ $paciente->registro }}</div>
                    </div>
                    <div class="header-date">{{ $data->fecha_consulta ? date('d/m/Y', strtotime($data->fecha_consulta)) : 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>
    <!-- PATIENT INFO -->
    <div class="patient-card">
        <div class="patient-name">{{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }} {{ $paciente->nombre }}</div>
        <table class="patient-table">
            <tr>
                <td><span class="patient-label">Edad:</span> <span class="patient-value">{{ $paciente->edad }}</span></td>
                <td><span class="patient-label">Peso:</span> <span class="patient-value">{{ $paciente->peso }} kg</span></td>
                <td><span class="patient-label">Talla:</span> <span class="patient-value">{{ $paciente->talla }} m</span></td>
                <td><span class="patient-label">IMC:</span> <span class="patient-value">{{ round($paciente->imc,2) }}</span></td>
                <td><span class="patient-label">Género:</span> <span class="patient-value">{{ $paciente->genero==1?'Hombre':'Mujer' }}</span></td>
                <td><span class="patient-label">F. Nac.:</span> <span class="patient-value">{{ $paciente->fechaNacimiento }}</span></td>
            </tr>
            <tr>
                <td><span class="patient-label">Est. Civil:</span> <span class="patient-value">{{ $paciente->estadoCivil }}</span></td>
                <td colspan="2"><span class="patient-label">Profesión:</span> <span class="patient-value">{{ $paciente->profesion }}</span></td>
                <td colspan="2"><span class="patient-label">Domicilio:</span> <span class="patient-value">{{ $paciente->domicilio }}</span></td>
                <td><span class="patient-label">Tel:</span> <span class="patient-value">{{ $paciente->telefono }}</span></td>
            </tr>
        </table>
        @if($paciente->diagnostico)
        <div class="patient-diagnosis">
            <span class="patient-diagnosis-label">Diagnóstico:</span> {{ $paciente->diagnostico }}
        </div>
        @endif
    </div>
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

        @if(isset($firmaBase64) && $firmaBase64)
        <!-- Firma al lado derecho al final -->
        <div style="margin-top: 0.5rem; text-align: right; padding-right: 2rem;">
            <img src="{{ $firmaBase64 }}" alt="Firma" style="max-height: 60px; max-width: 150px; display: block; margin-left: auto; margin-bottom: 0.5rem;">
            <p class="f-bold mb-0" style="text-align: right; font-size: 10px;">{{ $user->nombre_con_titulo }}</p>
            @if($user->cedula)
            <p class="" style="text-align: right; font-size: 10px;">Cédula Profesional: {{ $user->cedula }}</p>
            @endif
        </div>
        @endif
    </main>
    </div><!-- End content-wrapper -->
  </body>   
</html>
