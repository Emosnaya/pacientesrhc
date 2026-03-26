<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinica_paciente', function (Blueprint $table) {
            $table->boolean('portal_visible_citas')->default(false)->after('vinculado_at');
            $table->boolean('portal_visible_datos_basicos')->default(false)->after('portal_visible_citas');
            $table->boolean('portal_visible_expediente_resumen')->default(false)->after('portal_visible_datos_basicos');
        });
    }

    public function down(): void
    {
        Schema::table('clinica_paciente', function (Blueprint $table) {
            $table->dropColumn([
                'portal_visible_citas',
                'portal_visible_datos_basicos',
                'portal_visible_expediente_resumen',
            ]);
        });
    }
};
