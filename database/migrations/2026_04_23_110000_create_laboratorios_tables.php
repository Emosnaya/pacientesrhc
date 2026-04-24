<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Catálogo de laboratorios por clínica ──────────────────────────────
        Schema::create('laboratorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('contacto')->nullable(); // nombre del contacto
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── Órdenes de laboratorio ────────────────────────────────────────────
        Schema::create('ordenes_laboratorio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // quien la genera
            $table->foreignId('laboratorio_id')->nullable()->constrained('laboratorios')->nullOnDelete();
            $table->unsignedInteger('folio'); // folio autoincremental por clínica
            $table->string('estudios');       // lista de estudios solicitados (texto libre)
            $table->text('indicaciones')->nullable();
            $table->text('diagnostico_clinico')->nullable();
            $table->text('notas_laboratorio')->nullable(); // instrucciones al lab
            // Para dentales: URL del modelo dental adjunto
            $table->string('modelo_dental')->nullable();
            // Correo al que se enviará la orden (puede diferir del laboratorio catalogado)
            $table->string('email_laboratorio')->nullable();
            $table->boolean('correo_enviado')->default(false);
            $table->timestamp('correo_enviado_at')->nullable();
            // Status de seguimiento (actualizable por el laboratorio vía portal)
            $table->enum('status', [
                'pendiente',
                'recibida',
                'en_proceso',
                'lista',
                'entregada',
                'cancelada',
            ])->default('pendiente');
            $table->date('fecha_recoleccion')->nullable();       // cuándo el lab recoge/recibe
            $table->date('fecha_entrega_estimada')->nullable();  // cuándo promete entregar
            $table->date('fecha_entrega_real')->nullable();      // cuándo entregó realmente
            $table->timestamps();

            // folio único por clínica
            $table->unique(['clinica_id', 'folio']);
        });

        // ── Tokens para el portal del laboratorio ────────────────────────────
        Schema::create('orden_laboratorio_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes_laboratorio')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable(); // null = sin expiración
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_laboratorio_tokens');
        Schema::dropIfExists('ordenes_laboratorio');
        Schema::dropIfExists('laboratorios');
    }
};
