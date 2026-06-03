<?php

namespace App\Console\Commands;

use App\Models\Clinica;
use Illuminate\Console\Command;

class SyncConsultorioTrialDates extends Command
{
    protected $signature = 'consultorios:sync-trial-dates {--dry-run : Solo listar cambios sin guardar}';

    protected $description = 'Rellena trial_ends_at en consultorios a partir del propietario o fecha_vencimiento vigente';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $synced = 0;

        Clinica::query()
            ->where('es_consultorio_privado', true)
            ->where('pagado', false)
            ->with('propietario:id,trial_ends_at')
            ->orderBy('id')
            ->chunkById(100, function ($consultorios) use ($dryRun, &$synced) {
                foreach ($consultorios as $consultorio) {
                    $trialEnd = $consultorio->trialEndsAtEfectivo();
                    if (! $trialEnd) {
                        continue;
                    }

                    $needsTrial = ! $consultorio->trial_ends_at
                        || ! $consultorio->trial_ends_at->equalTo($trialEnd);

                    if (! $needsTrial) {
                        continue;
                    }

                    $this->line(sprintf(
                        '  #%d %s ? trial_ends_at %s',
                        $consultorio->id,
                        $consultorio->nombre,
                        $trialEnd->format('Y-m-d H:i')
                    ));

                    if (! $dryRun) {
                        $consultorio->sincronizarFechasTrial();
                    }

                    $synced++;
                }
            });

        $this->info($dryRun
            ? "Consultorios a sincronizar: {$synced} (dry-run)"
            : "Consultorios sincronizados: {$synced}");

        return self::SUCCESS;
    }
}
