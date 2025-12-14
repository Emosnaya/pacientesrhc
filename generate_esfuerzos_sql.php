<?php
// Script para generar SQL de importación de pruebas de esfuerzo

$csvFile = 'pruebas_esfuerzo.csv';
$outputFile = 'import_esfuerzos.sql';

// Funciones helper (copiad del script principal)
function convertDate($dateStr) {
    if (empty($dateStr) || $dateStr === 'n' || $dateStr === 's') {
        return 'NULL';
    }
    
    $meses = [
        'ene' => '01', 'feb' => '02', 'mar' => '03', 'abr' => '04',
        'may' => '05', 'jun' => '06', 'jul' => '07', 'ago' => '08',
        'sep' => '09', 'oct' => '10', 'nov' => '11', 'dic' => '12'
    ];
    
    $parts = explode('-', strtolower($dateStr));
    if (count($parts) === 3) {
        $day = (int)$parts[0];
        $monthStr = $parts[1];
        $year = $parts[2];
        
        if ($day < 1 || $day > 31) return 'NULL';
        
        $day = str_pad($day, 2, '0', STR_PAD_LEFT);
        $month = $meses[$monthStr] ?? '01';
        
        if (strlen($year) === 2) {
            $year = (int)$year <= 25 ? '20' . $year : '19' . $year;
        }
        
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            return 'NULL';
        }
        
        return "'$year-$month-$day'";
    }
    
    return 'NULL';
}

function convertBoolean($value) {
    $val = strtolower(trim($value));
    if ($val === 's' || $val === 'si' || $val === 'sí' || $val === '1' || $val === 'true') {
        return 1;
    }
    return 0;
}

function convertNumber($value) {
    if (empty($value) || !is_numeric($value)) return 'NULL';
    return $value;
}

function extractNumber($value) {
    if (empty($value)) return 'NULL';
    
    $val = trim($value);
    
    // Manejar casos especiales de texto
    $valLower = strtolower($val);
    if ($valLower === 'no tiene' || $valLower === 'n' || $valLower === 'no' || $valLower === 'x') {
        return 'NULL';
    }
    
    // Quitar %, comillas, espacios y otros caracteres no numéricos
    $cleaned = str_replace(['%', '"', "'", ' ', ','], '', $val);
    // Quitar todo excepto números, punto decimal y signo negativo
    $cleaned = preg_replace('/[^0-9.-]/', '', $cleaned);
    
    if (is_numeric($cleaned) && $cleaned !== '' && $cleaned !== '-') {
        return $cleaned;
    }
    return 'NULL';
}

function escapeSql($value) {
    if ($value === null || $value === '' || strtolower($value) === 'no tiene') {
        return 'NULL';
    }
    $value = str_replace("'", "''", $value);
    return "'" . $value . "'";
}

// Leer CSV
if (!file_exists($csvFile)) {
    die("Error: No se encontró el archivo CSV\n");
}

$csv = array_map(function($line) {
    return str_getcsv($line, ',', '"', '\\');
}, file($csvFile));

$headers = array_shift($csv);

echo "Total headers: " . count($headers) . "\n";
echo "Total filas: " . count($csv) . "\n";

// Crear archivo SQL
$sql = fopen($outputFile, 'w');
fwrite($sql, "-- Script de importación de pruebas de esfuerzo\n");
fwrite($sql, "-- Generado: " . date('Y-m-d H:i:s') . "\n");
fwrite($sql, "-- Total de registros: " . count($csv) . "\n\n");
fwrite($sql, "SET FOREIGN_KEY_CHECKS=0;\n");
fwrite($sql, "SET @user_id = 3;\n");
fwrite($sql, "SET @clinica_id = 1;\n");
fwrite($sql, "SET @tipo_exp = 1;\n\n");

$pruebasInsertadas = 0;
$pruebasOmitidas = 0;

