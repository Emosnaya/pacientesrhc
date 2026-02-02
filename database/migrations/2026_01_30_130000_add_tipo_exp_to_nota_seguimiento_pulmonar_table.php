<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('nota_seguimiento_pulmonar', function (Blueprint $table) {
            $table->integer('tipo_exp')->default(19)->after('clinica_id');
        });
    }

    public function down()
    {
        Schema::table('nota_seguimiento_pulmonar', function (Blueprint $table) {
            $table->dropColumn('tipo_exp');
        });
    }
};
