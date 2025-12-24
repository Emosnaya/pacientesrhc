<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nota_alta_fisioterapia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Datos generales
            $table->date('fecha');
            $table->text('diagnostico_medico')->nullable();
            $table->text('diagnostico_fisioterapeutico_inicial')->nullable();
            
            // Resumen clínico
            $table->date('fecha_inicio_atencion')->nullable();
            $table->date('fecha_termino')->nullable();
            $table->integer('numero_sesiones')->nullable();
            
            // Tratamiento otorgado
            $table->text('tratamiento_otorgado')->nullable();
            
            // Evolución y resultados
            $table->text('evolucion_resultados')->nullable();
            $table->integer('dolor_alta_eva')->nullable()->comment('Escala 0-10');
            $table->text('mejoria_funcional')->nullable();
            $table->text('objetivos_alcanzados')->nullable();
            
            // Estado funcional al alta
            $table->text('estado_funcional_alta')->nullable();
            
            // Recomendaciones y plan de seguimiento
            $table->text('recomendaciones_seguimiento')->nullable();
            
            // Pronóstico funcional
            $table->text('pronostico_funcional')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nota_alta_fisioterapia');
    }
};
