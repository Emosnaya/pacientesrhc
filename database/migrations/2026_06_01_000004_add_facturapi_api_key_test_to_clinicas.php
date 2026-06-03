<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            // API key de test de la organización en Facturapi (obtenida al crear org)
            $table->string('facturapi_api_key_test')->nullable()->after('facturapi_configured');
        });
    }

    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn('facturapi_api_key_test');
        });
    }
};
