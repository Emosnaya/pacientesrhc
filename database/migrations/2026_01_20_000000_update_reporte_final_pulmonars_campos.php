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
            // ELIMINAR campos que ya no se usan
            $table->dropColumn([
                'pe_inicial_pulso_oxigeno',
                'pe_inicial_ve_maxima',
                'pe_inicial_sit_up',
                'pe_final_pulso_oxigeno',
                'pe_final_ve_maxima',
                'pe_final_sit_up'
            ]);
            
            // AGREGAR nuevos campos después de spo2
            // Litros de oxígeno
            $table->decimal('pe_inicial_litros_oxigeno', 5, 2)->nullable()->after('pe_inicial_spo2');
            $table->decimal('pe_final_litros_oxigeno', 5, 2)->nullable()->after('pe_final_spo2');
            
            // BORG modificado disnea pico (reemplaza el campo borg_modificado genérico)
            $table->dropColumn(['pe_inicial_borg_modificado', 'pe_final_borg_modificado']);
            $table->integer('pe_inicial_borg_disnea')->nullable()->after('pe_inicial_fc_pico');
            $table->integer('pe_final_borg_disnea')->nullable()->after('pe_final_fc_pico');
            
            // BORG modificado fatiga pico
            $table->integer('pe_inicial_borg_fatiga')->nullable()->after('pe_inicial_borg_disnea');
            $table->integer('pe_final_borg_fatiga')->nullable()->after('pe_final_borg_disnea');
            
            // Sit to stand 30 seg
            $table->integer('pe_inicial_sit_to_stand_30seg')->nullable()->after('pe_inicial_borg_fatiga');
            $table->integer('pe_final_sit_to_stand_30seg')->nullable()->after('pe_final_borg_fatiga');
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
            // Restaurar campos eliminados
            $table->integer('pe_inicial_pulso_oxigeno')->nullable();
            $table->integer('pe_inicial_ve_maxima')->nullable();
            $table->integer('pe_inicial_sit_up')->nullable();
            $table->integer('pe_final_pulso_oxigeno')->nullable();
            $table->integer('pe_final_ve_maxima')->nullable();
            $table->integer('pe_final_sit_up')->nullable();
            
            // Eliminar campos nuevos
            $table->dropColumn([
                'pe_inicial_litros_oxigeno',
                'pe_final_litros_oxigeno',
                'pe_inicial_borg_disnea',
                'pe_final_borg_disnea',
                'pe_inicial_borg_fatiga',
                'pe_final_borg_fatiga',
                'pe_inicial_sit_to_stand_30seg',
                'pe_final_sit_to_stand_30seg'
            ]);
            
            // Restaurar borg_modificado genérico
            $table->decimal('pe_inicial_borg_modificado', 8, 2)->nullable();
            $table->decimal('pe_final_borg_modificado', 8, 2)->nullable();
        });
    }
};
