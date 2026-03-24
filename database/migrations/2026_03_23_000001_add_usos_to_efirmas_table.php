<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar campos de usos a la tabla efirmas.
     * Permite usar una sola e.firma para múltiples propósitos.
     */
    public function up(): void
    {
        Schema::table('efirmas', function (Blueprint $table) {
            $table->boolean('usar_para_recetas')->default(true)->after('activa');
            $table->boolean('usar_para_facturacion')->default(false)->after('usar_para_recetas');
        });
    }

    public function down(): void
    {
        Schema::table('efirmas', function (Blueprint $table) {
            $table->dropColumn(['usar_para_recetas', 'usar_para_facturacion']);
        });
    }
};
