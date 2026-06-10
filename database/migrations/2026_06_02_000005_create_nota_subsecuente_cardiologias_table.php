<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_subsecuente_cardiologias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedTinyInteger('tipo_exp')->default(39);

            $table->date('fecha_consulta');
            $table->time('hora')->nullable();
            $table->text('motivo_consulta')->nullable();
            $table->text('sintomas')->nullable();
            $table->text('exploracion_fisica')->nullable();
            $table->text('diagnostico_principal')->nullable();
            $table->string('diagnostico_cie10', 20)->nullable();
            $table->text('diagnosticos_secundarios')->nullable();
            $table->text('estudios_solicitados')->nullable();
            $table->date('proxima_cita')->nullable();

            $table->timestamps();

            $table->index(['paciente_id', 'fecha_consulta']);
            $table->index(['clinica_id', 'fecha_consulta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_subsecuente_cardiologias');
    }
};
