<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('planes_facturacion')
            ->where('clave', 'pro')
            ->update([
                'precio_mensual' => 899.00,
                'descripcion' => 'Para clínicas medianas. Incluye hasta 300 facturas al mes con operación fiscal administrada por LynkaMed.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('planes_facturacion')
            ->where('clave', 'pro')
            ->update([
                'precio_mensual' => 799.00,
                'descripcion' => 'Para clínicas medianas. Incluye hasta 300 facturas al mes.',
                'updated_at' => now(),
            ]);
    }
};
