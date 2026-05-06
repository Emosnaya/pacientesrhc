<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_clinica_mensajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinica_id');
            $table->unsignedBigInteger('user_id');
            $table->text('mensaje');
            $table->timestamps();

            $table->foreign('clinica_id')->references('id')->on('clinicas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Para polling incremental por clínica
            $table->index(['clinica_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_clinica_mensajes');
    }
};
