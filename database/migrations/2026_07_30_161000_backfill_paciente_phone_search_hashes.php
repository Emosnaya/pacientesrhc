<?php

use App\Services\PhoneHashService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pacientes', 'telefono_search_hash')) {
            return;
        }

        $phoneHashService = app(PhoneHashService::class);

        DB::table('pacientes')
            ->whereNull('telefono_search_hash')
            ->whereNotNull('telefono')
            ->select(['id', 'telefono'])
            ->orderBy('id')
            ->chunkById(250, function ($pacientes) use ($phoneHashService) {
                foreach ($pacientes as $paciente) {
                    try {
                        $telefono = Crypt::decryptString($paciente->telefono);
                    } catch (\Throwable) {
                        // Compatibilidad con teléfonos legacy que aún estén en texto plano.
                        $telefono = $paciente->telefono;
                    }

                    $hash = $phoneHashService->hash($telefono);
                    if ($hash) {
                        DB::table('pacientes')
                            ->where('id', $paciente->id)
                            ->update(['telefono_search_hash' => $hash]);
                    }
                }
            });
    }

    public function down(): void
    {
        // El hash es derivado y se conserva para no degradar búsquedas existentes.
    }
};
