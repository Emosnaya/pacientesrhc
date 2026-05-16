<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimiento_nutricional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedInteger('tipo_exp')->default(37);
            $table->date('fecha_elaboracion');
            $table->string('numero_seguimiento')->nullable();

            $table->json('valoracion_bioquimica')->nullable();
            $table->json('valoracion_dietetica')->nullable();
            $table->json('recordatorio_24h')->nullable();
            $table->json('analisis_dieta_habitual')->nullable();
            $table->json('intervencion_nutricional')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index(['paciente_id', 'clinica_id']);
            $table->index('tipo_exp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_nutricional');
    }
};
