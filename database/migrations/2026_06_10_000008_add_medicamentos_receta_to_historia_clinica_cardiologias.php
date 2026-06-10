<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historia_clinica_cardiologias', function (Blueprint $table) {
            $table->json('medicamentos_receta')->nullable()->after('notas_adicionales');
            $table->text('indicaciones_receta')->nullable()->after('medicamentos_receta');
        });
    }

    public function down(): void
    {
        Schema::table('historia_clinica_cardiologias', function (Blueprint $table) {
            $table->dropColumn(['medicamentos_receta', 'indicaciones_receta']);
        });
    }
};
