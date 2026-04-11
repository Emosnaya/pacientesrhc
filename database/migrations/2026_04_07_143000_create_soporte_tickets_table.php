<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soporte_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('numero_ticket', 20)->unique(); // TKT-2026-00001
            $table->enum('categoria', ['duda', 'error', 'sugerencia', 'otro'])->default('otro');
            $table->string('asunto', 255);
            $table->text('mensaje');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('clinica_id')->nullable()->constrained('clinicas')->nullOnDelete();
            $table->string('usuario_nombre')->nullable();
            $table->string('usuario_email')->nullable();
            $table->string('clinica_nombre')->nullable();
            $table->enum('status', ['nuevo', 'en_proceso', 'resuelto', 'cerrado'])->default('nuevo');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->text('respuesta')->nullable();
            $table->foreignId('resuelto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resuelto_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('clinica_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soporte_tickets');
    }
};
