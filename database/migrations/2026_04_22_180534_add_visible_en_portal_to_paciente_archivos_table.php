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
        Schema::table('paciente_archivos', function (Blueprint $table) {
            // Si subido_por_paciente=false: visible_en_portal controla si el paciente lo ve.
            // Si subido_por_paciente=true: el paciente siempre ve sus propios archivos.
            $table->boolean('visible_en_portal')->default(false)->after('subido_por_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paciente_archivos', function (Blueprint $table) {
            //
        });
    }
};
