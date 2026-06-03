<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            // true = el precio YA incluye IVA, false = el precio es antes de IVA (default)
            $table->boolean('facturacion_iva_incluido')->default(false)->after('facturapi_api_key_test');
            // Tasa de IVA a aplicar (default 16%)
            $table->decimal('facturacion_tasa_iva', 5, 2)->default(16.00)->after('facturacion_iva_incluido');
        });
    }

    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn(['facturacion_iva_incluido', 'facturacion_tasa_iva']);
        });
    }
};
