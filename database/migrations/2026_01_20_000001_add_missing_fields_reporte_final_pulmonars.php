<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reporte_final_pulmonars', function (Blueprint $table) {
            // %FC MAX - después de fc_pico
            $table->decimal('pe_inicial_porcentaje_fcmax', 5, 2)->nullable()->after('pe_inicial_fc_pico');
            $table->decimal('pe_final_porcentaje_fcmax', 5, 2)->nullable()->after('pe_final_fc_pico');
            
            // SpO2 mínima ejercicio - después de BORG fatiga
            $table->integer('pe_inicial_spo2_minima')->nullable()->after('pe_inicial_borg_fatiga');
            $table->integer('pe_final_spo2_minima')->nullable()->after('pe_final_borg_fatiga');
            
            // Nota: dinamometria ya existe en la tabla desde la migración original
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reporte_final_pulmonars', function (Blueprint $table) {
            $table->dropColumn([
                'pe_inicial_porcentaje_fcmax',
                'pe_final_porcentaje_fcmax',
                'pe_inicial_spo2_minima',
                'pe_final_spo2_minima'
            ]);
        });
    }
};
