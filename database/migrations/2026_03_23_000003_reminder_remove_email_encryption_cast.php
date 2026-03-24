<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Esta migración es un recordatorio para quitar el cast 'encrypted' del email.
     * 
     * DESPUÉS de ejecutar esta migración, editar app/Models/Paciente.php:
     * 
     * Cambiar:
     *   'email' => 'encrypted',
     * 
     * Por:
     *   // 'email' - sin encriptación para permitir índice único
     * 
     * O simplemente eliminar la línea del cast.
     */
    public function up(): void
    {
        // Solo es un marcador/recordatorio.
        // El email ya fue desencriptado en la migración anterior.
        // Ahora el modelo debe dejar de usar 'encrypted' para email.
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║  IMPORTANTE: Editar app/Models/Paciente.php                  ║\n";
        echo "║  Quitar 'email' => 'encrypted' del array \$casts             ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    public function down(): void
    {
        // Nada que revertir
    }
};
