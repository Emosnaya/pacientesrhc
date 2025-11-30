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
        Schema::table('esfuerzos', function (Blueprint $table) {
            $table->boolean('naughton')->nullable()->after('bruce');
            $table->string('tipo_esfuerzo')->nullable()->default('cardiaco')->after('naughton'); // 'cardiaco' o 'pulmonar'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('esfuerzos', function (Blueprint $table) {
            $table->dropColumn(['naughton', 'tipo_esfuerzo']);
        });
    }
};
