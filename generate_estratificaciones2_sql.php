<?php

$inputFile = 'estratificaciones2.csv';
$outputFile = 'import_estratificaciones2.sql';

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
    if ($lower === 's' || $lower === 'si' || $lower === 'sí' || $lower === 'yes' || $lower === 'y' || $lower === '1' || $lower === 'x') {
        return 1;
    }
    if ($lower === 'n' || $lower === 'no' || $lower === '0') {
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

// Crear array asociativo con headers
$data = [];
while (($row = fgetcsv($file)) !== false) {
    if (count($row) === $totalHeaders) {
        $rowData = array_combine($headers, $row);
        $data[] = $rowData;
    }
}
fclose($file);

echo "Total filas: " . count($data) . "\n";

// Crear archivo SQL
$sql = fopen($outputFile, 'w');
fwrite($sql, "-- Script de importación de estratificaciones (archivo 2)\n");
fwrite($sql, "-- Generado: " . date('Y-m-d H:i:s') . "\n");
fwrite($sql, "-- Total de registros: " . count($data) . "\n\n");
fwrite($sql, "SET FOREIGN_KEY_CHECKS=0;\n");
fwrite($sql, "SET @user_id = 3;\n");
fwrite($sql, "SET @clinica_id = 1;\n");
fwrite($sql, "SET @tipo_exp = 2;\n\n");

$insertados = 0;

foreach ($data as $idx => $row) {
    $registro = trim($row['Registro'] ?? '');
    
    if (empty($registro)) {
        continue;
    }
    
    // Buscar paciente_id
    fwrite($sql, "SET @paciente_id = (SELECT id FROM pacientes WHERE registro = '$registro' AND user_id = @user_id AND clinica_id = @clinica_id LIMIT 1);\n");
    
    // Insertar o actualizar estratificacion
    fwrite($sql, "INSERT INTO estratificacions (");
    fwrite($sql, "primeravez_rhc, pe_fecha, estrati_fecha, ");
    fwrite($sql, "c_isquemia, im, ima, imas, imaa, imal, imae, iminf, impi, impi_vd, imlat, imsesst, imComplicado, ");
    fwrite($sql, "valvular, otro, mcd, icc, reanimacion_cardio, falla_entrenar, ");
    fwrite($sql, "tabaquismo, dislipidemia, dm, has, obesidad, estres, sedentarismo, riesgo_otro, ");
    fwrite($sql, "depresion, ansiedad, sintomatologia, ");
    fwrite($sql, "puntuacion_atp2000, heart_score, col_total, ldl, hdl, tg, fevi, pcr, ");
    fwrite($sql, "enf_coronaria, isquemia, holter, ");
    fwrite($sql, "pe_capacidad, fc_basal, fc_maxima, fc_borg_12, dp_borg_12, mets_borg_12, ");
    fwrite($sql, "carga_max_bnda, tolerancia_max_esfuerzo, respuesta_presora, indice_ta_esf, ");
    fwrite($sql, "porc_fc_pre_alcanzado, r_cronotr, porder_cardiaco, recuperacion_tas, recuperacion_fc, ");
    fwrite($sql, "duke, veteranos, ectopia_ventricular, umbral_isquemico, supranivel_st, ");
    fwrite($sql, "infra_st_mayor2_135, infra_st_mayor2_5mets, riesgo_global, ");
    fwrite($sql, "grupo, semanas, borg, fc_diana_str, ");
    fwrite($sql, "karvonen, blackburn, narita, fc_diana, dp_diana, carga_inicial, comentarios, ");
    fwrite($sql, "user_id, paciente_id, tipo_exp, clinica_id, created_at, updated_at");
    fwrite($sql, ") VALUES (");
    
    // Fechas
    fwrite($sql, convertDate($row['1a vez RHC (fecha)'] ?? '') . ", ");
    fwrite($sql, convertDate($row['PE (fecha)'] ?? '') . ", ");
    fwrite($sql, convertDate($row['Estratificación (Fecha)'] ?? '') . ", ");
    
    // C. Isquémica y tipos de IM
    fwrite($sql, escapeSql($row['C. Isquémica'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IM'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMA'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMAS'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMAA'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMAL'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMAE'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMInf'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMPI'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMPI+VD'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMLat'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMSESST'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['IMComplicado (s/n)'] ?? '') . ", ");
    
    // Valvular y otras condiciones
    fwrite($sql, escapeSql($row['Valvular'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['Otro'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['MCD'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['ICC (s/n)'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['ReanimaciónCP (s/n)'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['Falla para entrenar (s/n)'] ?? '') . ", ");
    
    // Factores de riesgo
    fwrite($sql, convertBoolean($row['Tabaquismo'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['Dislipidemia'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['DM'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['HAS'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['Obesidad'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['Estrés'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['Sedentarismo'] ?? '') . ", ");
    
    // Usar la columna "Otro" de F. riesgo (índice 50)
    $otroRiesgo = isset($row['Otro']) && isset($headers[50]) && $headers[50] === 'Otro' ? $row[$headers[50]] : '';
    fwrite($sql, escapeSql($otroRiesgo) . ", ");
    
    // Depresión, ansiedad
    fwrite($sql, convertBoolean($row['Depresión (s/n)'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['Ansiedad'] ?? '') . ", ");
    fwrite($sql, escapeSql($row['Síntomatología'] ?? '') . ", ");
    
    // Puntuaciones y valores de laboratorio
    fwrite($sql, convertNumber($row['Puntuación ATP2000'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Heart-Score'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Col. Total'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['LDL'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['HDL'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Tg'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['FEVI%'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['PCR'] ?? '') . ", ");
    
    // Enfermedad coronaria, isquemia, holter
    fwrite($sql, escapeSql($row['Enf Coronaria'] ?? '') . ", ");
    fwrite($sql, escapeSql($row['Isquemia MN'] ?? '') . ", ");
    fwrite($sql, escapeSql($row['Holter'] ?? '') . ", ");
    
    // PE - Prueba de esfuerzo
    fwrite($sql, convertBoolean($row['Capacidad para Realizar PE (s/n)'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['FC Basal'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['FC Máxima'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['FC (borg12)'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['DP (Borg12)'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['METs Borg 12'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Carga máxima (medida por banda)'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Tolerancia Máxima al esfuerzo (METs)'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Respuesta presora'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Índice TA esf.'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['% de la FC predicha alcanzado'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['R.Cronotr'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Poder cardiaco en esfuerzo'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Recuperación TAS'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Recuperación FC'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Duke'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Veteranos'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['Ectopia ventricular frecuente (s/n)'] ?? '') . ", ");
    fwrite($sql, escapeSql($row['Umbral isquémico (METs/no/recup)'] ?? '') . ", ");
    fwrite($sql, convertBoolean($row['Supradesnivel del ST (Sin onda Q) (s/n)'] ?? '') . ", ");
    fwrite($sql, escapeSql($row['InfraST ≥ 2mm (FC/no/recup)'] ?? '') . ", ");
    fwrite($sql, escapeSql($row['InfraST ≥ 2mm (<5 METs)'] ?? '') . ", ");
    fwrite($sql, escapeSql($row['Riesgo global'] ?? '') . ", ");
    
    // Grupo - header consolidado en posición 58
    fwrite($sql, escapeSql($row['Grupo(a,b,c,d)'] ?? '') . ", ");
    
    // Semanas - header consolidado (verificar nombre exacto)
    $semanasHeader = '';
    foreach ($headers as $h) {
        if (strpos($h, 'Semanas') !== false) {
            $semanasHeader = $h;
            break;
        }
    }
    fwrite($sql, convertNumber($row[$semanasHeader] ?? '') . ", ");
    
    // Borg - header consolidado en posición 60
    fwrite($sql, convertNumber($row['Borg (8,10,12,14)'] ?? '') . ", ");
    
    // FC diana string - header consolidado en posición 61
    fwrite($sql, escapeSql($row['FC diana (Bo,K,Bl,N)'] ?? '') . ", ");
    
    // Nuevas columnas numéricas
    fwrite($sql, convertNumber($row['Karvonen'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Blackburn'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Narita'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['FC Diana'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Dp Diana'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Carga Inicial (Watts)'] ?? '') . ", ");
    fwrite($sql, escapeSql($row['Comentario 1'] ?? '') . ", ");
    
    // Metadatos
    fwrite($sql, "@user_id, @paciente_id, @tipo_exp, @clinica_id, NOW(), NOW()");
    fwrite($sql, ") ON DUPLICATE KEY UPDATE ");
    fwrite($sql, "primeravez_rhc = VALUES(primeravez_rhc), ");
    fwrite($sql, "pe_fecha = VALUES(pe_fecha), ");
    fwrite($sql, "estrati_fecha = VALUES(estrati_fecha), ");
    fwrite($sql, "c_isquemia = VALUES(c_isquemia), ");
    fwrite($sql, "im = VALUES(im), ima = VALUES(ima), imas = VALUES(imas), ");
    fwrite($sql, "imaa = VALUES(imaa), imal = VALUES(imal), imae = VALUES(imae), ");
    fwrite($sql, "iminf = VALUES(iminf), impi = VALUES(impi), impi_vd = VALUES(impi_vd), ");
    fwrite($sql, "imlat = VALUES(imlat), imsesst = VALUES(imsesst), imComplicado = VALUES(imComplicado), ");
    fwrite($sql, "valvular = VALUES(valvular), otro = VALUES(otro), mcd = VALUES(mcd), ");
    fwrite($sql, "icc = VALUES(icc), reanimacion_cardio = VALUES(reanimacion_cardio), ");
    fwrite($sql, "falla_entrenar = VALUES(falla_entrenar), ");
    fwrite($sql, "tabaquismo = VALUES(tabaquismo), dislipidemia = VALUES(dislipidemia), ");
    fwrite($sql, "dm = VALUES(dm), has = VALUES(has), obesidad = VALUES(obesidad), ");
    fwrite($sql, "estres = VALUES(estres), sedentarismo = VALUES(sedentarismo), ");
    fwrite($sql, "riesgo_otro = VALUES(riesgo_otro), ");
    fwrite($sql, "depresion = VALUES(depresion), ansiedad = VALUES(ansiedad), ");
    fwrite($sql, "sintomatologia = VALUES(sintomatologia), ");
    fwrite($sql, "puntuacion_atp2000 = VALUES(puntuacion_atp2000), ");
    fwrite($sql, "heart_score = VALUES(heart_score), ");
    fwrite($sql, "col_total = VALUES(col_total), ldl = VALUES(ldl), ");
    fwrite($sql, "hdl = VALUES(hdl), tg = VALUES(tg), fevi = VALUES(fevi), pcr = VALUES(pcr), ");
    fwrite($sql, "enf_coronaria = VALUES(enf_coronaria), isquemia = VALUES(isquemia), ");
    fwrite($sql, "holter = VALUES(holter), ");
    fwrite($sql, "pe_capacidad = VALUES(pe_capacidad), fc_basal = VALUES(fc_basal), ");
    fwrite($sql, "fc_maxima = VALUES(fc_maxima), fc_borg_12 = VALUES(fc_borg_12), ");
    fwrite($sql, "dp_borg_12 = VALUES(dp_borg_12), mets_borg_12 = VALUES(mets_borg_12), ");
    fwrite($sql, "carga_max_bnda = VALUES(carga_max_bnda), ");
    fwrite($sql, "tolerancia_max_esfuerzo = VALUES(tolerancia_max_esfuerzo), ");
    fwrite($sql, "respuesta_presora = VALUES(respuesta_presora), ");
    fwrite($sql, "indice_ta_esf = VALUES(indice_ta_esf), ");
    fwrite($sql, "porc_fc_pre_alcanzado = VALUES(porc_fc_pre_alcanzado), ");
    fwrite($sql, "r_cronotr = VALUES(r_cronotr), porder_cardiaco = VALUES(porder_cardiaco), ");
    fwrite($sql, "recuperacion_tas = VALUES(recuperacion_tas), ");
    fwrite($sql, "recuperacion_fc = VALUES(recuperacion_fc), ");
    fwrite($sql, "duke = VALUES(duke), veteranos = VALUES(veteranos), ");
    fwrite($sql, "ectopia_ventricular = VALUES(ectopia_ventricular), ");
    fwrite($sql, "umbral_isquemico = VALUES(umbral_isquemico), ");
    fwrite($sql, "supranivel_st = VALUES(supranivel_st), ");
    fwrite($sql, "infra_st_mayor2_135 = VALUES(infra_st_mayor2_135), ");
    fwrite($sql, "infra_st_mayor2_5mets = VALUES(infra_st_mayor2_5mets), ");
    fwrite($sql, "riesgo_global = VALUES(riesgo_global), ");
    fwrite($sql, "grupo = VALUES(grupo), semanas = VALUES(semanas), ");
    fwrite($sql, "borg = VALUES(borg), fc_diana_str = VALUES(fc_diana_str), ");
    fwrite($sql, "karvonen = VALUES(karvonen), blackburn = VALUES(blackburn), ");
    fwrite($sql, "narita = VALUES(narita), fc_diana = VALUES(fc_diana), ");
    fwrite($sql, "dp_diana = VALUES(dp_diana), carga_inicial = VALUES(carga_inicial), ");
    fwrite($sql, "comentarios = VALUES(comentarios), ");
    fwrite($sql, "updated_at = NOW()");
    fwrite($sql, ";\n\n");
    
    $insertados++;
}

fwrite($sql, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($sql);

echo "✅ Script SQL generado exitosamente: $outputFile\n";
echo "📊 Estratificaciones insertadas: $insertados\n";
echo "\nPara importar ejecuta:\n";
echo "mysql -u root -p cercap < $outputFile\n";

?>
