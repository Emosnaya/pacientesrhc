<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Marca registros de emergencia para diferenciación en auditorías COFEPRIS
     */
    public function up()
    {
        Schema::table('odontogramas', function (Blueprint $table) {
            $table->boolean('is_emergency_record')->default(false)->after('sucursal_id');
            $table->index('is_emergency_record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('odontogramas', function (Blueprint $table) {
            $table->dropIndex(['is_emergency_record']);
            $table->dropColumn('is_emergency_record');
        });
    }
};
