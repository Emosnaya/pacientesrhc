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
        // Agregar clinica_id a la tabla users
        Schema::table('users', function (Blueprint $table) {

        });

        // Agregar clinica_id a la tabla pacientes
        Schema::table('pacientes', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar clinica_id de la tabla pacientes
        Schema::table('pacientes', function (Blueprint $table) {

        });

        // Eliminar clinica_id de la tabla users
        Schema::table('users', function (Blueprint $table) {

        });
    }
};
