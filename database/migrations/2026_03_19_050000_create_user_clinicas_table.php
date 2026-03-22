<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla pivot user_clinicas:
     * Permite que un user-doctor sea propietario/colaborador de múltiples clínicas/consultorios,
     * y que una clínica tenga múltiples usuarios sin duplicar el campo clinica_id del user.
     */
    public function up(): void
    {
        Schema::create('user_clinicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            // propietario = creó el consultorio | colaborador = invitado | visor = solo lectura
            $table->string('rol_en_clinica')->default('colaborador'); // propietario|colaborador|visor
            $table->boolean('activa')->default(true);
            $table->unsignedBigInteger('invitado_por')->nullable();
            $table->foreign('invitado_por')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'clinica_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_clinicas');
    }
};
