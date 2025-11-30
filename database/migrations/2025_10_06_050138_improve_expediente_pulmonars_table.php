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
            // Unificar antecedentes heredo familiares en un solo campo
            $table->text('antecedentes_heredo_familiares')->nullable()->after('hora_consulta');
            
            // Cambiar factores de riesgo a boolean
            $table->boolean('tabaquismo_boolean')->nullable()->after('antecedentes_traumaticos');
            $table->text('tabaquismo_detalle')->nullable()->after('tabaquismo_boolean');
            $table->boolean('alcoholismo_boolean')->nullable()->after('tabaquismo_detalle');
            $table->text('alcoholismo_detalle')->nullable()->after('alcoholismo_boolean');
            $table->boolean('toxicomanias_boolean')->nullable()->after('alcoholismo_detalle');
            $table->text('toxicomanias_detalle')->nullable()->after('toxicomanias_boolean');
            
            // Cambiar síntomas a boolean
            $table->boolean('disnea_boolean')->nullable()->after('motivo_envio');
            $table->text('disnea_detalle')->nullable()->after('disnea_boolean');
            $table->boolean('fatiga_boolean')->nullable()->after('disnea_detalle');
            $table->text('fatiga_detalle')->nullable()->after('fatiga_boolean');
            $table->boolean('tos_boolean')->nullable()->after('fatiga_detalle');
            $table->text('tos_detalle')->nullable()->after('tos_boolean');
            $table->boolean('dolor_boolean')->nullable()->after('tos_detalle');
            $table->text('dolor_detalle')->nullable()->after('dolor_boolean');
            $table->boolean('sueno_boolean')->nullable()->after('independencia_avd');
            $table->text('sueno_detalle')->nullable()->after('sueno_boolean');
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
            // Eliminar campos unificados
            $table->dropColumn([
                'antecedentes_heredo_familiares',
                'tabaquismo_boolean',
                'tabaquismo_detalle',
                'alcoholismo_boolean',
                'alcoholismo_detalle',
                'toxicomanias_boolean',
                'toxicomanias_detalle',
                'disnea_boolean',
                'disnea_detalle',
                'fatiga_boolean',
                'fatiga_detalle',
                'tos_boolean',
                'tos_detalle',
                'dolor_boolean',
                'dolor_detalle',
                'sueno_boolean',
                'sueno_detalle'
            ]);
        });
    }
};
