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
        Schema::create('egresos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('user_id')->comment('Usuario que registra el egreso')->constrained('users')->onDelete('cascade');
            
            // Datos del egreso
            $table->text('monto')->comment('Monto cifrado');
            $table->enum('tipo_egreso', ['compra_material', 'servicio', 'mantenimiento', 'nomina', 'renta', 'servicios_publicos', 'otro'])->default('otro');
            $table->text('concepto')->comment('Concepto del egreso cifrado');
            $table->text('proveedor')->nullable()->comment('Nombre del proveedor cifrado');
            $table->text('factura')->nullable()->comment('Número de factura/ticket cifrado');
            $table->text('notas')->nullable()->comment('Notas adicionales cifradas');
            
            // Timestamps
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['clinica_id', 'created_at']);
            $table->index(['sucursal_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('tipo_egreso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};
