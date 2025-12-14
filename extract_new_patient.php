<?php
// Extraer el paciente nuevo del clinicos2.csv

$csv1 = array_map(function($line) {
    return str_getcsv($line, ',', '"', '\\');
}, file('import_data.csv'));

$csv2 = array_map(function($line) {
    return str_getcsv($line, ',', '"', '\\');
}, file('clinicos2.csv'));

$headers = array_shift($csv1);
array_shift($csv2); // Remover headers

// Crear índice de nombres en csv1
$nombresExistentes = [];
foreach ($csv1 as $row) {
    if (count($row) > 7) {
        $nombresExistentes[] = trim($row[6]); // Columna 7 (índice 6)
    }
}

// Buscar registros en csv2 que no están en csv1
$nuevos = 0;
$output = fopen('import_data.csv', 'a');

foreach ($csv2 as $row) {
    if (count($row) > 7) {
        $nombre = trim($row[6]);
        
        // Saltar si ya existe (ignorar mayúsculas y acentos)
        $existe = false;
        foreach ($nombresExistentes as $existente) {
            if (strtolower($nombre) === strtolower($existente)) {
                $existe = true;
                break;
            }
        }
        
        if (!$existe) {
            echo "Nuevo paciente encontrado: $nombre\n";
            fputcsv($output, $row);
            $nuevos++;
        }
    }
}

fclose($output);

echo "\nTotal de pacientes nuevos agregados: $nuevos\n";
