<?php

$inputFile = 'estratificaciones.csv';
$outputFile = 'import_estratificaciones.sql';

function convertDate($dateStr) {
    if (empty($dateStr) || strtolower($dateStr) === 'nv') {
        return 'NULL';
    }
    
    $meses = [
        'ene' => '01', 'feb' => '02', 'mar' => '03', 'abr' => '04',
        'may' => '05', 'jun' => '06', 'jul' => '07', 'ago' => '08',
        'sep' => '09', 'oct' => '10', 'nov' => '11', 'dic' => '12'
    ];
    
    if (preg_match('/(\d{1,2})-([a-z]{3})-(\d{2})/', strtolower($dateStr), $matches)) {
        $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $mes = $meses[$matches[2]] ?? '01';
        $anio = '20' . $matches[3];
        
        if (checkdate($mes, $dia, $anio)) {
            return "'" . $anio . '-' . $mes . '-' . $dia . "'";
        }
    }
    
    return 'NULL';
}

function convertBoolean($value) {
    if (empty($value)) {
        return 'NULL';
    }
    $lower = strtolower(trim($value));
    if ($lower === 's' || $lower === 'si' || $lower === 'sí' || $lower === 'yes' || $lower === 'y' || $lower === '1') {
        return 1;
    }
    if ($lower === 'n' || $lower === 'no' || $lower === 'x' || $lower === '0') {
        return 0;
    }
    if ($lower === 'nv') {
        return 'NULL';
    }
    return 'NULL';
}

function convertNumber($value) {
    if (empty($value)) {
        return 'NULL';
    }
    $lower = strtolower(trim($value));
    if ($lower === 'nv' || $lower === 'no tiene' || $lower === 'notiene' || $lower === 'n' || $lower === 'no' || $lower === 'x') {
        return 'NULL';
    }
    $cleaned = preg_replace('/[^0-9.-]/', '', $value);
    if (is_numeric($cleaned) && $cleaned !== '') {
        return $cleaned;
    }
    return 'NULL';
}

function escapeSql($value) {
    if (empty($value)) {
        return 'NULL';
    }
    $lower = strtolower(trim($value));
    if ($lower === 'nv' || $lower === 'no tiene' || $lower === 'notiene') {
        return 'NULL';
    }
    return "'" . addslashes(trim($value)) . "'";
}

// Leer CSV
$file = fopen($inputFile, 'r');
if (!$file) {
    die("No se pudo abrir el archivo $inputFile\n");
}

// Leer encabezados
$headers = fgetcsv($file);
$totalHeaders = count($headers);
echo "Total headers: $totalHeaders\n";

$allData = [];
while (($row = fgetcsv($file)) !== false) {
    $allData[] = $row;
}
fclose($file);

$totalFilas = count($allData) + 1;
echo "Total filas: " . count($allData) . "\n";

// Crear archivo SQL
$sql = fopen($outputFile, 'w');
fwrite($sql, "-- Script de importación de estratificaciones\n");
fwrite($sql, "-- Generado: " . date('Y-m-d H:i:s') . "\n");
fwrite($sql, "-- Total de registros: " . count($allData) . "\n\n");
fwrite($sql, "SET FOREIGN_KEY_CHECKS=0;\n");
fwrite($sql, "SET @user_id = 1;\n");
fwrite($sql, "SET @clinica_id = 3;\n");
fwrite($sql, "SET @tipo_exp = 2;\n\n");

$estratificacionesInsertadas = 0;

