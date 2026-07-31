<?php

namespace App\Jobs;

use App\Models\ClinicaExport;
use App\Services\ExpedienteClinicExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuildClinicaExpedienteExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(public int $exportId)
    {
    }

    public function handle(ExpedienteClinicExportService $service): void
    {
        $export = ClinicaExport::find($this->exportId);
        if (! $export) {
            return;
        }

        try {
            $service->build($export);
        } catch (\Throwable $e) {
            $service->fail($export, $e);
            throw $e;
        }
    }
}
