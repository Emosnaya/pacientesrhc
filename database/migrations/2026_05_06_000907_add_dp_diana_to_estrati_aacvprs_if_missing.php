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
        Schema::table('estrati_aacvprs', function (Blueprint $table) {
            if (!Schema::hasColumn('estrati_aacvprs', 'dp_diana')) {
                $table->decimal('dp_diana', 12, 2)->nullable()->after('fc_diana');
            }
            if (!Schema::hasColumn('estrati_aacvprs', 'carga_inicial')) {
                $table->decimal('carga_inicial', 10, 2)->nullable()->after('dp_diana');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No revertir para no perder datos en prod
    }
};
