#!/usr/bin/env php
<?php
/**
 * Script para generar SQL de importación masiva desde CSV
 * Uso: php generate_import_sql.php
 * 
 * Genera: import_bulk.sql con los INSERT statements
 */

$csvFile = __DIR__ . '/import_data.csv';
$outputFile = __DIR__ . '/import_bulk.sql';

// Función para convertir fechas del formato español al formato MySQL
function convertDate($dateStr) {
    if (empty($dateStr) || $dateStr === 'n' || $dateStr === 's') {
        return 'NULL';
    }
    
    $meses = [
        'ene' => '01', 'feb' => '02', 'mar' => '03', 'abr' => '04',
        'may' => '05', 'jun' => '06', 'jul' => '07', 'ago' => '08',
        'sep' => '09', 'oct' => '10', 'nov' => '11', 'dic' => '12'
    ];
    
    // Formato: "21-ago-21"
    $parts = explode('-', strtolower($dateStr));
    if (count($parts) === 3) {
        $day = (int)$parts[0];
        $monthStr = $parts[1];
        $year = $parts[2];
        
        // Validar día (1-31)
        if ($day < 1 || $day > 31) {
            return 'NULL';
        }
        
        $day = str_pad($day, 2, '0', STR_PAD_LEFT);
        $month = $meses[$monthStr] ?? '01';
        
        // Convertir año de 2 dígitos a 4
        if (strlen($year) === 2) {
            $year = (int)$year <= 25 ? '20' . $year : '19' . $year;
        }
        
        // Validar fecha con checkdate
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            return 'NULL';
        }
        
        return "'$year-$month-$day'";
    }
    
    return 'NULL';
}

// Función para convertir s/n a booleano
function convertBool($value) {
    if (empty($value)) return 'NULL';
    $value = strtolower(trim($value));
    if ($value === 's' || $value === '1' || $value === 'true') return '1';
    if ($value === 'n' || $value === '0' || $value === 'false') return '0';
    return 'NULL';
}

// Función para convertir género f/m a 0/1
function convertGender($value) {
    $value = strtolower(trim($value));
    if ($value === 'f') return '0'; // Femenino
    if ($value === 'm') return '1'; // Masculino
    return '1'; // Default masculino
}

// Función para convertir booleanos (s/n a 1/0)
function convertBoolean($value) {
    $val = strtolower(trim($value));
    if ($val === 's' || $val === 'si' || $val === 'sí' || $val === '1' || $val === 'true') {
        return 1;
    }
    return 0;
}

// Función para escapar strings SQL
function escapeSql($value) {
    if ($value === null || $value === '' || strtolower($value) === 'no tiene') {
        return 'NULL';
    }
    $value = str_replace("'", "''", $value);
    return "'" . $value . "'";
}

// Función para convertir números
function convertNumber($value) {
    if (empty($value) || !is_numeric($value)) return 'NULL';
    return $value;
}

// Función para calcular edad desde fecha de nacimiento
function calculateAge($fechaNac) {
    if (empty($fechaNac)) return 'NULL';
    
    $parts = explode('-', $fechaNac);
    if (count($parts) === 3) {
        $birthDate = new DateTime($parts[2] . '-' . $parts[1] . '-' . $parts[0]);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        return $age;
    }
    return 'NULL';
}

// Leer CSV
if (!file_exists($csvFile)) {
    die("Error: No se encontró el archivo CSV\n");
}

// Leer el CSV correctamente con manejo de comillas
$csv = array_map(function($line) {
    return str_getcsv($line, ',', '"', '\\');
}, file($csvFile));
$headers = array_shift($csv);

// Crear archivo SQL
$sql = fopen($outputFile, 'w');
fwrite($sql, "-- Script de importación masiva de pacientes\n");
fwrite($sql, "-- Generado: " . date('Y-m-d H:i:s') . "\n");
fwrite($sql, "-- Total de registros: " . count($csv) . "\n\n");
fwrite($sql, "SET FOREIGN_KEY_CHECKS=0;\n");
fwrite($sql, "SET @user_id = 3;\n");
fwrite($sql, "SET @clinica_id = 1;\n\n");

