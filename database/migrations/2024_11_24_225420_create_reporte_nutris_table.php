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
        Schema::create('reporte_nutris', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('sistolica')->nullable();
            $table->string('diastolica')->nullable();
            $table->string('trigliceridos')->nullable();
            $table->string('hdl')->nullable();
            $table->string('ldl')->nullable();
            $table->string('colesterol')->nullable();
            $table->string('aguaSimple')->nullable();
            $table->string('cafe_te')->nullable();
            $table->string('bebidas')->nullable();
            $table->string('light')->nullable();
            $table->string('comidas')->nullable();
            $table->string('actividad')->nullable();
            $table->string('actividadDias')->nullable();
            $table->string('minutosDia')->nullable();
            $table->string('formula')->nullable();
            $table->string('lipidos')->nullable();
            $table->string('Controlglucosa')->nullable();
            $table->string('controlPeso')->nullable();
            $table->string('otro')->nullable();
            $table->string('mmHg')->nullable();
            $table->string('estado')->nullable();
            $table->string('glucosa')->nullable();
            $table->string('recomendaciones')->nullable();
            $table->string('diagnostico')->nullable();
            $table->integer('tipo_exp')->nullable();
            $table->string('nutriologo')->nullable();
            $table->string('cedula_nutriologo')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reporte_nutris');
    }
};
