<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Invitaciones para que un doctor invite colaboradores a su consultorio privado.
     * El token es de un solo uso; el invitado lo acepta desde el email.
     */
    public function up(): void
    {
        Schema::create('consultorio_invitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->string('email');                     // email del invitado
            $table->string('token', 64)->unique();       // token de aceptación
            $table->string('rol')->default('colaborador'); // rol que tendrá al aceptar
            $table->foreignId('invitado_por')->constrained('users')->cascadeOnDelete();
            $table->enum('estado', ['pendiente', 'aceptada', 'rechazada', 'expirada'])
                  ->default('pendiente');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['token', 'estado']);
            $table->index(['email', 'clinica_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultorio_invitaciones');
    }
};
