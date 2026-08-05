<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fichas_endodoncia')) {
            Schema::create('fichas_endodoncia', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
                $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
                $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

                $table->date('fecha')->nullable();
                $table->unsignedSmallInteger('pieza')->nullable(); // FDI
                $table->string('diagnostico_pulpar', 120)->nullable();
                $table->string('diagnostico_periapical', 120)->nullable();
                $table->string('dolor', 80)->nullable();
                $table->json('pruebas')->nullable(); // frio, calor, percusion, palpacion, movilidad, sonda
                $table->text('hallazgos_rx')->nullable();
                $table->string('etapa', 80)->nullable(); // diagnostico, acceso, instrumentacion, obturacion, control
                $table->string('tecnica', 120)->nullable();
                $table->string('material_obturacion', 120)->nullable();
                $table->unsignedTinyInteger('conductos')->nullable();
                $table->text('tratamiento_realizado')->nullable();
                $table->text('plan_tratamiento')->nullable();
                $table->text('observaciones')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['clinica_id', 'paciente_id']);
                $table->index('fecha');
            });
        }

        if (! Schema::hasTable('fichas_ortodoncia')) {
            Schema::create('fichas_ortodoncia', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
                $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
                $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

                $table->date('fecha')->nullable();
                $table->string('clase_angle', 40)->nullable();
                $table->string('patron_esqueletal', 80)->nullable();
                $table->decimal('overjet_mm', 5, 2)->nullable();
                $table->decimal('overbite_mm', 5, 2)->nullable();
                $table->string('apinamiento', 80)->nullable();
                $table->string('habitos', 255)->nullable();
                $table->string('tipo_aparato', 120)->nullable();
                $table->string('fase', 80)->nullable();
                $table->date('proximo_control')->nullable();
                $table->text('diagnostico')->nullable();
                $table->text('plan_tratamiento')->nullable();
                $table->text('observaciones')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['clinica_id', 'paciente_id']);
                $table->index('fecha');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fichas_endodoncia');
        Schema::dropIfExists('fichas_ortodoncia');
    }
};
