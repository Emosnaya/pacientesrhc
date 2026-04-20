<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacto_comercial_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Información de contacto
            $table->string('nombre_contacto');
            $table->string('email');
            $table->string('telefono')->nullable();
            $table->string('cargo')->nullable();
            
            // Detalles de la solicitud
            $table->enum('tipo_solicitud', ['renovacion', 'upgrade', 'informacion', 'otro'])->default('renovacion');
            $table->text('mensaje')->nullable();
            $table->string('plan_actual')->nullable();
            $table->string('plan_interes')->nullable();
            
            // Estado del seguimiento
            $table->enum('estado', ['pendiente', 'contactado', 'en_negociacion', 'cerrado', 'rechazado'])->default('pendiente');
            $table->text('notas_internas')->nullable();
            $table->timestamp('fecha_contacto')->nullable();
            $table->foreignId('atendido_por')->nullable()->constrained('users')->onDelete('set null');
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['clinica_id', 'estado']);
            $table->index('estado');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacto_comercial_solicitudes');
    }
};
