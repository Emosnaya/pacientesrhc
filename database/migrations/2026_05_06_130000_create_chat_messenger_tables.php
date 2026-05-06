<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar la tabla simple anterior si existe
        Schema::dropIfExists('chat_clinica_mensajes');

        // Conversaciones (DM o grupo)
        Schema::create('chat_conversaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinica_id');
            $table->enum('tipo', ['directo', 'grupo'])->default('directo');
            $table->string('nombre')->nullable(); // solo para grupos
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('clinica_id')->references('id')->on('clinicas')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('clinica_id');
        });

        // Participantes con tracking de lectura
        Schema::create('chat_participantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversacion_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->foreign('conversacion_id')->references('id')->on('chat_conversaciones')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['conversacion_id', 'user_id']);
        });

        // Mensajes
        Schema::create('chat_mensajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversacion_id');
            $table->unsignedBigInteger('user_id');
            $table->text('mensaje');
            $table->timestamps();

            $table->foreign('conversacion_id')->references('id')->on('chat_conversaciones')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['conversacion_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_mensajes');
        Schema::dropIfExists('chat_participantes');
        Schema::dropIfExists('chat_conversaciones');
    }
};
