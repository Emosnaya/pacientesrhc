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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('user_id')->comment('Usuario que recibe el pago')->constrained('users')->onDelete('cascade');
            $table->foreignId('cita_id')->nullable()->comment('Cita asociada al pago si aplica')->constrained('citas')->onDelete('set null');
            
            // Datos financieros
            $table->text('monto')->comment('Monto cifrado');
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia'])->default('efectivo');
            $table->text('referencia')->nullable()->comment('Referencia bancaria o número de transacción cifrado');
            $table->text('concepto')->nullable()->comment('Concepto del pago cifrado');
            $table->text('notas')->nullable()->comment('Notas adicionales cifradas');
            
            // Firma digital del paciente
            $table->longText('firma_paciente')->nullable()->comment('Firma digital en base64');
            
            // Timestamps
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['clinica_id', 'created_at']);
            $table->index(['sucursal_id', 'created_at']);
            $table->index(['paciente_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('metodo_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
