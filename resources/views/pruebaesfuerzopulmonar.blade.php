<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Prueba de Esfuerzo Naughton Modificada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            line-height: 1.3;
        }
        .f-bold {
            font-weight: bold;
        }
        .f-9 {
            font-size: 9px;
        }
        .f-10 {
            font-size: 10px;
        }
        .f-11 {
            font-size: 11px;
        }
        .f-12 {
            font-size: 12px;
        }
        .text-center {
            text-align: center;
        }
        .mb-1 {
            margin-bottom: 0.25rem;
        }
        .mb-2 {
            margin-bottom: 0.5rem;
        }
        .mt-1 {
            margin-top: 0.25rem;
        }
        .mt-2 {
            margin-top: 0.5rem;
        }
        .mt-3 {
            margin-top: 0.75rem;
        }
        .section-title {
            font-weight: bold;
            font-size: 11px;
            background-color: #E8E8E8;
            padding: 3px 6px;
            margin-top: 0.5rem;
            margin-bottom: 0.3rem;
            border-left: 3px solid #4A90E2;
        }
        .tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 0.5rem;
        }
        .tabla td, .tabla th {
            padding: 2px 4px;
            border: 1px solid #666;
        }
        .tabla th {
            background-color: #D3D3D3;
            font-weight: bold;
            text-align: center;
        }
        .info-row {
            margin-bottom: 0.2rem;
        }
        .inline-label {
            font-weight: bold;
            display: inline-block;
            min-width: 120px;
        }
    </style>
