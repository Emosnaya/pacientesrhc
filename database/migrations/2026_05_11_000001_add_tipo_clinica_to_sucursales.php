<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega tipo_clinica y modulos_habilitados a la tabla sucursales,
     * para que cada sucursal pueda tener una especialidad distinta a la principal.
     * 
     * También asegura que max_sucursales tenga default correcto en clinicas.
     */
    public function up(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            // Especialidad de la sucursal (puede diferir del tipo principal de la clínica)
            $table->string('tipo_clinica')->nullable()->after('notas');
            // Módulos habilitados específicos para esta sucursal (JSON)
            $table->json('modulos_habilitados')->nullable()->after('tipo_clinica');

            $table->index('tipo_clinica');
        });

        // Asegurar que las clínicas que ya tienen permite_multiples_sucursales=true
        // tengan max_sucursales > 1 si estaba en 0 o null
        \DB::statement("
            UPDATE clinicas
            SET max_sucursales = GREATEST(COALESCE(max_sucursales, 1),
                                          (SELECT COUNT(*) FROM sucursales WHERE sucursales.clinica_id = clinicas.id))
            WHERE max_sucursales IS NULL OR max_sucursales < 1
        ");
    }

    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropIndex(['tipo_clinica']);
            $table->dropColumn(['tipo_clinica', 'modulos_habilitados']);
        });
    }
};
