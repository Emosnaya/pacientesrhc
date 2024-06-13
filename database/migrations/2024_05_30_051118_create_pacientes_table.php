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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('registro');
            $table->string('nombre');
            $table->string('apellidoPat');
            $table->string('apellidoMat');
            $table->string('telefono');
            $table->date('fechaNacimiento');
            $table->string('edad');
            $table->boolean('genero');
            $table->string('estadoCivil');
            $table->string('profesion');
            $table->string('domicilio');
            $table->double('talla');
            $table->double('peso');
            $table->double('cintura');
            $table->double('imc');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pacientes');
    }
};