</head>
<body>
    <header class="text-center mb-2">
        <img src="img/logo.png" alt="cercap logo" style="height: 70px">
        <h1 class="f-12 f-bold mb-1">PRUEBA DE ESFUERZO NAUGHTON MODIFICADA</h1>
        <p class="f-10 mb-1">Rehabilitación Pulmonar</p>
    </header>

    <main>
        <!-- Datos del paciente -->
        <div class="info-row f-10">
            <span class="inline-label">Fecha de Realización:</span>
            <span>{{ $data->fecha_realizacion ? date('d/m/Y', strtotime($data->fecha_realizacion)) : 'N/A' }}</span>
        </div>
        <div class="info-row f-10">
            <span class="inline-label">Nombre:</span>
            <span>{{ $paciente->apellidoPat . ' ' . $paciente->apellidoMat . ' ' . $paciente->nombre }}</span>
        </div>
        <div class="info-row f-10">
            <span class="inline-label">Fecha de nacimiento:</span>
            <span>{{ $paciente->fechaNacimiento ? date('d/m/Y', strtotime($paciente->fechaNacimiento)) : 'N/A' }}</span>
            <span class="inline-label" style="margin-left: 20px;">Edad:</span>
            <span>{{ $paciente->fechaNacimiento ? \Carbon\Carbon::parse($paciente->fechaNacimiento)->age : 'N/A' }} años</span>
        </div>
        
        @if($data->diagnosticos)
        <div class="info-row f-10">
            <span class="inline-label">Diagnósticos:</span>
            <span>{{ $data->diagnosticos }}</span>
        </div>
        @endif

        <div class="info-row f-10">
            <span class="inline-label">Oxígeno suplementario:</span>
            <span>{{ $data->oxigeno_suplementario ? 'Sí' : 'No' }}</span>
            @if($data->oxigeno_suplementario && $data->oxigeno_litros)
            <span> - {{ $data->oxigeno_litros }} L/min</span>
            @endif
        </div>

        <!-- Valores predictivos -->
        <div class="section-title">VALORES PREDICTIVOS</div>
        <table class="tabla">
            <tr>
                <td class="f-bold" style="width: 40%">Predicho VO2</td>
                <td>{{ $data->predicho_vo2 ?? 'N/A' }}</td>
                <td class="f-bold" style="width: 40%">Predicho METS</td>
                <td>{{ $data->predicho_mets ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="f-bold">100% FCMAX</td>
                <td>{{ $data->fcmax_100 ?? 'N/A' }}</td>
                <td class="f-bold">65% FCMAX</td>
                <td>{{ $data->fcmax_65 ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="f-bold">75% FCMAX</td>
                <td>{{ $data->fcmax_75 ?? 'N/A' }}</td>
                <td class="f-bold">85% FCMAX</td>
                <td>{{ $data->fcmax_85 ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Mediciones -->
        <div class="section-title">MEDICIONES</div>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Parámetro</th>
                    <th>Basal</th>
                    <th>Máximo</th>
                    <th>Rec. 1min</th>
                    <th>Rec. 3min</th>
                    <th>Rec. 5min</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="f-bold">FC</td>
                    <td class="text-center">{{ $data->basal_fc ?? '-' }}</td>
                    <td class="text-center">{{ $data->max_fc_pico ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec1_fc ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec3_fc ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec5_fc ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">TAS</td>
                    <td class="text-center">{{ $data->basal_tas ?? '-' }}</td>
                    <td class="text-center">{{ $data->max_tas ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec1_tas ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec3_tas ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec5_tas ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">TAD</td>
                    <td class="text-center">{{ $data->basal_tad ?? '-' }}</td>
                    <td class="text-center">{{ $data->max_tad ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec1_tad ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec3_tad ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec5_tad ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">Saturación</td>
                    <td class="text-center">{{ $data->basal_saturacion ? $data->basal_saturacion . '%' : '-' }}</td>
                    <td class="text-center">{{ $data->max_saturacion ? $data->max_saturacion . '%' : '-' }}</td>
                    <td class="text-center">{{ $data->rec1_saturacion ? $data->rec1_saturacion . '%' : '-' }}</td>
                    <td class="text-center">{{ $data->rec3_saturacion ? $data->rec3_saturacion . '%' : '-' }}</td>
                    <td class="text-center">{{ $data->rec5_saturacion ? $data->rec5_saturacion . '%' : '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">BORG Disnea</td>
                    <td class="text-center">{{ $data->basal_borg_disnea ?? '-' }}</td>
                    <td class="text-center">{{ $data->max_borg_disnea ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec1_borg_disnea ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec3_borg_disnea ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec5_borg_disnea ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="f-bold">BORG Fatiga</td>
                    <td class="text-center">{{ $data->basal_borg_fatiga ?? '-' }}</td>
                    <td class="text-center">{{ $data->max_borg_fatiga ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec1_borg_fatiga ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec3_borg_fatiga ?? '-' }}</td>
                    <td class="text-center">{{ $data->rec5_borg_fatiga ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Datos máximos adicionales -->
        @if($data->max_velocidad_kmh || $data->max_inclinacion || $data->max_mets)
        <div class="info-row f-10">
            @if($data->max_velocidad_kmh)
            <span class="inline-label">Velocidad máxima:</span>
            <span>{{ $data->max_velocidad_kmh }} km/hr</span>
            @endif
            @if($data->max_inclinacion)
            <span class="inline-label" style="margin-left: 20px;">Inclinación máxima:</span>
            <span>{{ $data->max_inclinacion }}%</span>
            @endif
            @if($data->max_mets)
            <span class="inline-label" style="margin-left: 20px;">METS máximos:</span>
            <span>{{ $data->max_mets }}</span>
            @endif
        </div>
        @endif

        @if($data->motivo_detencion)
        <div class="info-row f-10">
            <span class="inline-label">Motivo de detención:</span>
            <span>{{ $data->motivo_detencion }}</span>
        </div>
        @endif

        <!-- Resultados -->
        <div class="section-title">RESULTADOS</div>
        
        @if($data->etapa_maxima)
        <div class="info-row f-10">
            <span class="inline-label">Etapa máxima alcanzada:</span>
            <span>{{ $data->etapa_maxima }}</span>
        </div>
        @endif

        @if($data->vel_etapa || $data->inclinacion_etapa)
        <div class="info-row f-10">
            <span class="inline-label">Correspondiente a:</span>
            @if($data->vel_etapa)
            <span>Vel: {{ $data->vel_etapa }} km/hr</span>
            @endif
            @if($data->inclinacion_etapa)
            <span> - Inclinación: {{ $data->inclinacion_etapa }}%</span>
            @endif
        </div>
        @endif

        <table class="tabla mt-1">
            <tr>
                <td class="f-bold" style="width: 40%">METS Equivalentes</td>
                <td>{{ $data->mets_equivalentes ?? 'N/A' }}</td>
                <td class="f-bold" style="width: 40%">VO2 Equivalente</td>
                <td>{{ $data->vo2_equivalente ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="f-bold">FC Pico alcanzada</td>
                <td>{{ $data->fc_pico_alcanzado ?? $data->max_fc_pico ?? 'N/A' }}</td>
                <td class="f-bold">Saturación mínima</td>
                <td>{{ $data->saturacion_minima ? $data->saturacion_minima . '%' : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="f-bold">BORG MAX Disnea</td>
                <td>{{ $data->borg_max_disnea ?? $data->max_borg_disnea ?? 'N/A' }}</td>
                <td class="f-bold">BORG MAX Fatiga</td>
                <td>{{ $data->borg_max_fatiga ?? $data->max_borg_fatiga ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="f-bold">% FCMAX</td>
                <td>{{ $data->porcentaje_fcmax ? $data->porcentaje_fcmax . '%' : 'N/A' }}</td>
                <td class="f-bold">% METS Alcanzados</td>
                <td>{{ $data->porcentaje_mets ? $data->porcentaje_mets . '%' : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="f-bold">% VO2 Alcanzado</td>
                <td>{{ $data->porcentaje_vo2 ? $data->porcentaje_vo2 . '%' : 'N/A' }}</td>
                <td class="f-bold">Clase Funcional</td>
                <td>{{ $data->clase_funcional ?? 'N/A' }}</td>
            </tr>
        </table>

        @if($data->clase_funcional)
        <div class="f-9" style="margin-left: 20px; margin-bottom: 0.3rem;">
            <div>A: +20 ML/KG/MIN</div>
            <div>B: 16-20 ML/KG/MIN</div>
            <div>C: 10-16 ML/KG/MIN</div>
            <div>D: 6-10 ML/KG/MIN</div>
            <div>E: Menor a 6 ML/KG/MIN</div>
        </div>
        @endif

        <!-- Interpretación -->
        @if($data->interpretacion)
        <div class="section-title">INTERPRETACIÓN</div>
        <p class="f-9" style="text-align: justify; margin: 0.3rem 0;">{!! nl2br(e($data->interpretacion)) !!}</p>
        @endif

        <!-- Plan -->
        <div class="section-title">PLAN DE ACONDICIONAMIENTO</div>
        
        @if($data->plan_sesiones || $data->plan_tiempo)
        <div class="info-row f-10">
            @if($data->plan_sesiones)
            <span class="inline-label">Número de Sesiones:</span>
            <span>{{ $data->plan_sesiones }}</span>
            @endif
            @if($data->plan_tiempo)
            <span class="inline-label" style="margin-left: 20px;">Tiempo:</span>
            <span>{{ $data->plan_tiempo }}</span>
            @endif
        </div>
        @endif

        @if($data->plan_oxigeno_litros || $data->plan_borg_modificado || $data->plan_fc_inicial_min || $data->plan_fc_no_rebasar)
        <div class="f-10 f-bold mt-2 mb-1">Parámetros de seguridad:</div>
        
        @if($data->plan_oxigeno_litros)
        <div class="info-row f-10" style="margin-left: 10px;">
            • Uso de oxígeno suplementario: {{ $data->plan_oxigeno_litros }} L/min (mantener saturación arriba de 90%)
        </div>
        @endif
        
        @if($data->plan_borg_modificado)
        <div class="info-row f-10" style="margin-left: 10px;">
            • BORG Modificado durante entrenamiento: {{ $data->plan_borg_modificado }}
        </div>
        @endif
        
        @if($data->plan_fc_inicial_min || $data->plan_fc_inicial_max)
        <div class="info-row f-10" style="margin-left: 10px;">
            • FC Trabajo inicial: {{ $data->plan_fc_inicial_min ?? 'N/A' }} - {{ $data->plan_fc_inicial_max ?? 'N/A' }}
        </div>
        @endif
        
        @if($data->plan_fc_no_rebasar)
        <div class="info-row f-10" style="margin-left: 10px;">
            • FC No rebasar: {{ $data->plan_fc_no_rebasar }}
        </div>
        @endif
        @endif

        <!-- Equipos -->
        @if($data->plan_banda_sin_fin || $data->plan_ergometro_brazos || $data->plan_bicicleta_estatica)
        <div class="f-10 f-bold mt-2 mb-1">Equipos:</div>
        
        @if($data->plan_banda_sin_fin)
        <div class="f-10 f-bold mt-1" style="margin-left: 10px;">Banda sin Fin:</div>
        @if($data->plan_banda_velocidad)
        <div class="info-row f-9" style="margin-left: 20px;">Velocidad: {{ $data->plan_banda_velocidad }} km/hr</div>
        @endif
        @if($data->plan_banda_tiempo)
        <div class="info-row f-9" style="margin-left: 20px;">Tiempo: {{ $data->plan_banda_tiempo }}</div>
        @endif
        @if($data->plan_banda_resistencia)
        <div class="info-row f-9" style="margin-left: 20px;">Resistencia: {{ $data->plan_banda_resistencia }}</div>
        @endif
        @endif
        
        @if($data->plan_ergometro_brazos)
        <div class="f-10 f-bold mt-1" style="margin-left: 10px;">Ergómetro de Brazos:</div>
        @if($data->plan_ergometro_velocidad)
        <div class="info-row f-9" style="margin-left: 20px;">Velocidad: {{ $data->plan_ergometro_velocidad }}</div>
        @endif
        @if($data->plan_ergometro_resistencia)
        <div class="info-row f-9" style="margin-left: 20px;">Resistencia: {{ $data->plan_ergometro_resistencia }}</div>
        @endif
        @endif
        
        @if($data->plan_bicicleta_estatica)
        <div class="f-10 f-bold mt-1" style="margin-left: 10px;">Bicicleta Estática:</div>
        @if($data->plan_bicicleta_velocidad)
        <div class="info-row f-9" style="margin-left: 20px;">Velocidad: {{ $data->plan_bicicleta_velocidad }}</div>
        @endif
        @if($data->plan_bicicleta_resistencia)
        <div class="info-row f-9" style="margin-left: 20px;">Resistencia: {{ $data->plan_bicicleta_resistencia }}</div>
        @endif
        @endif
        @endif

        @if($data->plan_manejo_complementario)
        <div class="f-10 f-bold mt-2 mb-1">Completar manejo:</div>
        <div class="f-9" style="text-align: justify; margin-left: 10px;">{!! nl2br(e($data->plan_manejo_complementario)) !!}</div>
        @endif

        <!-- Firma -->
        <div style="margin-top: 30px; text-align: center;">
            @if(isset($firmaBase64) && $firmaBase64)
                <img src="{{ $firmaBase64 }}" alt="Firma" style="max-width: 150px; height: auto">
            @endif
            @if(isset($userParaFirma) && $userParaFirma)
            <div style="border-top: 2px solid #000; width: 300px; margin: 5px auto 5px auto;"></div>
            <p class="f-10 f-bold mb-1">Dr. {{ trim($userParaFirma->nombre . ' ' . ($userParaFirma->apellidoPat ?? '')) }}</p>
            <p class="f-9 mb-1">Médico Especialista en Medicina de Rehabilitación</p>
            @if(!empty($userParaFirma->cedula))
            <p class="f-9">Cédula Profesional: {{ $userParaFirma->cedula }}</p>
            @endif
            @endif
        </div>
    </main>

    <footer style="margin-top: 3rem; padding-top: 0.3rem; border-top: 1px solid #000;">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 50%; vertical-align: top;">
                <p class="f-9 mb-1"><strong>Torre Médica II</strong></p>
                <p class="f-9 mb-1">Real Mayorazgo 130, local 3</p>
                <p class="f-9 mb-1">Col. Xoco, Benito Juárez</p>
                <p class="f-9 mb-1">C.P. 03330 CDMX</p>
            </div>
            <div style="display: table-cell; width: 50%; vertical-align: top; text-align: right;">
                <p class="f-9 mb-1"><strong>Informes y citas:</strong></p>
                <p class="f-9 mb-1"> 55 2625 5547 / 55 2625 5548</p>
                <p class="f-9 mb-1"> 56 3034 8666</p>
                <p class="f-9 mb-1"> cercap.cardiopulmonar@gmail.com</p>
                <p class="f-9 mb-1"><strong>www.cercap.mx</strong></p>
            </div>
        </div>
    </footer>
</body>
</html>
