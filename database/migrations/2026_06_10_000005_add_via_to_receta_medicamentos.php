<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receta_medicamentos', function (Blueprint $table) {
            $table->string('via', 100)->nullable()->after('presentacion');
        });
    }

    public function down(): void
    {
        Schema::table('receta_medicamentos', function (Blueprint $table) {
            $table->dropColumn('via');
        });
    }
};
