<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('historia_clinica_fisioterapia', function (Blueprint $table) {
            $table->string('ocupacion', 255)->nullable()->after('hora');
        });
    }

    public function down()
    {
        Schema::table('historia_clinica_fisioterapia', function (Blueprint $table) {
            $table->dropColumn('ocupacion');
        });
    }
};
