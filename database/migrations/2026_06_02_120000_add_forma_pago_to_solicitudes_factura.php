<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_factura', function (Blueprint $table) {
            $table->string('forma_pago', 2)->nullable()->after('total')
                ->comment('c_FormaPago SAT: 01 efectivo, 03 transferencia, 04 tarjeta');
            $table->string('metodo_pago_cfdi', 3)->default('PUE')->after('forma_pago')
                ->comment('Método CFDI: PUE o PPD');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_factura', function (Blueprint $table) {
            $table->dropColumn(['forma_pago', 'metodo_pago_cfdi']);
        });
    }
};
