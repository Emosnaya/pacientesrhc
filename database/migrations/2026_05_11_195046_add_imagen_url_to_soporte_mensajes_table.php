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
        Schema::table('soporte_mensajes', function (Blueprint $table) {
            $table->string('imagen_url')->nullable()->after('mensaje');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('soporte_mensajes', function (Blueprint $table) {
            $table->dropColumn('imagen_url');
        });
    }
};
