<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pagos MODIFY COLUMN metodo_pago ENUM(
            'efectivo',
            'tarjeta',
            'tarjeta_credito',
            'tarjeta_debito',
            'transferencia'
        ) NOT NULL DEFAULT 'efectivo'");
    }

    public function down(): void
    {
        DB::table('pagos')
            ->whereIn('metodo_pago', ['tarjeta_credito', 'tarjeta_debito'])
            ->update(['metodo_pago' => 'tarjeta']);

        DB::statement("ALTER TABLE pagos MODIFY COLUMN metodo_pago ENUM(
            'efectivo',
            'tarjeta',
            'transferencia'
        ) NOT NULL DEFAULT 'efectivo'");
    }
};
