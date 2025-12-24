<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nota_evolucion_fisioterapia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Datos generales
            $table->date('fecha');
            $table->time('hora');
            $table->text('diagnostico_fisioterapeutico')->nullable();
            
            // Evolución clínica
            $table->text('observaciones_subjetivas')->nullable();
            $table->integer('dolor_eva')->nullable()->comment('Escala 0-10');
            $table->text('funcionalidad')->nullable();
            $table->text('observaciones_objetivas')->nullable();
            
            // Tratamiento realizado
            $table->text('tecnicas_modalidades_aplicadas')->nullable();
            $table->text('ejercicio_terapeutico')->nullable();
            
            // Respuesta al tratamiento
            $table->text('respuesta_tratamiento')->nullable();
            
            // Plan
            $table->text('plan')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nota_evolucion_fisioterapia');
    }
};
