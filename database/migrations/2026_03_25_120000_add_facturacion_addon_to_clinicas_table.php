<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add-on de facturación electrónica (CFDI) por volumen — fuera del plan base.
     * Las clínicas ya existentes quedan con el add-on activo para no romper despliegues actuales.
     */
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->boolean('facturacion_addon_activo')->default(false)->after('es_consultorio_privado');
        });

        DB::table('clinicas')->update(['facturacion_addon_activo' => true]);
    }

    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn('facturacion_addon_activo');
        });
    }
};
