<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('expediente_pulmonars', function (Blueprint $table) {
            $table->text('antecedentes_exposicionales')->nullable()->after('antecedentes_traumaticos');
            $table->integer('fio2_inicial')->nullable()->after('fc_inicial');
            $table->integer('fio2_final')->nullable()->after('fc_final');
        });
    }

    public function down()
    {
        Schema::table('expediente_pulmonars', function (Blueprint $table) {
            $table->dropColumn(['antecedentes_exposicionales', 'fio2_inicial', 'fio2_final']);
        });
    }
};
