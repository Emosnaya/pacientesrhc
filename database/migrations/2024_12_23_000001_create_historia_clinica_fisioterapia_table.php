<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historia_clinica_fisioterapia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Datos generales
            $table->date('fecha');
            $table->time('hora');
            
            // Motivo de consulta y padecimiento
            $table->text('motivo_consulta')->nullable();
            $table->text('padecimiento_actual')->nullable();
            
            // Antecedentes
            $table->text('antecedentes_heredofamiliares')->nullable();
            $table->text('antecedentes_personales_patologicos')->nullable();
            $table->text('antecedentes_personales_no_patologicos')->nullable();
            $table->text('antecedentes_quirurgicos_traumaticos')->nullable();
            
            // Exploración física
            $table->text('signos_vitales')->nullable();
            $table->text('inspeccion')->nullable();
            $table->text('palpacion')->nullable();
            $table->text('rango_movimiento')->nullable();
            $table->text('fuerza_muscular')->nullable();
            $table->text('pruebas_especiales')->nullable();
            
            // Impresión diagnóstica
            $table->text('diagnostico_medico')->nullable();
            $table->text('diagnostico_fisioterapeutico')->nullable();
            
            // Plan terapéutico
            $table->text('objetivos_tratamiento')->nullable();
            $table->text('modalidades_terapeuticas')->nullable();
            $table->text('educacion_paciente')->nullable();
            $table->text('pronostico')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('historia_clinica_fisioterapia');
    }
};
