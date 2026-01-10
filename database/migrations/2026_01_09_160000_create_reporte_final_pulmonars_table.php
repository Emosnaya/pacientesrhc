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
        Schema::create('reporte_final_pulmonars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained()->onDelete('cascade');
            $table->integer('tipo_exp')->default(15);
            
            // Fechas
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_termino')->nullable();
            
            // Diagnóstico
            $table->text('diagnostico')->nullable();
            
            // Descripción del programa
            $table->text('descripcion_programa')->nullable();
            
            // Prueba de esfuerzo inicial (Rubro)
            $table->string('pe_inicial_rubro')->nullable();
            $table->integer('pe_inicial_fc_basal')->nullable(); // FC basal (lpm)
            $table->integer('pe_inicial_spo2')->nullable(); // SpO2 inicial (%)
            $table->decimal('pe_inicial_carga_maxima', 8, 2)->nullable(); // Carga máxima (MET)
            $table->integer('pe_inicial_vo2_pico')->nullable(); // VO2 pico (%)
            $table->decimal('pe_inicial_vo2_pico_ml', 8, 2)->nullable(); // VO2 Pico ml/kg/min
            $table->integer('pe_inicial_fc_pico')->nullable(); // FC pico ejercicio (lpm)
            $table->integer('pe_inicial_pulso_oxigeno')->nullable(); // Pulso de oxígeno (%)
            $table->decimal('pe_inicial_borg_modificado', 8, 2)->nullable(); // BORG modificado pico
            $table->integer('pe_inicial_ve_maxima')->nullable(); // VE máxima ejercicio (%)
            $table->decimal('pe_inicial_dinamometria', 8, 2)->nullable(); // Dinamometría (kg)
            $table->integer('pe_inicial_sit_up')->nullable(); // Sit Up (repeticiones)
            
            // Prueba de esfuerzo final
            $table->string('pe_final_rubro')->nullable();
            $table->integer('pe_final_fc_basal')->nullable();
            $table->integer('pe_final_spo2')->nullable();
            $table->decimal('pe_final_carga_maxima', 8, 2)->nullable();
            $table->integer('pe_final_vo2_pico')->nullable();
            $table->decimal('pe_final_vo2_pico_ml', 8, 2)->nullable();
            $table->integer('pe_final_fc_pico')->nullable();
            $table->integer('pe_final_pulso_oxigeno')->nullable();
            $table->decimal('pe_final_borg_modificado', 8, 2)->nullable();
            $table->integer('pe_final_ve_maxima')->nullable();
            $table->decimal('pe_final_dinamometria', 8, 2)->nullable();
            $table->integer('pe_final_sit_up')->nullable();
            
            // Resultados clínicos
            $table->text('resultados_clinicos')->nullable();
            
            // Plan de seguimiento
            $table->text('plan')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reporte_final_pulmonars');
    }
};
