<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Estratificación AACVPR / EAPC
     */
    public function up(): void
    {
        Schema::create('estrati_aacvprs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('clinica_id')->nullable()->constrained('clinicas')->onDelete('cascade');
            
            // Fechas
            $table->date('fecha_estratificacion')->nullable();
            $table->date('primeravez_rhc')->nullable();
            $table->date('pe_fecha')->nullable();
            
            // =====================================================
            // TABLA AACVPR/EAPC - RIESGO ALTO
            // =====================================================
            // FEVI severamente disminuida (<40%)
            $table->boolean('alto_fevi_disminuida')->default(false);
            // Síntomas o signos en reposo o a baja carga (≤3METs)
            $table->boolean('alto_sintomas_reposo')->default(false);
            // Respuesta isquémica o angina durante ejercicio de baja intensidad
            $table->boolean('alto_isquemia_baja_intensidad')->default(false);
            // Arritmias ventriculares complejas en reposo o ejercicio
            $table->boolean('alto_arritmias_ventriculares')->default(false);
            // IM o procedimiento de revascularización complicado
            $table->boolean('alto_im_complicado')->default(false);
            // Capacidad funcional <5 METs
            $table->boolean('alto_capacidad_menor_5mets')->default(false);
            // Respuesta hemodinámica anormal al ejercicio (ICC, cambios isquémicos ST, hipotensión)
            $table->boolean('alto_hemodinamica_anormal')->default(false);
            // Paro cardiaco sobrevivido
            $table->boolean('alto_paro_cardiaco')->default(false);
            // Enfermedad coronaria compleja (tronco, multivaso, etc.)
            $table->boolean('alto_enfermedad_compleja')->default(false);
            
            // =====================================================
            // TABLA AACVPR/EAPC - RIESGO MODERADO
            // =====================================================
            // FEVI moderadamente disminuida (40-49%)
            $table->boolean('moderado_fevi_moderada')->default(false);
            // Síntomas o signos a intensidad moderada (3-6 METs)
            $table->boolean('moderado_sintomas_moderados')->default(false);
            // Isquemia leve a moderada durante ejercicio
            $table->boolean('moderado_isquemia_moderada')->default(false);
            // Capacidad funcional 5-7 METs
            $table->boolean('moderado_capacidad_5_7mets')->default(false);
            // Imposibilidad de automonitoreo de FC/síntomas
            $table->boolean('moderado_sin_automonitoreo')->default(false);
            
            // =====================================================
            // TABLA AACVPR/EAPC - RIESGO BAJO
            // =====================================================
            // FEVI preservada (≥50%)
            $table->boolean('bajo_fevi_preservada')->default(false);
            // Sin síntomas ni signos en reposo o ejercicio
            $table->boolean('bajo_sin_sintomas')->default(false);
            // Sin isquemia durante prueba de esfuerzo
            $table->boolean('bajo_sin_isquemia')->default(false);
            // Capacidad funcional ≥7 METs
            $table->boolean('bajo_capacidad_mayor_7mets')->default(false);
            // Sin arritmias complejas
            $table->boolean('bajo_sin_arritmias')->default(false);
            // IM o revascularización no complicada
            $table->boolean('bajo_im_no_complicado')->default(false);
            // Respuesta hemodinámica normal
            $table->boolean('bajo_hemodinamica_normal')->default(false);
            // Capacidad de automonitoreo adecuada
            $table->boolean('bajo_automonitoreo_adecuado')->default(false);
            
            // =====================================================
            // HALLAZGOS CLÍNICOS (campos de texto/valor)
            // =====================================================
            $table->string('hallazgo_fevi')->nullable(); // valor numérico FEVI %
            $table->string('hallazgo_mets')->nullable(); // valor numérico METs
            $table->text('hallazgo_sintomas')->nullable();
            $table->text('hallazgo_isquemia')->nullable();
            $table->text('hallazgo_arritmias')->nullable();
            $table->text('hallazgo_hemodinamica')->nullable();
            $table->text('hallazgo_procedimiento')->nullable();
            $table->text('hallazgo_coronaria')->nullable();
            
            // =====================================================
            // RIESGO GLOBAL (resultado final)
            // =====================================================
            $table->enum('riesgo_global', ['bajo', 'moderado', 'alto'])->default('bajo');
            
            // =====================================================
            // PARÁMETROS INICIALES (igual que estratificación original)
            // =====================================================
            $table->enum('grupo', ['a', 'b', 'c', 'd'])->default('a');
            $table->integer('semanas')->default(1);
            $table->integer('sesiones')->nullable();
            $table->integer('borg')->default(12);
            
            // FC Diana
            $table->enum('fc_diana_metodo', ['Bo', 'K', 'BI', 'N', 'UISQ', 'Manual'])->default('Bo');
            $table->string('fc_diana_str')->nullable();
            $table->decimal('fc_diana', 8, 2)->nullable();
            $table->decimal('fc_diana_manual', 8, 2)->nullable();
            
            // DP Diana y Carga
            $table->decimal('dp_diana', 12, 2)->nullable();
            $table->decimal('carga_inicial', 8, 2)->nullable();
            
            // Comentarios
            $table->text('comentarios')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estrati_aacvprs');
    }
};
