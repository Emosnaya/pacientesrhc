<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripcion_facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            
            // Plan y estado
            $table->enum('plan', ['basico', 'pro', 'enterprise'])->default('basico');
            $table->enum('estado', ['activa', 'pausada', 'cancelada', 'vencida'])->default('activa');
            
            // Límites y uso
            $table->unsignedInteger('cantidad_facturas_limite')->default(100);
            $table->unsignedInteger('cantidad_facturas_usadas')->default(0);
            
            // Fechas
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento');
            
            // Precio
            $table->decimal('precio_mensual', 10, 2)->default(499.00);
            
            // Facturapi
            $table->string('facturapi_subscription_id')->nullable();
            
            // Notas
            $table->text('notas')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indices
            $table->index('clinica_id');
            $table->index('estado');
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripcion_facturas');
    }
};
