<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_subsecuente_cardiologias', function (Blueprint $table) {
            $table->json('laboratorios')->nullable()->after('estudios_solicitados');
        });
    }

    public function down(): void
    {
        Schema::table('nota_subsecuente_cardiologias', function (Blueprint $table) {
            $table->dropColumn('laboratorios');
        });
    }
};