foreach ($allData as $row) {
    $data = array_combine($headers, $row);
    
    $registro = $data['Registro Exp'] ?? '';
    $nombreCompleto = $data['Nombre'] ?? '';
    
    if (empty($registro)) {
        continue;
    }
    
    fwrite($sql, "-- Estratificación para: $nombreCompleto (Registro: $registro)\n");
    
    // Construir INSERT
    fwrite($sql, "INSERT INTO estratificacions (");
    fwrite($sql, "primeravez_rhc, pe_fecha, estrati_fecha, ");
    fwrite($sql, "imComplicado, icc, reanimacion_cardio, falla_entrenar, depresion, ");
    fwrite($sql, "puntuacion_atp2000, heart_score, fevi, pcr, enf_coronaria, isquemia_irm, holter, ");
    fwrite($sql, "fc_basal, fc_borg_12, fc_maxima, dp_borg_12, mets_borg_12, ");
    fwrite($sql, "carga_max_bnda, tolerancia_max_esfuerzo, respuesta_presora, indice_ta_esf, ");
    fwrite($sql, "recuperacion_tas, porc_fc_pre_alcanzado, r_cronotr, recuperacion_fc, ");
    fwrite($sql, "porder_cardiaco, duke, veteranos, ectopia_ventricular, umbral_isquemico, ");
    fwrite($sql, "infra_st_mayor2_135, infra_st_mayor2_5mets, riesgo_global, grupo, ");
    fwrite($sql, "sesiones, borg, fc_diana_str, karvonen, blackburn, narita, ");
    fwrite($sql, "fc_diana, dp_diana, carga_inicial, ");
    fwrite($sql, "user_id, paciente_id, clinica_id, tipo_exp, created_at, updated_at");
    fwrite($sql, ") VALUES (\n");
    
    // Valores
    fwrite($sql, convertDate($data['1a vez RHC (fecha)'] ?? '') . ", ");
    fwrite($sql, convertDate($data['PE (fecha)'] ?? '') . ", ");
    fwrite($sql, convertDate($data['Estratificación (Fecha)'] ?? '') . ", ");
    
    // Booleans
    fwrite($sql, convertBoolean($data['IMComplicado (s/n)'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['ICC (s/n)'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['ReanimaciónCP (s/n)'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Falla para entrenar (s/n)'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Depresión (s/n)'] ?? '') . ", ");
    
    // Números
    fwrite($sql, convertNumber($data['Puntuación ATP2000'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Heart-Score'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['FEVI%'] ?? '') . ", ");
    fwrite($sql, escapeSql($data['PCR'] ?? '') . ", ");
    fwrite($sql, escapeSql($data['Enf Coronaria'] ?? '') . ", ");
    fwrite($sql, escapeSql($data['MN'] ?? '') . ", ");
    fwrite($sql, escapeSql($data['Holter'] ?? '') . ", ");
    
    // FCs y datos de esfuerzo
    fwrite($sql, convertNumber($data['FC Basal'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['FC (borg12)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['FC Máxima'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['DP (Borg12)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METs Borg 12'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Carga máxima (medida por banda)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Tolerancia Máxima al esfuerzo (METs)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Respuesta presora'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Índice TA esf.'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Recuperación TAS'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['% de la FC predicha alcanzado'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['R.Cronotr'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Recuperación FC'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Poder cardiaco en esfuerzo'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Duke'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Veteranos'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Ectopia ventricular frecuente (s/n)'] ?? '') . ", ");
    fwrite($sql, escapeSql($data['Umbral isquémico (METs/no/recup)'] ?? '') . ", ");
    
    // Infra ST - estas columnas están fragmentadas
    $infraSt135 = $data['No'] ?? '';
    if (!empty($infraSt135) && strtolower($infraSt135) !== 'n') {
        fwrite($sql, escapeSql($infraSt135) . ", ");
    } else {
        fwrite($sql, "NULL, ");
    }
    
    $infraSt5mets = $data['No'] ?? ''; // Columna 55
    if (!empty($infraSt5mets) && strtolower($infraSt5mets) !== 'n') {
        fwrite($sql, escapeSql($infraSt5mets) . ", ");
    } else {
        fwrite($sql, "NULL, ");
    }
    
    fwrite($sql, escapeSql($data['Riesgo global'] ?? '') . ", ");
    
    // Grupo - header consolidado en posición 58
    fwrite($sql, escapeSql($data['Grupo(a,b,c,d)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Sesiones (n)'] ?? '') . ", ");
    
    // Borg - header consolidado en posición 60
    fwrite($sql, convertNumber($data['Borg (8,10,12,14)'] ?? '') . ", ");
    
    // FC diana string - header consolidado en posición 61
    fwrite($sql, escapeSql($data['FC diana (Bo,K,Bl,N)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Karvonen'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Blackburn'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Narita'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['FC Diana'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Dp Diana'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Carga Inicial (Watts)'] ?? '') . ", ");
    
    // IDs y timestamps
    fwrite($sql, "@user_id, ");
    fwrite($sql, "(SELECT id FROM pacientes WHERE registro = " . escapeSql($registro) . " ");
    fwrite($sql, "AND clinica_id = @clinica_id AND user_id = @user_id LIMIT 1), ");
    fwrite($sql, "@clinica_id, @tipo_exp, NOW(), NOW());\n\n");
    
    $estratificacionesInsertadas++;
}

fwrite($sql, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($sql);

echo "✅ Script SQL generado exitosamente: $outputFile\n";
echo "📊 Estratificaciones insertadas: $estratificacionesInsertadas\n";
echo "\nPara importar ejecuta:\n";
echo "mysql -u root -p cercap < $outputFile\n";
