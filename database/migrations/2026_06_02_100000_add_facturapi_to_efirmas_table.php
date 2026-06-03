<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('efirmas', function (Blueprint $table) {
            $table->string('facturapi_organization_id')->nullable()->after('usar_para_facturacion');
            $table->string('facturapi_api_key_test')->nullable()->after('facturapi_organization_id');
            $table->string('facturapi_api_key_live')->nullable()->after('facturapi_api_key_test');
            $table->boolean('facturapi_configured')->default(false)->after('facturapi_api_key_live');
            $table->string('facturacion_serie', 10)->nullable()->after('facturapi_configured');
        });
    }

    public function down(): void
    {
        Schema::table('efirmas', function (Blueprint $table) {
            $table->dropColumn([
                'facturapi_organization_id',
                'facturapi_api_key_test',
                'facturapi_api_key_live',
                'facturapi_configured',
                'facturacion_serie',
            ]);
        });
    }
};
