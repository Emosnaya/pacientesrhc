<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->string('facturacion_serie', 10)->default('FAC')->after('facturacion_tasa_iva');
        });

        // Completar serie/folio desde respuesta Facturapi en registros existentes
        DB::table('solicitudes_factura')
            ->whereNotNull('facturapi_response')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $resp = json_decode($row->facturapi_response, true);
                    if (! is_array($resp)) {
                        continue;
                    }
                    $serie = $resp['series'] ?? $resp['serie'] ?? 'FAC';
                    $folio = $resp['folio_number'] ?? $resp['folio'] ?? null;
                    if ($folio === null) {
                        continue;
                    }
                    DB::table('solicitudes_factura')
                        ->where('id', $row->id)
                        ->update([
                            'serie' => $row->serie ?? $serie,
                            'folio' => $row->folio ?? (int) $folio,
                            'folio_fiscal' => $row->folio_fiscal && strlen($row->folio_fiscal) <= 20
                                ? $row->folio_fiscal
                                : ($serie . $folio),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn('facturacion_serie');
        });
    }
};
