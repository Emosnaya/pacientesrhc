<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega campo para controlar si una clínica puede manejar múltiples sucursales.
     * Esto permite ofrecer diferentes paquetes/planes:
     * - Clínica única: permite_multiples_sucursales = false (un solo lugar)
     * - Clínica con sucursales: permite_multiples_sucursales = true (múltiples ubicaciones)
     */
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->boolean('permite_multiples_sucursales')->default(false)->after('activa');
            $table->index('permite_multiples_sucursales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropIndex(['permite_multiples_sucursales']);
            $table->dropColumn('permite_multiples_sucursales');
        });
    }
};
