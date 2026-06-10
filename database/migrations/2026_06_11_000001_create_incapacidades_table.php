<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incapacidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedTinyInteger('tipo_exp')->default(40);
            $table->unsignedInteger('folio')->nullable();

            $table->enum('tipo_incapacidad', ['escolar', 'laboral', 'deportiva', 'transporte', 'otra']);
            $table->date('fecha_inicio');
            $table->date('fecha_termino');
            $table->text('diagnostico');
            $table->text('comentarios')->nullable();

            $table->timestamps();

            $table->index(['paciente_id', 'fecha_inicio']);
            $table->index(['clinica_id', 'fecha_inicio']);
            $table->index(['clinica_id', 'sucursal_id', 'folio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incapacidades');
    }
};
