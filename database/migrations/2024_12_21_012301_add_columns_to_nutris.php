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
        Schema::table('reporte_nutris', function (Blueprint $table) {
            $table->string('observaciones',1000)->nullable();
            $table->string('recomendacion',1000)->nullable();
            $table->string('controlPresion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nutris', function (Blueprint $table) {
            $table->dropColumn('observaciones');
            $table->dropColumn('recomendacion');
            $table->dropColumn('controlPresion');
        });
    }
};
