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
        Schema::table('clinicas', function (Blueprint $table) {
            $table->boolean('es_consultorio_privado')->default(false)->after('activa');
            $table->unsignedBigInteger('propietario_user_id')->nullable()->after('es_consultorio_privado');
            $table->foreign('propietario_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropForeign(['propietario_user_id']);
            $table->dropColumn(['es_consultorio_privado', 'propietario_user_id']);
        });
    }
};
