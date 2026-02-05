<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('clinica_id')->nullable()->constrained('clinicas')->onDelete('set null');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->onDelete('set null');
            $table->string('evento'); // created, updated, deleted, viewed
            $table->string('modelo_afectado'); // Paciente, Expediente, Receta, etc.
            $table->unsignedBigInteger('id_recurso'); // ID del registro afectado
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('datos_anteriores')->nullable(); // Estado antes del cambio
            $table->json('datos_nuevos')->nullable(); // Estado después del cambio
            $table->text('descripcion')->nullable(); // Descripción adicional
            $table->timestamps();

            // Índices para consultas rápidas
            $table->index(['user_id', 'created_at']);
            $table->index(['clinica_id', 'created_at']);
            $table->index(['modelo_afectado', 'id_recurso']);
            $table->index('evento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
