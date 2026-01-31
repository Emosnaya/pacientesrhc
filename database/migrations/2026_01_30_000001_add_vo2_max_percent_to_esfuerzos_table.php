<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('esfuerzos', function (Blueprint $table) {
            $table->double('vo2_max_percent')->nullable()->after('po2_teor');
        });
    }

    public function down()
    {
        Schema::table('esfuerzos', function (Blueprint $table) {
            $table->dropColumn('vo2_max_percent');
        });
    }
};
