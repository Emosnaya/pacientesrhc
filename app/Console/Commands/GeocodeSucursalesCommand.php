<?php

namespace App\Console\Commands;

use App\Models\Sucursal;
use Illuminate\Console\Command;

class GeocodeSucursalesCommand extends Command
{
    protected $signature = 'sucursales:geocode
        {--limit=50 : Máximo de sucursales a procesar}
        {--force : Re-geocodificar aunque ya tengan coordenadas}
        {--only-missing : Solo sin latitud/longitud (default implícito sin --force)}';

    protected $description = 'Geocodifica sucursales con Google Maps a partir de su dirección registrada';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $force = (bool) $this->option('force');

        $query = Sucursal::query()->orderBy('id');
        if (! $force) {
            $query->where(function ($q) {
                $q->whereNull('latitud')->orWhereNull('longitud');
            });
        }

        $rows = $query->limit($limit)->get();
        if ($rows->isEmpty()) {
            $this->info('No hay sucursales pendientes de geocodificar.');

            return self::SUCCESS;
        }

        $ok = 0;
        $fail = 0;

        foreach ($rows as $sucursal) {
            $before = $sucursal->tiene_coordenadas;
            $sucursal->syncGeocode(true);
            $sucursal->refresh();

            if ($sucursal->geocode_status === 'OK' && $sucursal->tiene_coordenadas) {
                $ok++;
                $this->line("OK  #{$sucursal->id} {$sucursal->nombre} → {$sucursal->latitud}, {$sucursal->longitud}");
            } else {
                $fail++;
                $kept = $before && $sucursal->tiene_coordenadas ? ' (coords anteriores conservadas)' : '';
                $this->warn("FAIL #{$sucursal->id} {$sucursal->nombre} status={$sucursal->geocode_status}{$kept}");
            }

            usleep(200000);
        }

        $this->info("Listo. ok={$ok} fail={$fail}");

        return self::SUCCESS;
    }
}
