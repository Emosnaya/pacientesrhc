<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('periodontogramas')) {
            return;
        }

        Schema::create('periodontogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('fecha')->nullable();
            // Array de dientes: numero, ausente, movilidad, furca, pd[6], gm[6], bop[6], placa[6]
            $table->json('dientes')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('plan_tratamiento')->nullable();
            $table->text('observaciones')->nullable();
            // Resumen calculado al guardar
            $table->unsignedSmallInteger('porcentaje_bop')->nullable();
            $table->decimal('promedio_pd', 5, 2)->nullable();
            $table->unsignedTinyInteger('piezas_pd_ge_5')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinica_id', 'paciente_id']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodontogramas');
    }
};
