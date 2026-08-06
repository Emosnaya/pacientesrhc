<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            return;
        }

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('origen', 40)->default('pasaporte_qr');
            $table->string('nombre');
            $table->string('email');
            $table->string('telefono', 40)->nullable();
            $table->string('establecimiento')->nullable();
            $table->string('especialidad', 120)->nullable();
            $table->text('mensaje')->nullable();
            /** UUID del pasaporte escaneado (no FK: el lead no es dueño del paciente). */
            $table->uuid('paciente_uuid')->nullable();
            $table->string('estado', 30)->default('nuevo');
            $table->text('notas_internas')->nullable();
            $table->timestamp('contactado_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
            $table->index('origen');
            $table->index('paciente_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
