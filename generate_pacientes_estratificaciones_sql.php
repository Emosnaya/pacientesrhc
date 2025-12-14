<?php

$inputFile = 'estratificaciones.csv';
$outputFile = 'import_pacientes_estratificaciones.sql';

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
$registros = [];
while (($row = fgetcsv($file)) !== false) {
    if (count($row) >= $totalHeaders) {
        $rowData = array_combine($headers, array_slice($row, 0, $totalHeaders));
        $registro = trim($rowData['Registro Exp'] ?? $rowData['Registro'] ?? '');
        if (!empty($registro) && !isset($registros[$registro])) {
            $registros[$registro] = $rowData;
        }
    }
}
fclose($file);

echo "Total registros únicos: " . count($registros) . "\n";

// Obtener registros existentes de la BD
$mysqli = new mysqli('localhost', 'root', '', 'cercap');
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$existentes = [];
$result = $mysqli->query("SELECT registro FROM pacientes WHERE user_id = 1 AND clinica_id = 3");
while ($row = $result->fetch_assoc()) {
    $existentes[] = $row['registro'];
}
$mysqli->close();

echo "Pacientes existentes en BD: " . count($existentes) . "\n";

// Crear archivo SQL
$sql = fopen($outputFile, 'w');
fwrite($sql, "-- Script de importación de pacientes faltantes del archivo estratificaciones\n");
fwrite($sql, "-- Generado: " . date('Y-m-d H:i:s') . "\n\n");
fwrite($sql, "SET @user_id = 1;\n");
fwrite($sql, "SET @clinica_id = 3;\n\n");

$insertados = 0;

foreach ($registros as $registro => $row) {
    if (in_array($registro, $existentes)) {
        continue;
    }
    
    // Este paciente no existe, crear
    $nombreCompleto = trim($row['Nombre'] ?? '');
    if (empty($nombreCompleto)) {
        continue;
    }
    
    $partes = explode(' ', $nombreCompleto);
    $apellidoPat = count($partes) > 0 ? $partes[0] : 'Sin';
    $apellidoMat = count($partes) > 1 ? $partes[1] : 'Apellido';
    $nombre = count($partes) > 2 ? implode(' ', array_slice($partes, 2)) : $nombreCompleto;
    
    // Convertir género M/F a 1/0
    $generoStr = $row['Género(M/F)'] ?? '';
    $genero = (strtoupper($generoStr) === 'M' || strtoupper($generoStr) === 'H') ? 1 : 0;
    
    // Calcular edad desde fecha de nacimiento si está disponible
    $fechaNac = convertDate($row['Fecha de Nacimiento'] ?? '');
    $edad = $row['Edad'] ?? '0';
    
    fwrite($sql, "INSERT INTO pacientes (");
    fwrite($sql, "registro, nombre, apellidoPat, apellidoMat, fechaNacimiento, edad, genero, peso, talla, cintura, imc, ");
    fwrite($sql, "user_id, clinica_id, created_at, updated_at");
    fwrite($sql, ") VALUES (");
    
    fwrite($sql, escapeSql($registro) . ", ");
    fwrite($sql, escapeSql($nombre) . ", ");
    fwrite($sql, escapeSql($apellidoPat) . ", ");
    fwrite($sql, escapeSql($apellidoMat) . ", ");
    fwrite($sql, $fechaNac . ", ");
    fwrite($sql, escapeSql($edad) . ", ");
    fwrite($sql, $genero . ", ");
    fwrite($sql, convertNumber($row['Peso(kg)'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Talla (m)'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['Cintura'] ?? '') . ", ");
    fwrite($sql, convertNumber($row['IMC'] ?? '') . ", ");
    fwrite($sql, "@user_id, @clinica_id, NOW(), NOW()");
    fwrite($sql, ");\n\n");
    
    $insertados++;
}

fclose($sql);

echo "✅ Script SQL generado exitosamente: $outputFile\n";
echo "📊 Pacientes faltantes a insertar: $insertados\n";
echo "\nPara importar ejecuta:\n";
echo "mysql -u root cercap < $outputFile\n";

?>
