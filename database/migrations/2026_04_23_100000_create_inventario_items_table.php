<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('categoria')->nullable(); // medicamento, insumo, equipo, etc.
            $table->string('unidad')->default('pza'); // pza, caja, frasco, ml, mg, etc.
            $table->decimal('cantidad', 10, 2)->default(0);
            $table->decimal('cantidad_minima', 10, 2)->default(0); // alerta de stock bajo
            $table->decimal('precio_costo', 10, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_items');
    }
};
