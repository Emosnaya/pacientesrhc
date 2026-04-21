<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega columna 'origen' para identificar de dónde viene el ticket:
     * - 'dashboard': Panel de clínica/consultorio
     * - 'portal_paciente': Portal del paciente
     */
    public function up()
    {
        Schema::table('soporte_tickets', function (Blueprint $table) {
            $table->string('origen', 50)->default('dashboard')->after('prioridad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('soporte_tickets', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};
