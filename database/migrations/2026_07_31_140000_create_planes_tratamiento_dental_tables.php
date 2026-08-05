<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('planes_tratamiento_dental')) {
            Schema::create('planes_tratamiento_dental', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
                $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
                $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('odontograma_id')->nullable()->constrained('odontogramas')->nullOnDelete();
                $table->foreignId('presupuesto_id')->nullable()->constrained('presupuestos')->nullOnDelete();

                $table->string('titulo', 180);
                $table->enum('estado', ['activo', 'completado', 'cancelado'])->default('activo');
                $table->date('fecha')->nullable();
                $table->text('notas')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['clinica_id', 'paciente_id']);
                $table->index(['clinica_id', 'estado']);
            });
        }

        if (! Schema::hasTable('plan_tratamiento_dental_items')) {
            Schema::create('plan_tratamiento_dental_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plan_tratamiento_dental_id')
                    ->constrained('planes_tratamiento_dental')
                    ->cascadeOnDelete();

                $table->string('diente', 10)->nullable();
                $table->string('procedimiento', 255);
                $table->unsignedTinyInteger('fase')->default(1);
                $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'cancelado'])->default('pendiente');
                $table->decimal('precio_estimado', 12, 2)->nullable();
                $table->text('notas')->nullable();
                $table->unsignedInteger('orden')->default(0);
                $table->timestamp('completado_at')->nullable();

                $table->timestamps();

                $table->index(['plan_tratamiento_dental_id', 'estado'], 'ptd_items_plan_estado_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_tratamiento_dental_items');
        Schema::dropIfExists('planes_tratamiento_dental');
    }
};
