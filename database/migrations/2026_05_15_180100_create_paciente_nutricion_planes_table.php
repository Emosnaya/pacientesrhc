<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente_nutricion_planes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('titulo', 160);
            $table->text('objetivo')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('kcal_objetivo', 8, 2)->nullable();
            $table->json('macros')->nullable();
            $table->json('plan_alimenticio')->nullable();
            $table->json('plan_ejercicio')->nullable();
            $table->text('notas')->nullable();
            $table->string('estado', 20)->default('activo'); // borrador|activo|cerrado
            $table->unsignedInteger('version')->default(1);
            $table->boolean('publicado_en_portal')->default(true);
            $table->timestamps();

            $table->index(['paciente_id', 'clinica_id']);
            $table->index(['clinica_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_nutricion_planes');
    }
};
