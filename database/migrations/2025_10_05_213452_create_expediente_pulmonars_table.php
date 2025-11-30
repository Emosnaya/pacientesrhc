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
        Schema::create('expediente_pulmonars', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Fecha y hora de consulta
            $table->date('fecha_consulta');
            $table->time('hora_consulta');
            
            // Antecedentes Heredo Familiares
            $table->text('antecedentes_padres')->nullable();
            $table->text('antecedentes_abuelos')->nullable();
            $table->text('antecedentes_otros')->nullable();
            
            // Antecedentes Personales No Patológicos - Inmunizaciones
            $table->boolean('covid19_si_no')->nullable();
            $table->integer('covid19_numero_dosis')->nullable();
            $table->date('covid19_fecha_ultima_dosis')->nullable();
            $table->boolean('influenza_si_no')->nullable();
            $table->integer('influenza_ano')->nullable();
            $table->boolean('neumococo_si_no')->nullable();
            $table->integer('neumococo_ano')->nullable();
            
            // Actividad Física
            $table->boolean('actividad_fisica_si_no')->nullable();
            $table->string('actividad_fisica_tipo')->nullable();
            $table->integer('actividad_fisica_dias_semana')->nullable();
            $table->integer('actividad_fisica_tiempo_dia')->nullable(); // en minutos
            
            // Antecedentes
            $table->text('antecedentes_alergicos')->nullable();
            $table->text('antecedentes_quirurgicos')->nullable();
            $table->text('antecedentes_traumaticos')->nullable();
            
            // Factores de Riesgo
            $table->text('tabaquismo')->nullable();
            $table->text('alcoholismo')->nullable();
            $table->text('toxicomanias')->nullable();
            
            // Enfermedades Crónico-Degenerativas
            $table->text('enfermedades_cronicas')->nullable(); // JSON con nombre, año, tratamiento
            
            // Medicamentos Actuales
            $table->text('medicamentos_actuales')->nullable(); // JSON con medicamento y dosis
            
            // Motivo de Referencia
            $table->string('medico_envia')->nullable();
            $table->text('motivo_envio')->nullable();
            $table->text('disnea')->nullable();
            $table->text('fatiga')->nullable();
            $table->text('tos')->nullable();
            $table->text('dolor')->nullable();
            $table->text('independencia_avd')->nullable();
            $table->text('sueño')->nullable();
            $table->text('estado_emocional')->nullable();
            
            // Exploración Física - Nota: peso, talla e IMC se obtienen del modelo Paciente
            $table->integer('fc')->nullable(); // Frecuencia cardíaca
            $table->string('ta')->nullable(); // Tensión arterial
            $table->integer('sat_aa')->nullable(); // Saturación aire ambiente
            $table->integer('sat_fio2')->nullable(); // Saturación con FIO2
            $table->integer('fio2')->nullable(); // Fracción inspirada de oxígeno
            
            // Exploración por sistemas
            $table->text('cabeza_cuello')->nullable();
            $table->text('torax')->nullable();
            $table->text('extremidades')->nullable();
            $table->text('equilibrio')->nullable();
            $table->text('marcha')->nullable();
            $table->integer('sit_to_stand_30seg')->nullable();
            $table->integer('sit_to_stand_60seg')->nullable();
            $table->decimal('dinamometria_derecha', 5, 2)->nullable();
            $table->decimal('dinamometria_izquierda', 5, 2)->nullable();
            $table->text('otros_exploracion')->nullable();
            
            // Diagnósticos y Plan
            $table->text('diagnosticos_finales')->nullable();
            $table->text('plan_tratamiento')->nullable();
            
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
        Schema::dropIfExists('expediente_pulmonars');
    }
};
