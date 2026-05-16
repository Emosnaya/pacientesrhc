<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_clinica_soap_nutricional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedInteger('tipo_exp')->default(38);
            $table->date('fecha_elaboracion');
            $table->string('numero_seguimiento')->nullable();
            $table->string('nutriologo_evaluador')->nullable();
            $table->string('encargado_turno')->nullable();

            $table->json('subjetivo')->nullable();
            $table->json('objetivo')->nullable();
            $table->json('analisis')->nullable();
            $table->json('plan')->nullable();

            $table->timestamps();

            $table->index(['paciente_id', 'clinica_id']);
            $table->index('tipo_exp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_clinica_soap_nutricional');
    }
};
