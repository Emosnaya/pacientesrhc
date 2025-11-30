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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('clinica_id');
            $table->enum('tipo', ['recordatorio', 'tarea', 'evento'])->default('evento');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('color', 7)->default('#3B82F6'); // Color en formato hex
            $table->boolean('completado')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('clinica_id')->references('id')->on('clinicas')->onDelete('cascade');
            $table->index(['clinica_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventos');
    }
};
