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
            $table->foreignId('clinica_id')->nullable()->constrained('clinicas')->onDelete('cascade');
            $table->index('clinica_id');
        });

        // Agregar clinica_id a la tabla pacientes
        Schema::table('pacientes', function (Blueprint $table) {
            $table->foreignId('clinica_id')->nullable()->constrained('clinicas')->onDelete('cascade');
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
        // Eliminar clinica_id de la tabla pacientes
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropForeign(['clinica_id']);
            $table->dropIndex(['clinica_id']);
            $table->dropColumn('clinica_id');
        });

        // Eliminar clinica_id de la tabla users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinica_id']);
            $table->dropIndex(['clinica_id']);
            $table->dropColumn('clinica_id');
        });
    }
};
