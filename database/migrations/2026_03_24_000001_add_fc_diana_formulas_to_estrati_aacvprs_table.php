<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar campos karvonen, blackburn, narita a estrati_aacvprs
     * para calcular FC Diana igual que en estratificaciones INCH.
     */
    public function up(): void
    {
        Schema::table('estrati_aacvprs', function (Blueprint $table) {
            $table->decimal('karvonen', 8, 2)->nullable()->after('fc_diana');
            $table->decimal('blackburn', 8, 2)->nullable()->after('karvonen');
            $table->decimal('narita', 8, 2)->nullable()->after('blackburn');
        });
    }

    public function down(): void
    {
        Schema::table('estrati_aacvprs', function (Blueprint $table) {
            $table->dropColumn(['karvonen', 'blackburn', 'narita']);
        });
    }
};
