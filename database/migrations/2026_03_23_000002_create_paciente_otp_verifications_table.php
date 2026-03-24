<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla para códigos OTP de verificación de pacientes existentes.
     * Cuando un paciente ya existe (por email), la clínica debe verificar
     * que tiene acceso legítimo enviando un OTP al email del paciente.
     */
    public function up(): void
    {
        Schema::create('paciente_otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Quién solicitó
            $table->string('otp_code', 6);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['paciente_id', 'clinica_id', 'otp_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_otp_verifications');
    }
};
