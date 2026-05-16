<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente_nutricion_seguimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('paciente_nutricion_planes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('fecha');
            $table->json('comidas')->nullable();
            $table->unsignedInteger('agua_ml')->nullable();
            $table->json('ejercicio')->nullable();
            $table->json('habitos')->nullable();
            $table->boolean('cumplio_plan')->nullable();
            $table->unsignedTinyInteger('energia_nivel')->nullable(); // 1-10
            $table->unsignedTinyInteger('hambre_nivel')->nullable(); // 1-10
            $table->text('notas_paciente')->nullable();
            $table->text('notas_clinica')->nullable();
            $table->boolean('completado')->default(false);
            $table->string('capturado_por', 20)->default('paciente'); // paciente|staff
            $table->timestamps();

            $table->unique(['paciente_id', 'clinica_id', 'fecha'], 'uniq_nutri_seguimiento_dia');
            $table->index(['paciente_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_nutricion_seguimientos');
    }
};
