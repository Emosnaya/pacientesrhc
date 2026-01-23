<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historia_clinica_dental', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Doctor que crea
            
            // Datos de la consulta
            $table->string('lugar')->nullable();
            $table->date('fecha')->nullable();
            $table->string('nombre_doctor')->nullable();
            $table->string('cedula_profesional')->nullable();
            
            // Alergias
            $table->text('alergias')->nullable();
            
            // Información adicional del paciente
            $table->boolean('toma_medicamento')->default(false);
            $table->text('medicamento_detalle')->nullable();
            $table->boolean('alergico_anestesicos')->default(false);
            $table->text('anestesicos_detalle')->nullable();
            $table->boolean('alergico_medicamentos')->default(false);
            $table->text('medicamentos_alergicos_detalle')->nullable();
            $table->boolean('embarazada')->default(false);
            $table->boolean('toma_anticonceptivos')->default(false);
            
            // Información dental adicional
            $table->boolean('mal_aliento')->default(false);
            $table->boolean('hipersensibilidad_dental')->default(false);
            $table->boolean('respira_boca')->default(false);
            $table->boolean('muerde_unas')->default(false);
            $table->boolean('muerde_labios')->default(false);
            $table->boolean('aprieta_dientes')->default(false);
            $table->integer('veces_cepilla_dia')->nullable();
            $table->text('higienizacion_metodo')->nullable();
            $table->date('ultima_visita_odontologo')->nullable();
            
            // Historia de la enfermedad
            $table->text('historia_enfermedad')->nullable();
            $table->text('motivo_consulta')->nullable();
            
            // Antecedentes Familiares (SI/NO)
            $table->boolean('af_diabetes')->default(false);
            $table->boolean('af_hipertension')->default(false);
            $table->boolean('af_cancer')->default(false);
            $table->boolean('af_cardiacas')->default(false);
            $table->boolean('af_vih')->default(false);
            $table->boolean('af_epilepsia')->default(false);
            
            // Información Patológica del Paciente (SI/NO)
            $table->boolean('ip_diabetes')->default(false);
            $table->boolean('ip_hipertension')->default(false);
            $table->boolean('ip_cancer')->default(false);
            $table->boolean('ip_cardiacas')->default(false);
            $table->boolean('ip_veneras')->default(false);
            $table->boolean('ip_epilepsia')->default(false);
            $table->boolean('ip_asma')->default(false);
            $table->boolean('ip_gastricas')->default(false);
            $table->boolean('ip_cicatriz')->default(false);
            $table->boolean('ip_presion_alta_baja')->default(false);
            
            // Antecedentes Toxicológicos (SI/NO con detalles)
            $table->boolean('at_fuma')->default(false);
            $table->string('at_fuma_detalle')->nullable(); // "Por día: X"
            $table->boolean('at_drogas')->default(false);
            $table->string('at_drogas_detalle')->nullable(); // "¿Cuáles?"
            $table->boolean('at_toma')->default(false);
            $table->string('at_toma_detalle')->nullable(); // "Frecuencia"
            
            // Antecedentes Ginecoobstétricos (para mujeres)
            $table->boolean('ag_menarca')->default(false);
            $table->integer('ag_menarca_edad')->nullable();
            $table->boolean('ag_menopausia')->default(false);
            $table->integer('ag_menopausia_edad')->nullable();
            $table->boolean('ag_embarazo')->default(false);
            
            // Antecedentes Odontológicos (SI/NO)
            $table->boolean('ao_limpieza_6meses')->default(false);
            $table->boolean('ao_sangrado')->default(false);
            $table->boolean('ao_dolor_abrir')->default(false);
            $table->boolean('ao_tratamiento_ortodoncia')->default(false);
            $table->boolean('ao_morder_labios')->default(false);
            $table->boolean('ao_dieta_dulces')->default(false);
            $table->boolean('ao_cepilla_dientes')->default(false);
            $table->boolean('ao_trauma_cara')->default(false);
            $table->boolean('ao_dolor_masticar')->default(false);
            
            // Examen de Tejidos Blandos (áreas para escribir)
            $table->text('etb_carrillos')->nullable();
            $table->text('etb_encias')->nullable();
            $table->text('etb_lengua')->nullable();
            $table->text('etb_paladar')->nullable();
            $table->text('etb_atm')->nullable();
            $table->text('etb_labios')->nullable();
            
            // Signos Vitales
            $table->string('sv_ta')->nullable(); // Tensión arterial
            $table->string('sv_pulso')->nullable();
            $table->string('sv_fc')->nullable(); // Frecuencia cardíaca
            $table->string('sv_peso')->nullable();
            $table->string('sv_altura')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('paciente_id');
            $table->index('sucursal_id');
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historia_clinica_dental');
    }
};
