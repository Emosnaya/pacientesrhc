<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Historia Clínica Dental</title>
    <style>
        /* Evitar que el PDF corte contenido: permitir saltos de página */
        html, body {
            margin: 0;
            padding: 0;
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
        }
        .container-fluid {
            overflow: visible !important;
            page-break-inside: auto;
        }
        /* Títulos de sección: evitar que queden solos al final de la página */
        .section-title {
            page-break-after: avoid;
        }
        /* Tablas: permitir que se partan entre páginas */
        table {
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        /* Bloques de texto largos que puedan fluir */
        p {
            page-break-inside: auto;
        }
        /* Firma: evitar que quede sola en nueva hoja; preferir al final de la primera */
        .firma-section {
            page-break-before: avoid;
            page-break-inside: avoid;
        }
        /* Estilo para el logo */
        .logo-container {
            height: 60px;
            overflow: hidden;
            display: inline-block;
        }
        .logo-container img {
            height: 60px;
            width: auto;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            margin: 0 25px 15px 15px;
            padding: 10px;
        }
        .paciente {
            font-size: 9px;
        }
        .medio {
            position: relative;
            margin-bottom: 6px;
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
        .f-bold {
            font-weight: bold;
        }
        .f-normal {
            font-weight: normal;
        }
        .f-10 {
            font-size: 9px;
        }
        .f-12 {
            font-size: 10px;
        }
        .f-15 {
            font-size: 12px;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .header-info td {
            border: none;
            padding: 4px 8px;
            font-size: 10px;
            vertical-align: top;
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
            padding: 3px 5px;
            margin-top: 5px;
            margin-bottom: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .check-item {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 2px;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
            margin-bottom: 3px;
        }
        table td {
            padding: 2px 4px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        .no-border td {
            border: none;
        }
        .firma-section {
            margin-top: 15px;
            padding-top: 8px;
            text-align: center;
        }
        .firma-image {
            max-width: 150px;
            max-height: 50px;
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
        <header style="margin-bottom: 8px;">
            <div class="paciente ma-t-0 mb-0">
                <p class="f-bold f-15 text-center mb-0 mt-0">Historia Clínica Dental</p>
                @if(!empty($clinicaLogo))
                <div class="logo-container"><img src="{{ $clinicaLogo }}" alt="Logo"></div>
                @endif
                <div class="medio">
                    <p class="texto-izquierda mb-0 f-bold">Fecha: {{ $data->fecha ? $data->fecha->format('d/m/Y') : 'N/A' }}</p>
                    <span class="texto-derecha f-bold">Lugar: {{ $data->lugar ?? 'N/A' }}</span>
                </div>
                <br>
            </div>
        </header>

        <!-- Datos del Paciente -->
        <div class="section-title mt-0">DATOS DEL PACIENTE</div>
        <table class="no-border">
            <tr>
                <td><span class="f-bold">Nombre:</span> {{ $paciente->nombre }} {{ $paciente->apellidoPat ?? '' }} {{ $paciente->apellidoMat ?? '' }}</td>
                <td><span class="f-bold">Edad:</span> {{ $paciente->fechaNacimiento ? \Carbon\Carbon::parse($paciente->fechaNacimiento)->age : ($paciente->edad ?? '') }} años</td>
            </tr>
            <tr>
                <td><span class="f-bold">Género:</span> {{ ($paciente->genero ?? '') == 1 || ($paciente->genero ?? '') === 'masculino' ? 'Masculino' : 'Femenino' }}</td>
                <td><span class="f-bold">Teléfono:</span> {{ $paciente->telefono ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td colspan="2"><span class="f-bold">Dirección:</span> {{ $paciente->domicilio ?? $paciente->direccion ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Información Médica General -->
        <div class="section-title">INFORMACIÓN MÉDICA GENERAL</div>
        
        @if($data->medicamentos_actuales && count($data->medicamentos_actuales) > 0)
        <table class="no-border">
            <tr>
                <th colspan="2" style="background-color: #f0f0f0; padding: 8px; text-align: left;">Medicamentos Actuales</th>
            </tr>
            @foreach($data->medicamentos_actuales as $index => $medicamento)
            <tr>
                <td><span class="f-bold">Medicamento {{ $index + 1 }}:</span> {{ $medicamento['medicamento'] ?? 'N/A' }}</td>
                <td><span class="f-bold">Dosis:</span> {{ $medicamento['dosis'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </table>
        @endif
        
        <table class="no-border">
            <tr>
                <td><span class="f-bold">¿Alérgico a anestésicos?</span> {{ $data->alergico_anestesicos ? 'Sí' : 'No' }}</td>
                <td><span class="f-bold">¿Alérgico a medicamentos?</span> {{ $data->alergico_medicamentos ? 'Sí' : 'No' }}</td>
            </tr>
            @if($data->alergico_anestesicos && $data->anestesicos_detalle)
            <tr>
                <td colspan="2"><span class="f-bold">Detalles anestésicos:</span> {{ $data->anestesicos_detalle }}</td>
            </tr>
            @endif
            @if($data->alergico_medicamentos && $data->medicamentos_alergicos_detalle)
            <tr>
                <td colspan="2"><span class="f-bold">Medicamentos alérgicos:</span> {{ $data->medicamentos_alergicos_detalle }}</td>
            </tr>
            @endif
            <tr>
                <td><span class="f-bold">¿Embarazada?</span> {{ $data->embarazada ? 'Sí' : 'No' }}</td>
                <td><span class="f-bold">¿Toma anticonceptivos?</span> {{ $data->toma_anticonceptivos ? 'Sí' : 'No' }}</td>
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
                <td colspan="2"><span class="f-bold">Última visita al odontólogo:</span> {{ $data->ultima_visita_odontologo ? \Carbon\Carbon::parse($data->ultima_visita_odontologo)->format('d/m/Y') : 'N/A' }}</td>
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
            <input type="checkbox" {{ $data->ip_diabetes ? 'checked' : '' }} disabled> Diabetes
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ip_hipertension ? 'checked' : '' }} disabled> Hipertensión
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ip_veneras ? 'checked' : '' }} disabled> VIH/Enf. Venéreas
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ip_cancer ? 'checked' : '' }} disabled> Cáncer
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ip_asma ? 'checked' : '' }} disabled> Asma
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ip_epilepsia ? 'checked' : '' }} disabled> Epilepsia
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ip_cardiacas ? 'checked' : '' }} disabled> Enf. Cardiacas
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ip_gastricas ? 'checked' : '' }} disabled> Enf. Gástricas
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ip_cicatriz ? 'checked' : '' }} disabled> Cicatriz
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ip_presion_alta_baja ? 'checked' : '' }} disabled> Presión alta/baja
        </div>

        <!-- Antecedentes Toxicológicos -->
        <div class="section-title">ANTECEDENTES TOXICOLÓGICOS</div>
        <table class="no-border">
            <tr>
                <td><span class="f-bold">¿Fuma?</span> {{ $data->at_fuma ? 'Sí' : 'No' }}</td>
                <td>{{ $data->at_fuma_detalle ?? '' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">¿Consume drogas?</span> {{ $data->at_drogas ? 'Sí' : 'No' }}</td>
                <td>{{ $data->at_drogas_detalle ?? '' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">¿Consume alcohol?</span> {{ $data->at_toma ? 'Sí' : 'No' }}</td>
                <td>{{ $data->at_toma_detalle ?? '' }}</td>
            </tr>
        </table>

        <!-- Antecedentes Ginecoobstétricos (si aplica) -->
        @php $generoFemenino = ($paciente->genero ?? '') == 0 || ($paciente->genero ?? '') === 'femenino'; @endphp
        @if($generoFemenino && ($data->ag_menarca || $data->ag_menopausia || $data->ag_embarazo || $data->ag_menarca_edad || $data->ag_menopausia_edad))
        <div class="section-title">ANTECEDENTES GINECOOBSTÉTRICOS</div>
        <table class="no-border">
            @if($data->ag_menarca && $data->ag_menarca_edad)
            <tr>
                <td><span class="f-bold">Edad de menarca:</span> {{ $data->ag_menarca_edad }} años</td>
            </tr>
            @endif
            @if($data->ag_menopausia && $data->ag_menopausia_edad)
            <tr>
                <td><span class="f-bold">Edad de menopausia:</span> {{ $data->ag_menopausia_edad }} años</td>
            </tr>
            @endif
            @if($data->ag_embarazo)
            <tr>
                <td><span class="f-bold">Embarazo:</span> Sí</td>
            </tr>
            @endif
        </table>
        @endif

        <!-- Antecedentes Odontológicos -->
        <div class="section-title">ANTECEDENTES ODONTOLÓGICOS</div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_limpieza_6meses ? 'checked' : '' }} disabled> Limpieza últimos 6 meses
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_sangrado ? 'checked' : '' }} disabled> Sangrado de encías
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_dolor_masticar ? 'checked' : '' }} disabled> Dolor al masticar
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_tratamiento_ortodoncia ? 'checked' : '' }} disabled> Ortodoncia previa
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_morder_labios ? 'checked' : '' }} disabled> Morder labios
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_dieta_dulces ? 'checked' : '' }} disabled> Dieta rica en dulces
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_cepilla_dientes ? 'checked' : '' }} disabled> Cepilla dientes
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_trauma_cara ? 'checked' : '' }} disabled> Trauma en cara
        </div>
        <div class="check-item">
            <input type="checkbox" {{ $data->ao_dolor_abrir ? 'checked' : '' }} disabled> Dolor al abrir boca
        </div>

        <!-- Examen Tejidos Blandos -->
        <div class="section-title">EXAMEN DE TEJIDOS BLANDOS</div>
        <table>
            <tr>
                <td><span class="f-bold">Carrillos:</span> {{ $data->etb_carrillos ?? 'N/A' }}</td>
                <td><span class="f-bold">Encías:</span> {{ $data->etb_encias ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">Lengua:</span> {{ $data->etb_lengua ?? 'N/A' }}</td>
                <td><span class="f-bold">Paladar:</span> {{ $data->etb_paladar ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">ATM:</span> {{ $data->etb_atm ?? 'N/A' }}</td>
                <td><span class="f-bold">Labios:</span> {{ $data->etb_labios ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Signos Vitales -->
        <div class="section-title">SIGNOS VITALES</div>
        <table class="no-border">
            <tr>
                <td><span class="f-bold">TA:</span> {{ $data->sv_ta ?? 'N/A' }}</td>
                <td><span class="f-bold">Pulso:</span> {{ $data->sv_pulso ?? 'N/A' }}</td>
                <td><span class="f-bold">FC:</span> {{ $data->sv_fc ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><span class="f-bold">Peso:</span> {{ $data->sv_peso ?? 'N/A' }} kg</td>
                <td><span class="f-bold">Altura:</span> {{ $data->sv_altura ?? 'N/A' }} cm</td>
                <td></td>
            </tr>
        </table>

        @if(isset($firmaBase64) && $firmaBase64)
        <!-- Firma Digital -->
        <div class="firma-section">
            <img src="{{ $firmaBase64 }}" alt="Firma Digital" class="firma-image">
            <p class="f-bold mb-0">{{ $user->nombre_con_titulo }}</p>
            @if($user->cedula)
            <p class="f-7 mb-0">Cédula Profesional: {{ $user->cedula }}</p>
            @endif
            <hr style="width: 200px; margin: 5px auto;">
            <p class="f-10 mb-0">Firma del Doctor</p>
        </div>
        @endif
    </div>
</body>
</html>
