<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->string('facturapi_api_key_live')->nullable()->after('facturapi_api_key_test');
            $table->string('facturapi_mode')->default('test')->after('facturapi_api_key_live'); // 'test' | 'live'
        });
    }

    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn(['facturapi_api_key_live', 'facturapi_mode']);
        });
    }
};
