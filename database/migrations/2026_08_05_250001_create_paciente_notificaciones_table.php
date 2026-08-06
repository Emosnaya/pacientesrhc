<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('paciente_notificaciones')) {
            return;
        }

        Schema::create('paciente_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->string('tipo', 64);
            $table->string('titulo');
            $table->text('cuerpo')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();

            $table->index(['paciente_id', 'leida_at']);
            $table->index(['paciente_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_notificaciones');
    }
};
