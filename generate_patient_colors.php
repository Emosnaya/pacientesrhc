<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Paciente;

// Colores atractivos y distinguibles
$colores = [
    '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8',
    '#F7DC6F', '#BB8FCE', '#85C1E2', '#F8B195', '#F67280',
    '#C06C84', '#6C5B7B', '#355C7D', '#99B898', '#FECEAB',
    '#FF847C', '#E84A5F', '#2A363B', '#A8E6CF', '#FFD3B6',
    '#FFAAA5', '#FF8B94', '#A8DADC', '#457B9D', '#1D3557',
    '#E63946', '#F1FAEE', '#A8DADC', '#F77F00', '#D62828',
    '#264653', '#2A9D8F', '#E9C46A', '#F4A261', '#E76F51',
    '#8338EC', '#3A86FF', '#FB5607', '#FFBE0B', '#8AC926'
];

echo "Generando colores únicos para pacientes...\n\n";

$pacientes = Paciente::whereNull('color')->get();

echo "Pacientes sin color: " . $pacientes->count() . "\n\n";

$index = 0;
foreach ($pacientes as $paciente) {
    $color = $colores[$index % count($colores)];
    $paciente->color = $color;
    $paciente->save();
    
    echo "✓ {$paciente->nombre} {$paciente->apellidoPat}: {$color}\n";
    
    $index++;
}

echo "\n✅ Colores asignados exitosamente!\n";
