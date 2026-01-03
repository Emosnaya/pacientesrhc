<?php
/**
 * Script para verificar las últimas citas y sus estados
 * Ejecutar con: php check_citas.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Cita;

echo "\n=== ÚLTIMAS 10 CITAS ===\n\n";

$citas = Cita::with('paciente')
    ->orderBy('updated_at', 'desc')
    ->take(10)
    ->get();

foreach ($citas as $cita) {
    echo sprintf(
        "ID: %d | Fecha: %s | Hora: %s | Estado: %s | Paciente: %s | Updated: %s\n",
        $cita->id,
        $cita->fecha->format('Y-m-d'),
        $cita->hora ? $cita->hora->format('H:i') : 'N/A',
        $cita->estado,
        $cita->paciente ? $cita->paciente->nombre : 'N/A',
        $cita->updated_at->format('Y-m-d H:i:s')
    );
    
    if ($cita->motivo_cancelacion) {
        echo "   Motivo cancelación: {$cita->motivo_cancelacion}\n";
    }
    echo "\n";
}

echo "=== FIN ===\n\n";
