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
        Schema::table('reporte_fisios', function (Blueprint $table) {
            if (! Schema::hasColumn('reporte_fisios', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            }

            if (! Schema::hasColumn('reporte_fisios', 'sucursal_id')) {
                $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->onDelete('set null');
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
        Schema::table('reporte_fisios', function (Blueprint $table) {
            if (Schema::hasColumn('reporte_fisios', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('reporte_fisios', 'sucursal_id')) {
                $table->dropForeign(['sucursal_id']);
                $table->dropColumn('sucursal_id');
            }
        });
    }
};
