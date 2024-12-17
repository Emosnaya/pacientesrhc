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
        Schema::create('reporte_psicos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('motivo_consulta',600)->nullable();
            $table->string('antecedentes_medicos',600)->nullable();
            $table->string('cirugias_previas',600)->nullable();
            $table->string('antecedentes_familiares',600)->nullable();
            $table->string('tratamiento_actual',600)->nullable();
            $table->string('aspectos_sociales',600)->nullable();
            $table->string('escalas_utilizadas',600)->nullable();
            $table->string('sintomas_actuales',600)->nullable();
            $table->string('plan_tratamiento',600)->nullable();
            $table->string('seguimiento',600)->nullable();
            $table->string('calif_salud')->nullable();
            $table->boolean('realizas_ejercicio')->nullable();
            $table->string('ejercicio_frecuencia')->nullable();
            $table->boolean('condicion_medica')->nullable();
            $table->string('dieta_diaria')->nullable();
            $table->boolean('frutas_verduras')->nullable();
            $table->string('frecuencia_comida')->nullable();
            $table->boolean('feliz')->nullable();
            $table->boolean('apoyo_emocional')->nullable();
            $table->string('estres_nivel')->nullable();
            $table->string('frecuencia_reuniones')->nullable();
            $table->boolean('actividades_comunitarias')->nullable();
            $table->boolean('comunidad')->nullable();
            $table->string('situa_financiera')->nullable();
            $table->boolean('seguro_economico')->nullable();
            $table->boolean('ingresos_suficientes')->nullable();
            $table->boolean('trabajo_actual')->nullable();
            $table->boolean('reconocimiento')->nullable();
            $table->boolean('equilibrio_trabajo')->nullable();
            $table->string('alchol_consumo',600)->nullable();
            $table->string('drogas_recreativas',600)->nullable();
            $table->boolean('tabaco_consumo',600)->nullable();
            $table->integer('tipo_exp')->nullable();
            $table->string('psicologo')->nullable();
            $table->string('cedula_psicologo')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reporte_psicos');
    }
};