$pacienteId = 1;

// Crear mapeo de índices de columnas para evitar problemas con headers duplicados
$colIndex = [];
foreach ($headers as $i => $header) {
    if (!isset($colIndex[$header])) {
        $colIndex[$header] = $i;
    }
}

foreach ($csv as $rowIndex => $row) {
    if (count($row) < 10) continue; // Saltar filas vacías
    
    // Verificar que coincidan headers y row
    if (count($headers) !== count($row)) {
        echo "⚠️  Advertencia en línea " . ($rowIndex + 2) . ": headers=" . count($headers) . ", columns=" . count($row) . "\n";
        // Ajustar array si es necesario
        if (count($row) < count($headers)) {
            $row = array_pad($row, count($headers), '');
        } else {
            $row = array_slice($row, 0, count($headers));
        }
    }
    
    // Crear array asociativo usando solo el PRIMER índice de cada header
    $data = [];
    foreach ($colIndex as $headerName => $idx) {
        $data[$headerName] = $row[$idx] ?? '';
    }
    
    // Extraer datos del paciente
    $registro = escapeSql($data['Registro'] ?? '');
    $nombreCompleto = trim($data['Nombre'] ?? '');
    
    // Validar que tenga nombre completo
    if (empty($nombreCompleto)) {
        $lineNum = $rowIndex + 2;
        echo "⚠️  Línea $lineNum: Nombre vacío - Registro: $registro\n";
        fwrite($sql, "-- Línea CSV $lineNum: Registro $registro omitido (sin nombre completo)\n");
        continue;
    }
    
    // Separar nombre completo en apellidos y nombre
    $nombrePartes = explode(' ', $nombreCompleto);
    $totalPalabras = count($nombrePartes);
    
    if ($totalPalabras >= 3) {
        // Formato: Apellido1 Apellido2 Nombre(s)
        $apellidoPat = $nombrePartes[0];
        $apellidoMat = $nombrePartes[1];
        $nombre = implode(' ', array_slice($nombrePartes, 2));
    } else if ($totalPalabras == 2) {
        // Formato: Apellido Nombre
        $apellidoPat = $nombrePartes[0];
        $apellidoMat = '';
        $nombre = $nombrePartes[1];
    } else {
        // Solo 1 palabra - usar como nombre
        $apellidoPat = '';
        $apellidoMat = '';
        $nombre = $nombrePartes[0];
    }
    
    // Validar que tenga al menos nombre o apellido
    if (empty($nombre) && empty($apellidoPat)) {
        fwrite($sql, "-- Registro $registro omitido: sin nombre ni apellido\n");
        continue;
    }
    
    $telefono = escapeSql($data['Teléfono'] ?? $data['Tel.'] ?? '');
    $fechaNac = $data['Fecha de Nacimiento'] ?? '';
    $fechaNacSQL = convertDate($fechaNac);
    $edad = $data['Edad'] ?? calculateAge($fechaNac);
    $genero = convertGender($data['Género (f/m)'] ?? 'm');
    $estadoCivil = escapeSql($data['Estado civil'] ?? '');
    $profesion = escapeSql($data['Profesión'] ?? '');
    $domicilio = escapeSql($data['Domicilio'] ?? '');
    
    // Medidas antropométricas
    $talla = convertNumber($data['Talla'] ?? 0);
    $peso = convertNumber($data['Peso (Inicio)'] ?? 0);
    $cintura = convertNumber($data['Cintura'] ?? 0);
    $imc = convertNumber($data['IMC (Inicio)'] ?? 0);
    
    // Datos clínicos
    $diagnostico = escapeSql($data['Diagnósticos'] ?? '');
    $envio = escapeSql($data['Envío'] ?? '');
    
    // Medicamentos (concatenar todas las columnas de medicamentos)
    $medicamentos = [];
    $medCols = ['Betabloqueador (Nombre/dosis)', 'Nitratos (Nombre/dosis)', 'Calcioantagonista (Nombre/dosis)', 
                'Aspirina (Nombre/dosis)', 'Anticoagulación (Nombre/dosis)', 'IECAS (Nombre/dosis)', 
                'ATII (Nombre/dosis)', 'Diuréticos(Nombre/dosis)', 'Estatinas(Nombre/dosis)', 
                'Fibratos (Nombre/dosis)', 'Digoxina (Nombre/dosis)', 'Antiarrítmicos (Nombre/dosis)', 
                'Otros (Nombre/dosis)'];
    
    foreach ($medCols as $col) {
        if (!empty($data[$col]) && $data[$col] !== 'n') {
            $medicamentos[] = $data[$col];
        }
    }
    $medicamentosJoin = implode(', ', $medicamentos);
    // Truncar a 255 caracteres para que quepa en VARCHAR(255)
    if (strlen($medicamentosJoin) > 255) {
        $medicamentosJoin = substr($medicamentosJoin, 0, 252) . '...';
    }
    $medicamentosStr = escapeSql($medicamentosJoin);
    
    // Verificar si el paciente ya existe (por nombre y apellidos)
    $sqlCheck = "-- Verificar paciente: $apellidoPat $apellidoMat $nombre\n";
    $sqlCheck .= "SET @existing_patient_id = (\n";
    $sqlCheck .= "    SELECT id FROM pacientes \n";
    $sqlCheck .= "    WHERE nombre = " . escapeSql($nombre) . " \n";
    $sqlCheck .= "    AND apellidoPat = " . escapeSql($apellidoPat) . " \n";
    $sqlCheck .= "    AND apellidoMat = " . escapeSql($apellidoMat) . " \n";
    $sqlCheck .= "    AND user_id = @user_id \n";
    $sqlCheck .= "    AND clinica_id = @clinica_id \n";
    $sqlCheck .= "    LIMIT 1\n";
    $sqlCheck .= ");\n\n";
    
    fwrite($sql, $sqlCheck);
    
    // INSERT de paciente solo si no existe
    $sqlPaciente = "INSERT INTO pacientes (registro, nombre, apellidoPat, apellidoMat, telefono, fechaNacimiento, edad, genero, estadoCivil, profesion, domicilio, talla, peso, cintura, imc, medicamentos, diagnostico, envio, user_id, clinica_id, created_at, updated_at)\n";
    $sqlPaciente .= "SELECT $registro, " . escapeSql($nombre) . ", " . escapeSql($apellidoPat) . ", " . escapeSql($apellidoMat) . ", $telefono, $fechaNacSQL, " . escapeSql($edad) . ", $genero, $estadoCivil, $profesion, $domicilio, $talla, $peso, $cintura, $imc, $medicamentosStr, $diagnostico, $envio, @user_id, @clinica_id, NOW(), NOW()\n";
    $sqlPaciente .= "WHERE @existing_patient_id IS NULL;\n\n";
    
    fwrite($sql, $sqlPaciente);
    
    // Obtener el ID (ya sea el recién insertado o el existente)
    $sqlGetId = "SET @patient_id = COALESCE(@existing_patient_id, LAST_INSERT_ID());\n\n";
    fwrite($sql, $sqlGetId);
    
    // INSERT de clínico - usar la fecha de la primera columna del CSV
    $fecha = convertDate($data['Fecha'] ?? '');
    $fecha1vez = convertDate($data['1a vez (fecha)'] ?? '');
    $hora = escapeSql($data['Hora'] ?? '');
    
    // Fechas de eventos cardíacos
    $imAnterior = convertDate($data['IM Anterior (fecha)'] ?? '');
    $imSeptal = convertDate($data['IM Septal (fecha)'] ?? '');
    $imApical = convertDate($data['IM Apical (fecha)'] ?? '');
    $imLateral = convertDate($data['IM Lateral (fecha)'] ?? '');
    $imInferior = convertDate($data['IM Inferior (fecha)'] ?? '');
    $imVD = convertDate($data['IM del VD (fecha)'] ?? '');
    $anginaInestable = convertDate($data['Angina Inestable (fecha)'] ?? '');
    $anginaEstable = convertDate($data['Angor Estable (desde cuando)'] ?? '');
    $choqueCard = convertDate($data['Choque Card. (fecha)'] ?? '');
    $mSubita = convertDate($data['M. Súbita (fecha)'] ?? '');
    
    // Clasificaciones
    $claseFCCS = convertNumber($data['Clase F CCS'] ?? '');
    $fallaCardiaca = convertBool($data['Falla Cardiaca'] ?? '');
    $cfNYHA = convertNumber($data['CF NYHA'] ?? '');
    $crvc = convertDate($data['CRVC (fecha)'] ?? '');
    $crvcHemoductos = escapeSql($data['CRVC (Hemoductos)'] ?? '');
    
    // Valvulopatías
    $insufArtPer = convertBool($data['Insuficiencia Arterial Periférica (s/n)'] ?? '');
    $vMitral = convertBool($data['V. Mitral'] ?? '');
    $vAortica = convertBool($data['V. Aórtica'] ?? '');
    $vTricuspide = convertBool($data['V. Tricúspide'] ?? '');
    $vPulmonar = convertBool($data['V. Pulmonar'] ?? '');
    $congenitos = convertBool($data['Congénitos'] ?? '');
    
    // Fechas de rehabilitación
    $estratificacion = convertDate($data['Estratificación (fecha)'] ?? '');
    $inicioFase2 = convertDate($data['Inicio Fase II (fecha)'] ?? '');
    $finFase2 = convertDate($data['Fin Fase II (fecha)'] ?? '');
    
    // Factores de riesgo
    $tabaquismo = convertBool($data['Tabaquismo (s/n)'] ?? '');
    $cigDia = convertNumber($data['Cig/día'] ?? '');
    $cigYears = convertNumber($data['Fumó (# años)'] ?? '');
    $cigAbandono = convertBool($data['Abandonó (s/n)'] ?? '');
    $cigAñosAbandono = convertNumber($data['Años de abandono'] ?? '');
    $hipertensionAños = convertNumber($data['Hipertensión (años)'] ?? '');
    $dmYears = convertNumber($data['DM (años)'] ?? '');
    
    // Actividad física
    $actividadFis = convertBool($data['Actividad física (s/n)'] ?? '');
    $tipoActividad = escapeSql($data['Tipo'] ?? '');
    $actividadHrs = convertNumber($data['Hrs / Semana'] ?? '');
    $actividadYears = convertNumber($data['Años practicando'] ?? '');
    $actividadAbandonoYears = convertNumber($data['Años abandono'] ?? '');
    
    // Otros factores
    $estresYears = convertNumber($data['Estrés (años)'] ?? '');
    $ansiedadYears = convertNumber($data['Ansiedad (años)'] ?? '');
    $depresionYears = convertNumber($data['Depresión (años)'] ?? '');
    $hipercolesterolemiaY = convertNumber($data['Hipercolesterolemia (años)'] ?? '');
    $hipertrigliceridemiaY = convertNumber($data['Hipertrigliceridemia (años)'] ?? '');
    $diabetesY = convertNumber($data['Diabetes (años)'] ?? '');
    
    // Tratamiento
    $tiempoEvolucion = escapeSql($data['Tiempo evolución'] ?? '');
    $tratamiento = escapeSql($data['Tratamiento'] ?? '');
    $fechaTra = convertDate($data['Fecha'] ?? '');
    
    // Estos campos son booleanos en la BD (TINYINT) - convertir s/n a 1/0
    $betabloqueador = convertBoolean($data['Betabloqueador (Nombre/dosis)'] ?? '');
    $nitratos = convertBoolean($data['Nitratos (Nombre/dosis)'] ?? '');
    $calcioantagonista = convertBoolean($data['Calcioantagonista (Nombre/dosis)'] ?? '');
    $aspirina = convertBoolean($data['Aspirina (Nombre/dosis)'] ?? '');
    $anticoagulacion = convertBoolean($data['Anticoagulación (Nombre/dosis)'] ?? '');
    $iecas = convertBoolean($data['IECAS (Nombre/dosis)'] ?? '');
    $atii = convertBoolean($data['ATII (Nombre/dosis)'] ?? '');
    $diureticos = convertBoolean($data['Diuréticos(Nombre/dosis)'] ?? '');
    $estatinas = convertBoolean($data['Estatinas(Nombre/dosis)'] ?? '');
    $fibratos = convertBoolean($data['Fibratos (Nombre/dosis)'] ?? '');
    $digoxina = convertBoolean($data['Digoxina (Nombre/dosis)'] ?? '');
    $antiarritmicos = convertBoolean($data['Antiarrítmicos (Nombre/dosis)'] ?? '');
    $otros = escapeSql($data['Otros (Nombre/dosis)'] ?? '');
    
    // Laboratorios
    $bhFecha = convertDate($data['BH (fecha)'] ?? '');
    $hb = convertNumber($data['Hb'] ?? '');
    $leucos = convertNumber($data['Leucos'] ?? '');
    $plaquetas = convertNumber($data['Plaquetas'] ?? '');
    $qs = convertDate($data['QS (fecha)'] ?? '');
    $glucosa = convertNumber($data['Glucosa'] ?? '');
    $creatinina = convertNumber($data['Creatinina'] ?? '');
    $acUnico = convertNumber($data['Ac. Úrico'] ?? '');
    $colesterol = convertNumber($data['Colesterol'] ?? '');
    $ldl = convertNumber($data['LDL'] ?? '');
    $hdl = convertNumber($data['HDL'] ?? '');
    $trigliceridos = convertNumber($data['Triglicéridos'] ?? '');
    $tp = convertNumber($data['TP'] ?? '');
    $inr = convertNumber($data['INR'] ?? '');
    $tpt = convertNumber($data['TPT'] ?? '');
    $pcras = convertNumber($data['PCRas'] ?? '');
    $otroLab = escapeSql($data['Otros'] ?? '');
    
    // ECG
    $ecgFecha = convertDate($data['Fecha'] ?? '');
    $ritmo = escapeSql($data['Ritmo'] ?? '');
    $rRmm = convertNumber($data['R-R (mm)'] ?? '');
    $fcEcog = convertNumber($data['FC'] ?? '');
    $aP = convertNumber($data['âP'] ?? '');
    $aQRS = convertNumber($data['âQRS'] ?? '');
    $aT = convertNumber($data['âT'] ?? '');
    $duracionQrs = convertNumber($data['Duración QRS'] ?? '');
    $duracionP = convertNumber($data['Duración P'] ?? '');
    $qtm = convertNumber($data['QTm'] ?? '');
    $qtc = convertNumber($data['QTc'] ?? '');
    $pr = convertNumber($data['PR'] ?? '');
    $bav = convertNumber($data['BAV (1,2,3)'] ?? '');
    $brihh = convertBool($data['BRIHH'] ?? '');
    $brdhh = convertBool($data['BRDHH'] ?? '');
    $qAs = convertBool($data['Q AS'] ?? '');
    $qInf = convertBool($data['Q inf'] ?? '');
    $qLat = convertBool($data['Q lat'] ?? '');
    $otrosEcg = escapeSql($data['Otros'] ?? '');
    
    // Ecocardiograma
    $ecoFecha = convertDate($data['Fecha'] ?? '');
    $fePor = convertNumber($data['FE(%)'] ?? '');
    $ddPor = convertNumber($data['DD(mm)'] ?? '');
    $dsPor = convertNumber($data['DS(mm)'] ?? '');
    $triviPor = escapeSql($data['TRIVI (ms)'] ?? '');
    $relEA = convertNumber($data['Rel e-A'] ?? '');
    $otrosEco = escapeSql($data['Otros'] ?? '');
    
    // Medicina Nuclear
    $mnFecha = convertDate($data['Fecha'] ?? '');
    $fePorMn = convertNumber($data['FE (%)'] ?? '');
    $antIm = convertBool($data['Ant (IM)'] ?? '');
    $antIsq = convertBool($data['Ant (isq)'] ?? '');
    $antRr = convertBool($data['Ant (RR)'] ?? '');
    $septIm = convertBool($data['Sept (IM)'] ?? '');
    $septIsq = convertBool($data['Sept (isq)'] ?? '');
    $septRr = convertBool($data['Sept (RR)'] ?? '');
    $latIm = convertBool($data['Lat (IM)'] ?? '');
    $latIsq = convertBool($data['Lat (isq)'] ?? '');
    $latRr = convertBool($data['Lat (RR)'] ?? '');
    $infIm = convertBool($data['Inf (IM)'] ?? '');
    $infIsq = convertBool($data['Inf (isq)'] ?? '');
    $infRr = convertBool($data['Inf (RR)'] ?? '');
    $vrie = convertBool($data['VRIE (s/n)'] ?? '');
    $vrieFecha = convertDate($data['Fecha VRIE'] ?? '');
    $feviBasal = convertNumber($data['FEVI (basal)'] ?? '');
    $fevi10Dobuta = convertNumber($data['FEVI (10\'dobuta)'] ?? '');
    $reservaInotAbsolut = convertNumber($data['Reserva Inot Absolut'] ?? '');
    $reservaInotRelat = convertNumber($data['Reserva Inot Relativ'] ?? '');
    $vrieOtros = escapeSql($data['Otros'] ?? '');
    
    // Cateterismo
    $cateterismo = !empty($data['Catet']) ? 1 : 0;
    $catetFecha = convertDate($data['Fecha'] ?? '');
    $catetFe = convertNumber($data['FE'] ?? '');
    $catetD2vi = convertNumber($data['D2VI'] ?? '');
    $catetTco = convertNumber($data['Tco'] ?? '');
    $catetDaProx = escapeSql($data['DA (prox)'] ?? '');
    $catetDaMed = escapeSql($data['DA (1/2)'] ?? '');
    $catetDaDist = escapeSql($data['DA(dist)'] ?? '');
    $catet1aD = convertNumber($data['1a D'] ?? '');
    $catet2aD = convertNumber($data['2a D'] ?? '');
    $catetCxProx = escapeSql($data['Cx (prox)'] ?? '');
    $catetCxDist = convertNumber($data['Cx (dist)'] ?? '');
    $catetOm = convertNumber($data['OM'] ?? '');
    $catetPl = convertNumber($data['PL'] ?? '');
    $catetCdAprox = escapeSql($data['CD (prox)'] ?? '');
    $catetCdMed = escapeSql($data['CD (1/2)'] ?? '');
    $catetCdDist = escapeSql($data['CD (dist)'] ?? '');
    $catetRVentIzq = convertNumber($data['R. Vent Izq'] ?? '');
    $catetDp = convertNumber($data['DP'] ?? '');
    $catetOtros = escapeSql($data['Otros'] ?? '');
    $catetMovilidad = escapeSql($data['Movilidad'] ?? '');
    
    // Resultados del programa
    $termino = convertBool($data['Terminó (1)'] ?? '');
    $semanas = convertNumber($data['Semanas'] ?? '');
    $aprendioBorg = convertBool($data['Aprendió Borg (1)'] ?? '');
    $muerte = convertBool($data['Muerte (1)'] ?? '');
    $inestabilidadCardio = convertBool($data['Inestabilidad cardiovascular (1)'] ?? '');
    $hospitalizacion = convertBool($data['Hospitalización'] ?? '');
    $suspMotuPropio = convertBool($data['Suspendió por "motu propio"'] ?? '');
    $lesionOsteo = convertBool($data['Lesión osteomuscular'] ?? '');
    $resOtros = convertBool($data['Otros'] ?? '');
    
    // DASI
    $eraVezFecha = convertDate($data['Nota de 1a vez (fecha)'] ?? '');
    $sintomas = escapeSql($data['Síntomas.'] ?? '');
    $dasi = convertNumber($data['DASI (METs)'] ?? '');
    $comerVestirse = convertBool($data['Puede comer, bañarse, vestirse ó ir al baño?'] ?? '');
    $caminarCasa = convertBool($data['Puede caminar dentro de casa?'] ?? '');
    $caminar2Cuadras = convertBool($data['Puede caminar 2 cuadras en plano?'] ?? '');
    $subirPiso = convertBool($data['Puede subir un piso de escaleras?'] ?? '');
    $correrCorta = convertBool($data['Puede correr una distancia corta?'] ?? '');
    $lavarTrastes = convertBool($data['Puede lavar los trastes ó sacudir el polvo?'] ?? '');
    $aspirarCasa = convertBool($data['Puede aspirar la casa ó cargar el mandado?'] ?? '');
    $trapear = convertBool($data['Puede trapear los pisos ó cargar cosas pesadas?'] ?? '');
    $jardineria = convertBool($data['Puede hacer jardinería (podar el pasto, levantar las hojas secas)?'] ?? '');
    $relaciones = convertBool($data['Tiene relaciones sexuales?'] ?? '');
    $jugar = convertBool($data['Juega golf, boliche, baila, juega tenis (dobles), futbol, ó beisbol?'] ?? '');
    $deportesExtenuantes = convertBool($data['Juega deportes extenuantes como natación, tenis (singles), futbol, basquetbol?'] ?? '');
    
    // Examen físico
    $ta = escapeSql($data['TA'] ?? '');
    $fc = convertNumber($data['FC'] ?? '');
    $exploracionFisica = escapeSql($data['Exploración Física..'] ?? '');
    $estudios = escapeSql($data['Estudios a solicitar'] ?? '');
    $diagnosticoGeneral = escapeSql($data['Diagnóstico'] ?? '');
    $plan = escapeSql($data['Plan'] ?? '');
    
    $sqlClinico = "INSERT INTO clinicos (fecha, fecha_1vez, hora, imAnterior, imSeptal, imApical, imLateral, imInferior, imdelVD, anginaInestabale, anginaEstabale, choque_card, m_subita, clase_f_ccs, falla_cardiaca, cf_nyha, crvc, crvc_hemoductos, insuficiencia_art_per, v_mitral, v_aortica, v_tricuspide, v_pulmonar, congenitos, estratificacion, inicio_fase_2, fin_fase_2, tabaquismo, cig_dia, cig_years, cig_abandono, cig_años_abandono, hipertension_años, dm_years, actividad_fis, tipo_actividad, actividad_hrs_smn, actividad_years, actividad_abadono_years, estres_years, ansiedad_years, depresion_years, hipercolesterolemia_y, hipertrigliceridemia_y, diabetes_y, tiempo_evolucion, tratamiento, fecha_tra, betabloqueador, nitratos, calcioantagonista, aspirina, anticoagulacion, iecas, atii, diureticos, estatinas, fibratos, digoxina, antiarritmicos, otros, bh_fecha, hb, leucos, plaquetas, qs, glucosa, creatinina, ac_unico, colesterol, ldl, hdl, trigliceridos, tp, inr, tpt, pcras, otro_lab, ecg_fecha, ritmo, r_r_mm, fc_ecog, aP, aQRS, aT, duracion_qrs, duracion_p, qtm, qtc, pr, bav, brihh, brdhh, q_as, q_inf, q_lat, otros_ecg, eco_fecha, fe_por, dd_por, ds_por, trivi_por, rel_e_a, otros_eco, mn_fecha, fe_por_mn, ant_im, ant_isq, ant_rr, sept_im, sept_isq, sept_rr, lat_im, lat_isq, lat_rr, inf_im, inf_isq, inf_rr, vrie, vrie_fcha, fevi_basal, fevi_10_dobuta, reserva_inot_absolut, reserva_inot_relat, vrie_otros, cateterismo, catet_fecha, catet_fe, catet_d2vi, catet_tco, catet_da_prox, catet_da_med, catet_da_dist, catet_1a_d, catet_2a_d, catet_cx_prox, catet_cx_dist, catet_om, catet_pl, catet_cd_aprox, catet_cd_med, catet_cd_dist, catet_r_vent_izq, catet_dp, catet_otros, catet_movilidad, termino, semanas, aprendio_borg, muerte, inestabilidad_cardio, hospitalizacion, susp_motu_propio, lesion_osteo, res_otros, era_vez_fecha, sintomas, dasi, comer_vestirse, caminar_casa, caminar_2_cuadras, subir_piso, correr_corta, lavar_trastes, aspirar_casa, trapear, jardineria, relaciones, jugar, deportes_extenuantes, TA, fc, exploracion_fisica, estudios, diagnostico_general, plan, user_id, paciente_id, clinica_id, tipo_exp, created_at, updated_at) VALUES\n";
    
    $sqlClinico .= "($fecha, $fecha1vez, $hora, $imAnterior, $imSeptal, $imApical, $imLateral, $imInferior, $imVD, $anginaInestable, $anginaEstable, $choqueCard, $mSubita, $claseFCCS, $fallaCardiaca, $cfNYHA, $crvc, $crvcHemoductos, $insufArtPer, $vMitral, $vAortica, $vTricuspide, $vPulmonar, $congenitos, $estratificacion, $inicioFase2, $finFase2, $tabaquismo, $cigDia, $cigYears, $cigAbandono, $cigAñosAbandono, $hipertensionAños, $dmYears, $actividadFis, $tipoActividad, $actividadHrs, $actividadYears, $actividadAbandonoYears, $estresYears, $ansiedadYears, $depresionYears, $hipercolesterolemiaY, $hipertrigliceridemiaY, $diabetesY, $tiempoEvolucion, $tratamiento, $fechaTra, $betabloqueador, $nitratos, $calcioantagonista, $aspirina, $anticoagulacion, $iecas, $atii, $diureticos, $estatinas, $fibratos, $digoxina, $antiarritmicos, $otros, $bhFecha, $hb, $leucos, $plaquetas, $qs, $glucosa, $creatinina, $acUnico, $colesterol, $ldl, $hdl, $trigliceridos, $tp, $inr, $tpt, $pcras, $otroLab, $ecgFecha, $ritmo, $rRmm, $fcEcog, $aP, $aQRS, $aT, $duracionQrs, $duracionP, $qtm, $qtc, $pr, $bav, $brihh, $brdhh, $qAs, $qInf, $qLat, $otrosEcg, $ecoFecha, $fePor, $ddPor, $dsPor, $triviPor, $relEA, $otrosEco, $mnFecha, $fePorMn, $antIm, $antIsq, $antRr, $septIm, $septIsq, $septRr, $latIm, $latIsq, $latRr, $infIm, $infIsq, $infRr, $vrie, $vrieFecha, $feviBasal, $fevi10Dobuta, $reservaInotAbsolut, $reservaInotRelat, $vrieOtros, $cateterismo, $catetFecha, $catetFe, $catetD2vi, $catetTco, $catetDaProx, $catetDaMed, $catetDaDist, $catet1aD, $catet2aD, $catetCxProx, $catetCxDist, $catetOm, $catetPl, $catetCdAprox, $catetCdMed, $catetCdDist, $catetRVentIzq, $catetDp, $catetOtros, $catetMovilidad, $termino, $semanas, $aprendioBorg, $muerte, $inestabilidadCardio, $hospitalizacion, $suspMotuPropio, $lesionOsteo, $resOtros, $eraVezFecha, $sintomas, $dasi, $comerVestirse, $caminarCasa, $caminar2Cuadras, $subirPiso, $correrCorta, $lavarTrastes, $aspirarCasa, $trapear, $jardineria, $relaciones, $jugar, $deportesExtenuantes, $ta, $fc, $exploracionFisica, $estudios, $diagnosticoGeneral, $plan, @user_id, @patient_id, @clinica_id, 3, NOW(), NOW());\n\n";
    
    fwrite($sql, $sqlClinico);
    
    $pacienteId++;
}

fwrite($sql, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($sql);

echo "✅ Script SQL generado exitosamente: $outputFile\n";
echo "📊 Total de pacientes: " . ($pacienteId - 1) . "\n";
echo "\nPara importar ejecuta:\n";
echo "mysql -u root -p pacientesrhc < $outputFile\n";
