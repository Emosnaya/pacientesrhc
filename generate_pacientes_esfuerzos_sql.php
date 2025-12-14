<?php

$inputFile = 'pruebas_esfuerzo.csv';
$outputFile = 'import_pacientes_esfuerzos.sql';

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

function convertNumber($value) {
    if (empty($value)) {
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

// Crear array asociativo con headers
$data = [];
$registros = [];
while (($row = fgetcsv($file)) !== false) {
    if (count($row) >= $totalHeaders) {
        $rowData = array_combine($headers, array_slice($row, 0, $totalHeaders));
        $registro = trim($rowData['Registro'] ?? '');
        if (!empty($registro) && !isset($registros[$registro])) {
            $registros[$registro] = $rowData;
        }
    }
}
fclose($file);

echo "Total registros únicos: " . count($registros) . "\n";

// Crear archivo SQL
$sql = fopen($outputFile, 'w');
fwrite($sql, "-- Script de importación de pacientes del archivo esfuerzos\n");
fwrite($sql, "-- Generado: " . date('Y-m-d H:i:s') . "\n\n");
fwrite($sql, "SET @user_id = 1;\n");
fwrite($sql, "SET @clinica_id = 3;\n\n");

$insertados = 0;

foreach ($registros as $registro => $row) {
    // Separar nombre completo en partes
    $nombreCompleto = trim($row['Nombre'] ?? '');
    if (empty($nombreCompleto)) {
        continue; // Omitir si no hay nombre
    }
    
    $partes = explode(' ', $nombreCompleto);
    $apellidoPat = count($partes) > 0 ? $partes[0] : 'Sin';
    $apellidoMat = count($partes) > 1 ? $partes[1] : 'Apellido';
    $nombre = count($partes) > 2 ? implode(' ', array_slice($partes, 2)) : $nombreCompleto;
    
    // Convertir género
    $masculino = $row['Masculino'] ?? '0';
    $genero = ($masculino === '1' || $masculino === 1) ? 1 : 0;
    
    // Calcular IMC si tenemos peso y talla
    $peso = floatval($row['Peso'] ?? 0);
    $talla = floatval($row['Talla (m)'] ?? 0);
    $imc = ($peso > 0 && $talla > 0) ? round($peso / ($talla * $talla), 2) : 'NULL';
    
    fwrite($sql, "INSERT INTO pacientes (");
    fwrite($sql, "registro, nombre, apellidoPat, apellidoMat, fechaNacimiento, edad, genero, peso, talla, imc, ");
    fwrite($sql, "user_id, clinica_id, created_at, updated_at");
    fwrite($sql, ") VALUES (");
    
    fwrite($sql, escapeSql($registro) . ", ");
    fwrite($sql, escapeSql($nombre) . ", ");
    fwrite($sql, escapeSql($apellidoPat) . ", ");
    fwrite($sql, escapeSql($apellidoMat) . ", ");
    fwrite($sql, convertDate($row['Fecha de Nacimiento'] ?? '') . ", ");
    fwrite($sql, "'0', "); // edad como string
    fwrite($sql, $genero . ", ");
    fwrite($sql, convertNumber($row['Peso'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Talla (m)'] ?? '') . ", ");
    fwrite($sql, ($imc === 'NULL' ? 'NULL' : $imc) . ", ");
    fwrite($sql, "@user_id, @clinica_id, NOW(), NOW()");
    fwrite($sql, ");\n\n");
    
    $insertados++;
}

fclose($sql);

echo "✅ Script SQL generado exitosamente: $outputFile\n";
echo "📊 Pacientes a insertar: $insertados\n";
echo "\nPara importar ejecuta:\n";
echo "mysql -u root cercap < $outputFile\n";

?>
