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
        Schema::table('expediente_pulmonars', function (Blueprint $table) {
            // === EVALUACIÓN FUNCIONAL ===
            
            // 1. Signos Vitales Iniciales
            $table->integer('sat_inicial')->nullable()->after('fio2');
            $table->integer('fc_inicial')->nullable()->after('sat_inicial');
            
            // 2. Sit to Stand 5 rep (en segundos)
            $table->decimal('sit_to_stand_5rep', 8, 2)->nullable()->after('fc_inicial');
            
            // 3, 4, 5 ya existen: sit_to_stand_30seg, sit_to_stand_60seg, dinamometria_derecha, dinamometria_izquierda
            
            // VALORES FINALES para Sit to Stand
            $table->decimal('sit_to_stand_5rep_final', 8, 2)->nullable()->after('otros_exploracion');
            $table->integer('sit_to_stand_30seg_final')->nullable()->after('sit_to_stand_5rep_final');
            $table->integer('sit_to_stand_60seg_final')->nullable()->after('sit_to_stand_30seg_final');
            $table->decimal('dinamometria_derecha_final', 5, 2)->nullable()->after('sit_to_stand_60seg_final');
            $table->decimal('dinamometria_izquierda_final', 5, 2)->nullable()->after('dinamometria_derecha_final');
            
            // 6. Signos Vitales Finales
            $table->integer('sat_final')->nullable()->after('dinamometria_izquierda_final');
            $table->integer('fc_final')->nullable()->after('sat_final');
            $table->integer('nadir')->nullable()->after('fc_final');
            $table->integer('fc_pico')->nullable()->after('nadir');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expediente_pulmonars', function (Blueprint $table) {
            $table->dropColumn([
                'sat_inicial',
                'fc_inicial',
                'sit_to_stand_5rep',
                'sit_to_stand_5rep_final',
                'sit_to_stand_30seg_final',
                'sit_to_stand_60seg_final',
                'dinamometria_derecha_final',
                'dinamometria_izquierda_final',
                'sat_final',
                'fc_final',
                'nadir',
                'fc_pico'
            ]);
        });
    }
};