foreach ($csv as $rowIndex => $row) {
    if (count($row) < 10) continue;
    
    // Ajustar si headers y row no coinciden
    if (count($headers) !== count($row)) {
        if (count($row) < count($headers)) {
            $row = array_pad($row, count($headers), '');
        } else {
            $row = array_slice($row, 0, count($headers));
        }
    }
    
    $data = array_combine($headers, $row);
    
    // Extraer datos básicos
    $fecha = convertDate($data['Fecha'] ?? '');
    $numPrueba = escapeSql($data['Prueba No.'] ?? '');
    $nombreCompleto = trim($data['Nombre'] ?? '');
    
    if (empty($nombreCompleto)) {
        fwrite($sql, "-- Línea " . ($rowIndex + 2) . ": Omitido (sin nombre)\n");
        $pruebasOmitidas++;
        continue;
    }
    
    // Buscar paciente por número de registro (más confiable que el nombre)
    $registro = $data['Registro'] ?? '';
    
    fwrite($sql, "-- Prueba para: $nombreCompleto (Registro: $registro)\n");
    fwrite($sql, "INSERT INTO esfuerzos (fecha, numPrueba, icc, FEVI, metodo, nyha, ccs, ");
    fwrite($sql, "betabloqueador, iecas, nitratos, digoxina, calcioAntag, antirritmicos, hipolipemiantes, diureticos, aldactone, antiagregante, otros, ");
    fwrite($sql, "prevalencia, confusor, sensibilidad, especificidad, vpp, vpn, ");
    fwrite($sql, "pruebaIngreso, pruebaFinFase2, pruebaFinFase3, fechaDeInicio, ");
    fwrite($sql, "balke, bruce, ciclo, banda, medicionGases, ");
    fwrite($sql, "fcBasal, tasBasal, tadBasal, dapBasal, ");
    fwrite($sql, "fcBorg12, tasBorg12, tadBorg12, dpBorg12, ");
    fwrite($sql, "w_50, fc_w_50, tas_w_50, tad_w_50, borg_w_50, dp_w_50, ");
    fwrite($sql, "fcMax, tasMax, tadMax, borgMax, tAMax_tAbasal, tAMax_tAbasal_val, tiempoEsfuerzo, dpMax, motivoSuspension, ");
    fwrite($sql, "fc_1er_min, tas_1er_min, tad_1er_min, borg_1er_min, dp_1er_min, ");
    fwrite($sql, "fc_3er_min, tas_3er_min, tad_3er_min, borg_3er_min, dp_3er_min, ");
    fwrite($sql, "fc_5to_min, tas_5to_min, tad_5to_min, ");
    fwrite($sql, "fc_8vo_min, tas_8vo_min, tad_8vo_min, ");
    fwrite($sql, "fc_U_isq, tas_U_isq, tad_U_isq, borg_U_isq, scoreAngina, ");
    fwrite($sql, "arritmias, tipoArritmias, positiva, tipoCambioElectrico, ectopia_ventricular, ");
    fwrite($sql, "veteranos, duke, riesgo, u_isq_borg, u_isq_dp, ");
    fwrite($sql, "fc_max_calc, fc_85, fc_max_alcanzado, ");
    fwrite($sql, "vel_borg_12, inclin_borg_12, watts_ciclo_b_12, ");
    fwrite($sql, "vel_max, incl_max, watts_ciclo_max, ");
    fwrite($sql, "vel_um_isq, incl_um_isq, watts_ciclo_u_isq, ");
    fwrite($sql, "vo2t_mujer, mets_teorico_mujer, vo2t_varon, mets_teorico_varon, mets_teorico_general, ");
    fwrite($sql, "vo2r_borg_12, mets_banda_borg_12, mets_ciclo_b12, mets_borg_12, ");
    fwrite($sql, "vo2r_max, mets_banda_max, mets_ciclo_max, mets_max, ");
    fwrite($sql, "vo2_alcanzado, vo2r_U_isq, mets_banda_U_isq, mets_ciclo_U_isq, mets_U_isq, ");
    fwrite($sql, "mvo2, mvo2_mets, iem, ");
    fwrite($sql, "po2t_mujer, po2t_varon, po2tr, rfa_mujer, rfa_varon, ");
    fwrite($sql, "resp_presora, indice_tas, resp_crono, ");
    fwrite($sql, "fcmax_fc1er, fcmax_fc3er, fc_rec_1_por, fc_rec_3_por, ");
    fwrite($sql, "recup_tas, pbp3, pce, tce, ");
    fwrite($sql, "vel_borg_12_mh, vel_max_mh, vel_u_isq_mh, ");
    fwrite($sql, "ch_borg_12, ch_max, ch_u_isq, ");
    fwrite($sql, "cv_borg_12, cv_max, cv_u_isq, ");
    fwrite($sql, "conclusiones, user_id, paciente_id, clinica_id, tipo_exp, created_at, updated_at) VALUES\n");
    
    // Valores
    fwrite($sql, "($fecha, $numPrueba, ");
    fwrite($sql, convertBoolean($data['ICC ó digoxina (0/1)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['FEVI'] ?? '') . ", ");
    fwrite($sql, escapeSql($data['Diagnóstico'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['NYHA'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['CCS'] ?? '') . ", ");
    
    // Medicamentos (todos booleanos)
    fwrite($sql, convertBoolean($data['Betabloqueador'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['IECAs'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Nitratos'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Digoxina'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Calcioantagonistas'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Antiarrítmicos'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Hipolipem'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Diuréticos'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Aldactone'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Antiagregante'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Otros'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['prevalencia'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['confusor'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['sensibilidad'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['especificidad'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['VPP'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['VPN'] ?? '') . ", ");
    
    fwrite($sql, convertBoolean($data['Pba. Ingreso'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Pba. Fin Fase II'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Pba. Fin Fase III'] ?? '') . ", ");
    fwrite($sql, convertDate($data['Fecha de Inicio'] ?? '') . ", ");
    
    // Determinar balke o bruce según Protocolo
    $protocolo = strtolower(trim($data['Protocolo'] ?? ''));
    $isBalke = (strpos($protocolo, 'balke') !== false) ? 1 : 0;
    $isBruce = (strpos($protocolo, 'bruce') !== false) ? 1 : 0;
    fwrite($sql, "$isBalke, ");
    fwrite($sql, "$isBruce, ");
    fwrite($sql, convertBoolean($data['Ciclo'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Banda'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Medición de gases'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['FC Basal'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAS Basal (brazo)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAD Basal'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['DAP Basal'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['FC Borg 12'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAS Borg 12'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAD Borg 12'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['DP B 12'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['50 w'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['FC 50 w'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAS 50 w'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAD 50 w'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Borg 50 w'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['DP 50 w'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['FC Max'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAS Max'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAD max'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Borg Max'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAmax-TAbasal'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Score de TA'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Tiempo de Esfuerzo'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['DP Max'] ?? '') . ", ");
    fwrite($sql, escapeSql($data['Motivo de Susp.'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['FC 1er min Rec'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAS 1er min rec'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAD 1er min Rec'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Borg 1er min rec.'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['DP (1er min R)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['FC 3er min Rec'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAS 3er min rec'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAD 3er min Rec'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Borg 3er min Rec'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['DP (3er min R)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data["FC 5to '"] ?? '') . ", ");
    fwrite($sql, convertNumber($data["TAS 5to'"] ?? '') . ", ");
    fwrite($sql, convertNumber($data["TAD 5to'"] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data["FC 8vo '"] ?? '') . ", ");
    fwrite($sql, convertNumber($data["TAS 8vo'"] ?? '') . ", ");
    fwrite($sql, convertNumber($data["TAD 8vo'"] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['FC (U. Isq)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAS (U. Isq)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TAD (U. isq)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Borg (U.isq)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Score de Angina'] ?? '') . ", ");
    
    fwrite($sql, convertBoolean($data['Arritmias'] ?? '') . ", ");
    fwrite($sql, escapeSql($data['Tipo de arritmias'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Ectopia ventricular Frecuente (si/no)'] ?? '') . ", ");
    fwrite($sql, escapeSql($data['Tipo de cambio eléctrico'] ?? '') . ", ");
    fwrite($sql, convertBoolean($data['Ectopia ventricular Frecuente (si/no)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Veteranos'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Duke'] ?? '') . ", ");
    fwrite($sql, escapeSql($data['Respuesta al ejercicio'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['U. Isq. Borg'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['U. Isq (DP)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['FC Máxima Calc.'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['FC 85%'] ?? '') . ", ");
    fwrite($sql, extractNumber($data['% FC max Alcanzado'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Vel Borg 12 (MPH)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Inclin Borg 12'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Watts Ciclo B 12'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Vel max (MPH)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Incl Max'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Watts ciclo max'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Vel Um Isq (MPH)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Incl Um Isq.'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Watts Ciclo U. Isq'] ?? '') . ", ");
    
    // VO2 y METS
    fwrite($sql, convertNumber($data['VO2T (Mujer)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METS Teór. (Mujer)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['VO2T(Varón)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METS Teór. (Varón)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METS Teór. (General)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['VO2r(Borg 12)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METS-banda (Borg 12)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METs-ciclo (B 12)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METS (Borg 12)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['VO2r (max)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METS (Max)-Banda'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METs (Max) ciclo'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METs (Max)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['% VO2 Alcanzado'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['VO2r(U.Isq)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METS (U. Isq)-banda'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METs (U.Isq) ciclo'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['METs (U. Isq)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['MVO2'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['MVO2 (METS)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['IEM'] ?? '') . ", ");
    
    // PO2 y RFA
    fwrite($sql, convertNumber($data['PO2T (Mujer)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['PO2T (Varón)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['PO2r'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['RFA (mujer)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['RFA (varón)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Resp. Presora'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Índice de TAS en esfuerzo'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Resp Cronotrópica'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['FC max – FC 1\''] ?? '') . ", ");
    fwrite($sql, convertNumber($data['FC max – FC 3\''] ?? '') . ", ");
    fwrite($sql, convertNumber($data['% FC rec 1\''] ?? '') . ", ");
    fwrite($sql, convertNumber($data['% FC rec 3\''] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Recuperación TA'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['PBP3'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['PCE'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['TCE'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['Vel Borg 12 (m/h)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Vel max (m/h)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['Vel U. Isq (m/h)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['CH (Borg 12)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['CH (Max)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['CH (U. Isq)'] ?? '') . ", ");
    
    fwrite($sql, convertNumber($data['CV (Borg 12)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['CV (max)'] ?? '') . ", ");
    fwrite($sql, convertNumber($data['CV (U. isq)'] ?? '') . ", ");
    
    // Concatenar las columnas de tipo de prueba para conclusiones
    // PHP índices: 72 = "Tipo de Prueba", 73 = columna vacía con continuación del texto
    $conclusiones = trim(
        ($data['Tipo de Prueba'] ?? '') . ' ' .
        ($data[''] ?? '')  // Columna 73 tiene header vacío
    );
    fwrite($sql, escapeSql($conclusiones) . ", ");
    fwrite($sql, "@user_id, ");
    fwrite($sql, "(SELECT id FROM pacientes WHERE registro = " . escapeSql($registro) . " ");
    fwrite($sql, "AND clinica_id = @clinica_id AND user_id = @user_id LIMIT 1), ");
    fwrite($sql, "@clinica_id, @tipo_exp, NOW(), NOW());\n\n");
    
    $pruebasInsertadas++;
}

fwrite($sql, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($sql);

echo "✅ Script SQL generado exitosamente: $outputFile\n";
echo "📊 Pruebas insertadas: $pruebasInsertadas\n";
echo "⚠️  Pruebas omitidas: $pruebasOmitidas\n";
echo "\nPara importar ejecuta:\n";
echo "mysql -u root -p cercap < $outputFile\n";
