<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            // Agregar columnas si no existen
            if (!Schema::hasColumn('clinicas', 'facturapi_organization_id')) {
                $table->string('facturapi_organization_id')->nullable();
            }
            if (!Schema::hasColumn('clinicas', 'facturapi_configured')) {
                $table->boolean('facturapi_configured')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn(['facturapi_organization_id', 'facturapi_configured']);
        });
    }
};
