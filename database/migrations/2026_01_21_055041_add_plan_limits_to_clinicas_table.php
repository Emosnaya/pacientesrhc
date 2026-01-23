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
            // Agregar campo de duración (mensual/anual)
            $table->enum('duration', ['mensual', 'anual'])->default('mensual')->after('plan');
            
            // Agregar límites según el plan
            $table->integer('max_sucursales')->default(1)->after('duration');
            $table->integer('max_usuarios')->default(3)->after('max_sucursales');
            $table->integer('max_pacientes')->default(200)->after('max_usuarios');
            
            // Índices para mejor rendimiento
            $table->index('duration');
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
            $table->dropIndex(['duration']);
            $table->dropColumn(['duration', 'max_sucursales', 'max_usuarios', 'max_pacientes']);
        });
    }
};
