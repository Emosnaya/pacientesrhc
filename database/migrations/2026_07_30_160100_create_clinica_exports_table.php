<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinica_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->string('status', 20)->default('pending'); // pending|processing|completed|failed
            $table->json('scope')->nullable();
            $table->unsignedInteger('pacientes_total')->default(0);
            $table->unsignedInteger('pacientes_done')->default(0);
            $table->unsignedInteger('expedientes_total')->default(0);
            $table->unsignedInteger('archivos_total')->default(0);
            $table->string('ruta_zip')->nullable();
            $table->unsignedBigInteger('tamanio_bytes')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['clinica_id', 'created_at']);
            $table->index(['status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinica_exports');
    }
};
