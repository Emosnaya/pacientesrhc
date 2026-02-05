
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
    </style>
  </head>
  <body>
    <header class=" mb-0">
      <div class="paciente mt-0">
        <p class="f-bold f-17 text-center mb-0 mt-0">Reporte Final del Programa Rehabilitación Cardiaca.</p>
        <div class="logo-container"><img src="{{ $clinicaLogo }}" alt="logo clínica"></div>
        <br>
          <p  class="f-bold mb-0 f-15">Estimado (a): <span class="f-normal">Dr (a) {{ $paciente->envio}}</span></p>
          <p class="mb-1 mt-0 f-15">Por medio de este conducto me permito informarle de los por menores del programa de Rehabilitación Cardiaca</p>
      </div>
  </header>
  <main class="ma-t-0">
    <div class="medio f-15">
      <p class=" texto-izquierda mb-0 f-bold">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre }}</span> </p> <span class="ml-5 text-right texto-derecha f-bold">Fecha de Ingreso: <span class="f-normal">{{date('d/m/Y',strtotime($data->fecha_inicio ))}}</span></span>
      <p class=" texto-izquierda mb-0 f-bold mt-3">Edad: <span class="f-normal">{{$paciente->edad}}</span> </p> <span class="ml-5 text-right texto-derecha f-bold mt-3">Fecha de Egreso: <span class="f-normal">{{date('d/m/Y',strtotime($data->fecha_final))}}</span></span>
      <p class=" texto-izquierda mb-0 f-bold mt-d">Diagnóstico: <span class="f-normal">{{$paciente->diagnostico}}</span> </p>
    </div>
    <div class="medio mt-5">
      <p class="text-sm texto-izquierda mb-0 f-bold mt-1"> <span class="f-bold">Núm de Sesiones: <span class="f-normal">{{$estrati[0]->sesiones}}</span></span> </p> 
    </div>
    <div class="paciente mt-4">
      <h2 class="h5 titulo">Metodología: </h2>
      <p  class="f-15 f-normal mb-2">Al ingreso se realizó la estratificación de riesgo cardiovascular correspondiente y se comenzó con la rehabilitación cardiaca, consistente en sesiones ergométricas intercalando diversos grupos musculares y de forma progresiva.</p>
      <p class="f-15 f-normal mb-2">Las sesiones fueron controladas mediante supervisión intensiva de la tensión arterial y trazo electrocardiográfico continuos. Durante el proceso el paciente aprendió adecuadamente la graduación de la intensidad del ejercicio  mediante la escala de percepción del esfuerzo (Borg).</p>
      <p class="f-15 f-normal mb-2">Durante las sesiones no presentó complicaciones y ningún evento adverso que consignar.</p>
      <p class="f-15 f-normal mb-2">Al egreso se realizó una prueba de esfuerzo submáxima para evaluación del acondicionamiento cardiovascular y físico.</p>
      <p class="f-15 f-normal mb-0">Los resultados se muestran en la siguiente tabla:</p>
    </div>
    <table class="tabla text-lft border-t table-striped mt-0">
      <thead class="border-t text-center">
        <tr>
          <th scope="col border-t">Rubro</th>
          <th scope="col border-t">Primera Prueba</th>
          <th scope="col border-t">Segunda Prueba</th>
          <th scope="col border-t">Variación (%)</th>
        </tr>
      </thead >
      <tbody class="text-lft border-t">
        <tr>
          <th scope="row border-r">Fecha</th>
          <td class="border-l border-r text-ctr">{{date('d/m/Y',strtotime($data->fecha_inicio ))}}</td>
          <td class="border-l border-r text-ctr">{{date('d/m/Y',strtotime($data->fecha_final ))}}</td>
          <td class="border-l border-r text-ctr">{{$data->fecha}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Fc basal (lpm)*</th>
          <td class="border-l border-r text-ctr">{{$esfuerzoUno->fcBasal }}</td>
          <td class="border-l border-r text-ctr">{{$esfuerzoDos->fcBasal}}</td>
          <td class="border-l border-r text-ctr">{{round($data->fc_basal,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Doble Producto basal**</th>
          <td class="border-l border-r text-ctr">{{$esfuerzoUno->dapBasal}}</td>
          <td class="border-l border-r text-ctr">{{$esfuerzoDos->dapBasal}}</td>
          <td class="border-l border-r text-ctr">{{round($data->doble_pr_bas,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">FC máxima</th>
          <td class="border-l border-r text-ctr">{{$esfuerzoUno->fcMax}}</td>
          <td class="border-l border-r text-ctr">{{$esfuerzoDos->fcMax}}</td>
          <td class="border-l border-r text-ctr">{{round($data->fc_maxima,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Doble Producto máximo</th>
          <td class="border-l border-r text-ctr">{{$esfuerzoUno->dpMax}}</td>
          <td class="border-l border-r text-ctr">{{$esfuerzoDos->dpMax}}</td>
          <td class="border-l border-r text-ctr">{{round($data->doble_pr_max,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">FC Borg 12</th>
          <td class="border-l border-r text-ctr">{{$esfuerzoUno->fcBorg12}}</td>
          <td class="border-l border-r text-ctr">{{$esfuerzoDos->fcBorg12}}</td>
          <td class="border-l border-r text-ctr">{{round($data->fc_borg12,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Doble Producto Borg 12</th>
          <td class="border-l border-r text-ctr">{{$esfuerzoUno->dpBorg12 }}</td>
          <td class="border-l border-r text-ctr">{{$esfuerzoDos->dpBorg12}}</td>
          <td class="border-l border-r text-ctr">{{round($data->doble_pr_b12,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Carga máxima (METs)</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->mets_max,1)}}</td>
          <td class="border-l border-r text-ctr">{{ round($esfuerzoDos->mets_max,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->carga_max,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">% METs alcanzado</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->vo2_alcanzado,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($esfuerzoDos->vo2_alcanzado,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->mets_por,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Tiempo de ejercicio (min)^</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->tiempoEsfuerzo,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($esfuerzoDos->tiempoEsfuerzo,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->tiempo_ejer,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Recuperación de la FC 1'(lpm)</th>
          <td class="border-l border-r text-ctr">{{$esfuerzoUno->fcmax_fc1er}}</td>
          <td class="border-l border-r text-ctr">{{$esfuerzoDos->fcmax_fc1er}}</td>
          <td class="border-l border-r text-ctr">{{round($data->recup_fc,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Umbral Isquémico (METs)</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->mets_U_isq,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($esfuerzoDos->mets_U_isq,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->umbral_isq,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Umbral Isquémico (FC)</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->fc_U_isq,1)}}</td>
          <td class="border-l border-r text-ctr">{{ round($esfuerzoDos->fc_U_isq,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->umbral_isq_fc,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Máximo Desnivel ST</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->MaxInfra,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($esfuerzoDos->MaxInfra,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->max_des_st,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Índice TA en esfuerzo</th>
          <td class="border-l border-r text-ctr">{{sprintf("%.2f", floor($esfuerzoUno->indice_tas  * 100) / 100);}}</td>
          <td class="border-l border-r text-ctr">{{sprintf("%.2f", floor($esfuerzoDos->indice_tas  * 100) / 100);}}</td>
          <td class="border-l border-r text-ctr">{{round($data->indice_ta_es,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Recuperación de la TAS 1/3</th>
          <td class="border-l border-r text-ctr">{{sprintf("%.2f", floor($esfuerzoUno->recup_tas  * 100) / 100);}}</td>
          <td class="border-l border-r text-ctr">{{sprintf("%.2f", floor($esfuerzoDos->recup_tas   * 100) / 100);}}</td>
          <td class="border-l border-r text-ctr">{{round($data->recup_tas,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Resp. Cronotrópica (lpm/MET)</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->resp_crono,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($esfuerzoDos->resp_crono,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->resp_crono,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">IEM***</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->iem,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($esfuerzoDos->iem,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->iem,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Poder Cardiaco en ejercicio</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->pce)}}</td>
          <td class="border-l border-r text-ctr">{{round($esfuerzoDos->pce)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->pod_car_eje,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Puntuación de Duke</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->duke,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($esfuerzoDos->duke,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->duke,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Puntuación de Veteranos</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->veteranos,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($esfuerzoDos->veteranos,1)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->veteranos,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Score de Angor</th>
          <td class="border-l border-r text-ctr">{{round($esfuerzoUno->scoreAngina)}}</td>
          <td class="border-l border-r text-ctr">{{round($esfuerzoDos->scoreAngina)}}</td>
          <td class="border-l border-r text-ctr">{{round($data->score_ang,1)}}</td>
        </tr>
        <tr>
          <th scope="row border-r">Ectopia Ventricular Frecuente</th>
          <td class="border-l border-r text-ctr">{{$esfuerzoUno->ectopia_ventricular===1?"si":"no"}}</td>
          <td class="border-l border-r text-ctr">{{$esfuerzoDos->ectopia_ventricular===1?"si":"no"}}</td>
          <td class="border-l border-r text-ctr"></td>
        </tr>
      </tbody>
  </table>
  <p class="f-10 f-normal mb-0">* Frecuencia cardiaca (FC). ** Doble producto=(TA sist)(FC),***Índice de Eficiencia Miocárdica (IEM).</p>
  <p class="f-10 f-normal mb-0 mt-0">^ Tiempo de ejercicio corregido para  protocolo de Bruce.</p>
  <div class="medio">
    <p class=" texto-izquierda mb-0 f-bold f-15 txt-blue mt-1">{{ $clinica->nombre ?? 'Clínica' }}</p> <span class="ml-5 text-right texto-derecha f-bold mt-1">{{ $clinica->telefono ?? '' }}@if($clinica->email ?? null)/<span class="f-normal txt-r mt-1">{{ $clinica->email }}</span>@endif</span>
  </div>
  <div class="mt-5">
    <div>
      <div class="paciente mt-0">
        <p class="f-bold f-17 text-center mb-0 mt-0">Reporte Final del Programa Rehabilitación Cardiaca.</p>
        <div class="logo-container"><img src="{{ $clinicaLogo }}" alt="logo clínica"></div>
        <br>
          <p  class="f-bold mb-0 f-15">Estimado (a): <span class="f-normal">Dr (a) {{ $paciente->envio}}</span></p>
      </div>
    </div>
    <div class="medio f-15">
      <p class=" texto-izquierda mb-0 f-bold">Nombre: <span class="f-normal">{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre }}</span> </p> <span class="ml-5 text-right texto-derecha f-bold">Fecha de Ingreso: <span class="f-normal">{{date('d/m/Y',strtotime($data->fecha_inicio ))}}</span></span>
      <p class=" texto-izquierda mb-0 f-bold mt-3">Edad: <span class="f-normal">{{$paciente->edad}}</span> </p> <span class="ml-5 text-right texto-derecha f-bold mt-3">Fecha de Egreso: <span class="f-normal">{{date('d/m/Y',strtotime($data->fecha_final ))}}</span></span>
      <p class=" texto-izquierda mb-0 f-bold mt-d">Diagnóstico: <span class="f-normal">{{$paciente->diagnostico}}</span> </p>
    </div>
    <div class="medio mt-5">
      <p class="text-sm texto-izquierda mb-0 f-bold mt-1"> <span class="f-bold">Núm de Sesiones: <span class="f-normal">{{$estrati[0]->sesiones}}</span></span> </p> 
    </div>
    <div class="paciente mt-5">
      <p class="f-15 f-normal mb-2">Como parte del programa  y con el fin de supervisar esta etapa promoviendo el apego al método, se llevará la asesoría intermitente de la realización de la terapia ergométrica del paciente por medio de la programación de refuerzos (en los siguientes seis meses)  para así disminuir la incidencia de deserción del paciente durante la parte domiciliaria del programa. Esta supervisión se realizará siempre en estrecha colaboración con su médico tratante. </p>
    </div>
    <div class="paciente mt-4">
      <h2 class="h5 titulo">Conclusiones: </h2>
      <p  class="f-15 f-normal mb-2">Después de un periodo inicial de acondicionamiento intensivo la evolución del paciente  es satisfactoria, con un aumento importante de su capacidad física y de su tolerancia al ejercicio, así como una reducción de Riesgo CV del <span>{{ sprintf("%.2f", floor(($esfuerzoDos->mets_max - $esfuerzoUno->mets_max)*12))}}%</span> de origen cardiovascular. Aunque el logro es muy importante todavía se espera que mejore aún más la tolerancia al esfuerzo con el transcurrir de los meses, con el beneficio que con lleva el ejercicio; ya por demás demostrado en los programas de Rehabilitación Cardiaca.</p>
    </div>
    <div class="paciente mt-4">
      <p  class="f-15 f-normal mb-5">Agradeciendo su confianza y preferencia. Aprovecho para enviarle un cordial saludo y quedo a sus órdenes.</p>
    </div>
    @if(isset($firmaBase64) && $firmaBase64)
    <div class="paciente mt-5">
      <p  class="f-15 f-normal mb-5">Atentamente,</p>
    </div>
    <div class="paciente mt-5 text-left">
      <div class="signature">
        <img src="{{ $firmaBase64 }}" alt="Firma Digital">
      </div>
      <p  class="f-15 f-bold mb-0">{{ $user->nombre_con_titulo }}</p>
      <p  class="f-15 f-normal mb-2">Rehabilitación Cardiaca.</p>
    </div>
    @endif
    <div class="medio marg-final">
      <p class=" texto-izquierda mb-0 f-bold f-15 txt-blue marg-final">{{ $clinica->nombre ?? 'Clínica' }}</p> <span class="ml-5 text-right texto-derecha f-bold marg-final">{{ $clinica->telefono ?? '' }}@if($clinica->email ?? null)/<span class="f-normal txt-r marg-final">{{ $clinica->email }}</span>@endif</span>
    </div>



  </div>
  </main>
  
  </body>
</html>