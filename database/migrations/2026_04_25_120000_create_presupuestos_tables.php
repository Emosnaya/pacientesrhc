<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // quien crea

            $table->string('titulo', 180);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['borrador', 'enviado', 'aceptado', 'rechazado', 'cancelado', 'completado'])->default('borrador');
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vigencia')->nullable();

            $table->decimal('monto_total', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->text('condiciones_pago')->nullable();

            // Aceptación del paciente en portal
            $table->timestamp('paciente_aceptado_at')->nullable();
            $table->timestamp('paciente_rechazado_at')->nullable();
            $table->enum('paciente_aceptacion_tipo', ['visualizacion', 'firma'])->nullable();
            $table->longText('firma_paciente')->nullable(); // base64
            $table->string('paciente_aceptacion_ip', 64)->nullable();
            $table->text('paciente_aceptacion_user_agent')->nullable();

            $table->timestamps();

            $table->index(['clinica_id', 'paciente_id']);
            $table->index(['clinica_id', 'estado']);
        });

        Schema::create('presupuesto_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();
            $table->string('concepto', 255);
            $table->text('descripcion')->nullable();
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuesto_items');
        Schema::dropIfExists('presupuestos');
    }
};
