<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Historia Clínica Dental</title>
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
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
        }
        .paciente {
            font-size: 11px;
        }
        .f-bold {
            font-weight: bold;
        }
        .f-normal {
            font-weight: normal;
        }
        .f-10 {
            font-size: 10px;
        }
        .f-12 {
            font-size: 12px;
        }
        .f-15 {
            font-size: 14px;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .medio {
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
        .mb-0 {
            margin-bottom: 0;
        }
        .mt-0 {
            margin-top: 0;
        }
        .mb-1 {
            margin-bottom: 0.25rem;
        }
        .section-title {
            background-color: #e8f4f8;
            padding: 5px;
            margin-top: 10px;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 12px;
        }
        .check-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table td {
            padding: 3px 5px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        .no-border td {
            border: none;
        }
        .firma-section {
            margin-top: 30px;
            text-align: center;
        }
        .firma-image {
            max-width: 200px;
            max-height: 60px;
        }
        hr {
            border: none;
            border-top: 1px solid #333;
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <header class="mb-0">
            <div class="paciente mt-0 mb-0">
                <p class="f-bold f-15 text-center mb-0 mt-0">Historia Clínica Dental</p>
                <div class="logo-container"><img src="{{ $clinicaLogo }}" alt="logo clínica"></div>
                <div class="medio">
                    <p class="texto-izquierda mb-0 f-bold">Fecha: {{ date('d/m/Y', strtotime($data->fecha)) }}</p>
                    <p class="texto-derecha mb-0 f-bold">Lugar: {{ $data->lugar }}</p>
                </div>
            </div>
        </header>

        <!-- Datos del Paciente -->
        <div class="section-title">DATOS DEL PACIENTE</div>
        <table class="no-border">
            <tr>
                <td><span class="f-bold">Nombre:</span> {{ $paciente->nombre }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}</td>
                <td><span class="f-bold">Edad:</span> {{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age }} años</td>
            </tr>
            <tr>
                <td><span class="f-bold">Género:</span> {{ $paciente->genero == 'masculino' ? 'Masculino' : 'Femenino' }}</td>
                <td><span class="f-bold">Teléfono:</span> {{ $paciente->telefono }}</td>
            </tr>
            <tr>
                <td colspan="2"><span class="f-bold">Dirección:</span> {{ $paciente->direccion }}</td>
            </tr>
        </table>

        <!-- Información Médica General -->
        <div class="section-title">INFORMACIÓN MÉDICA GENERAL</div>
        <table class="no-border">
            <tr>
                <td><span class="f-bold">¿Toma algún medicamento?</span> {{ $data->toma_medicamento ? 'Sí' : 'No' }}</td>
                <td><span class="f-bold">¿Alérgico a anestésicos?</span> {{ $data->alergico_anestesicos ? 'Sí' : 'No' }}</td>
            </tr>
            @if($data->alergico_anestesicos && $data->detalles_alergia_anestesicos)
            <tr>
                <td colspan="2"><span class="f-bold">Detalles:</span> {{ $data->detalles_alergia_anestesicos }}</td>
            </tr>
            @endif
            <tr>
                <td><span class="f-bold">¿Alérgico a medicamentos?</span> {{ $data->alergico_medicamentos ? 'Sí' : 'No' }}</td>
                <td><span class="f-bold">¿Embarazada?</span> {{ $data->embarazada ? 'Sí' : 'No' }}</td>
            </tr>
            @if($data->alergico_medicamentos && $data->detalles_alergia_medicamentos)
            <tr>
                <td colspan="2"><span class="f-bold">Medicamentos:</span> {{ $data->detalles_alergia_medicamentos }}</td>
            </tr>
            @endif
            @if($data->embarazada && $data->meses_embarazo)
            <tr>
                <td colspan="2"><span class="f-bold">Meses de embarazo:</span> {{ $data->meses_embarazo }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="2"><span class="f-bold">¿Toma anticonceptivos?</span> {{ $data->toma_anticonceptivos ? 'Sí' : 'No' }}</td>
            </tr>
        </table>

        <!-- Información Dental -->
        <div class="section-title">INFORMACIÓN DENTAL</div>
        <div class="check-item">
            <input type="checkbox" {{ $data->mal_aliento ? 'checked' : '' }} disabled> Mal aliento
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->hipersensibilidad_dental ? 'checked' : '' }} disabled> Hipersensibilidad dental
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->respira_boca ? 'checked' : '' }} disabled> Respira por la boca
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->muerde_unas ? 'checked' : '' }} disabled> Muerde uñas
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->muerde_labios ? 'checked' : '' }} disabled> Muerde labios
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->aprieta_dientes ? 'checked' : '' }} disabled> Aprieta dientes
        </div>
        <br>
        <table class="no-border" style="margin-top: 5px;">
            <tr>
                <td><span class="f-bold">Veces que cepilla al día:</span> {{ $data->veces_cepilla_dia ?? 'N/A' }}</td>
                <td><span class="f-bold">Método de higienización:</span> {{ $data->higienizacion_metodo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td colspan="2"><span class="f-bold">Última visita al odontólogo:</span> {{ $data->ultima_visita_odontologo ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Historia y Motivo -->
        <div class="section-title">HISTORIA Y MOTIVO DE CONSULTA</div>
        <p class="f-10"><span class="f-bold">Historia de la enfermedad actual:</span><br>{{ $data->historia_enfermedad ?? 'N/A' }}</p>
        <p class="f-10"><span class="f-bold">Motivo de consulta:</span><br>{{ $data->motivo_consulta ?? 'N/A' }}</p>

        <!-- Antecedentes Familiares -->
        <div class="section-title">ANTECEDENTES FAMILIARES</div>
        <div class="check-item">
            <input type="checkbox" {{ $data->af_diabetes ? 'checked' : '' }} disabled> Diabetes
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->af_hipertension ? 'checked' : '' }} disabled> Hipertensión
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->af_cancer ? 'checked' : '' }} disabled> Cáncer
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->af_cardiacas ? 'checked' : '' }} disabled> Enf. Cardiacas
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->af_vih ? 'checked' : '' }} disabled> VIH
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->af_epilepsia ? 'checked' : '' }} disabled> Epilepsia
        </div>

        <!-- Antecedentes Patológicos -->
        <div class="section-title">INFORMACIÓN PATOLÓGICA</div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ap_diabetes ? 'checked' : '' }} disabled> Diabetes
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ap_hipertension ? 'checked' : '' }} disabled> Hipertensión
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ap_vih ? 'checked' : '' }} disabled> VIH
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ap_hepatitis ? 'checked' : '' }} disabled> Hepatitis
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ap_cancer ? 'checked' : '' }} disabled> Cáncer
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ap_tuberculosis ? 'checked' : '' }} disabled> Tuberculosis
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ap_asma ? 'checked' : '' }} disabled> Asma
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ap_epilepsia ? 'checked' : '' }} disabled> Epilepsia
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ap_cardiacas ? 'checked' : '' }} disabled> Enf. Cardiacas
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ap_renales ? 'checked' : '' }} disabled> Enf. Renales
        </div>

        <!-- Antecedentes Toxicológicos -->
        <div class="section-title">ANTECEDENTES TOXICOLÓGICOS</div>
        <table class="no-border">
            <tr>
                <td><span class="f-bold">¿Fuma?</span> {{ $data->fuma ? 'Sí' : 'No' }}</td>
                <td>{{ $data->fuma_detalles ?? '' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">¿Consume drogas?</span> {{ $data->drogas ? 'Sí' : 'No' }}</td>
                <td>{{ $data->drogas_detalles ?? '' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">¿Consume alcohol?</span> {{ $data->alcohol ? 'Sí' : 'No' }}</td>
                <td>{{ $data->alcohol_detalles ?? '' }}</td>
            </tr>
        </table>

        <!-- Antecedentes Ginecoobstétricos (si aplica) -->
        @if($paciente->genero == 'femenino' && ($data->menarca || $data->menopausia || $data->embarazos))
        <div class="section-title">ANTECEDENTES GINECOOBSTÉTRICOS</div>
        <table class="no-border">
            @if($data->menarca)
            <tr>
                <td><span class="f-bold">Edad de menarca:</span> {{ $data->menarca }} años</td>
            </tr>
            @endif
            @if($data->menopausia)
            <tr>
                <td><span class="f-bold">Edad de menopausia:</span> {{ $data->menopausia }} años</td>
            </tr>
            @endif
            @if($data->embarazos)
            <tr>
                <td><span class="f-bold">Número de embarazos:</span> {{ $data->embarazos }}</td>
            </tr>
            @endif
        </table>
        @endif

        <!-- Antecedentes Odontológicos -->
        <div class="section-title">ANTECEDENTES ODONTOLÓGICOS</div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_limpieza_previa ? 'checked' : '' }} disabled> Limpieza previa
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_sangrado_encias ? 'checked' : '' }} disabled> Sangrado de encías
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_dolor_masticar ? 'checked' : '' }} disabled> Dolor al masticar
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_ortodoncia_previa ? 'checked' : '' }} disabled> Ortodoncia previa
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_tratamiento_conductos ? 'checked' : '' }} disabled> Tratamiento de conductos
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_cirugia_bucal ? 'checked' : '' }} disabled> Cirugía bucal
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_protesis ? 'checked' : '' }} disabled> Prótesis
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_carillas ? 'checked' : '' }} disabled> Carillas
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_blanqueamiento ? 'checked' : '' }} disabled> Blanqueamiento
        </div>

        <!-- Examen Tejidos Blandos -->
        <div class="section-title">EXAMEN DE TEJIDOS BLANDOS</div>
        <table>
            <tr>
                <td><span class="f-bold">Carrillos:</span> {{ $data->carrillos ?? 'N/A' }}</td>
                <td><span class="f-bold">Encías:</span> {{ $data->encias ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">Lengua:</span> {{ $data->lengua ?? 'N/A' }}</td>
                <td><span class="f-bold">Paladar:</span> {{ $data->paladar ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">ATM:</span> {{ $data->atm ?? 'N/A' }}</td>
                <td><span class="f-bold">Labios:</span> {{ $data->labios ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Signos Vitales -->
        <div class="section-title">SIGNOS VITALES</div>
        <table class="no-border">
            <tr>
                <td><span class="f-bold">TA:</span> {{ $data->ta ?? 'N/A' }}</td>
                <td><span class="f-bold">Pulso:</span> {{ $data->pulso ?? 'N/A' }}</td>
                <td><span class="f-bold">FC:</span> {{ $data->fc ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">Peso:</span> {{ $data->peso ?? 'N/A' }} kg</td>
                <td><span class="f-bold">Altura:</span> {{ $data->altura ?? 'N/A' }} cm</td>
                <td></td>
            </tr>
        </table>

        <!-- Firma Digital -->
        <div class="firma-section">
            @if(isset($firmaBase64) && $firmaBase64)
                <img src="{{ $firmaBase64 }}" alt="Firma Digital" class="firma-image">
            @endif
            <p class="f-bold mb-0">{{ $data->nombre_doctor ?? 'Dr. ' . $user->nombre . ' ' . $user->apellido_paterno }}</p>
            <p class="f-10 mb-0">Cédula Profesional: {{ $data->cedula_profesional ?? $user->cedula_profesional ?? 'N/A' }}</p>
            <hr style="width: 200px; margin: 5px auto;">
            <p class="f-10 mb-0">Firma del Doctor</p>
        </div>
    </div>
</body>
</html>
