<?php

namespace Tests\Unit;

use App\Models\ClinicaExport;
use Carbon\Carbon;
use Tests\TestCase;

class ClinicaExportModelTest extends TestCase
{
    public function test_progress_percent_handles_empty_and_partial(): void
    {
        $empty = new ClinicaExport([
            'status' => ClinicaExport::STATUS_PENDING,
            'pacientes_total' => 0,
            'pacientes_done' => 0,
        ]);
        $this->assertSame(0, $empty->progressPercent());

        $completedEmpty = new ClinicaExport([
            'status' => ClinicaExport::STATUS_COMPLETED,
            'pacientes_total' => 0,
            'pacientes_done' => 0,
        ]);
        $this->assertSame(100, $completedEmpty->progressPercent());

        $partial = new ClinicaExport([
            'status' => ClinicaExport::STATUS_PROCESSING,
            'pacientes_total' => 4,
            'pacientes_done' => 1,
        ]);
        $this->assertSame(25, $partial->progressPercent());
    }

    public function test_downloadable_requires_completed_path_and_not_expired(): void
    {
        $ready = new ClinicaExport([
            'status' => ClinicaExport::STATUS_COMPLETED,
            'ruta_zip' => 'exports/1/export.zip',
            'expires_at' => Carbon::now()->addDay(),
        ]);
        $this->assertTrue($ready->isDownloadable());
        $this->assertFalse($ready->isExpired());

        $expired = new ClinicaExport([
            'status' => ClinicaExport::STATUS_COMPLETED,
            'ruta_zip' => 'exports/1/export.zip',
            'expires_at' => Carbon::now()->subMinute(),
        ]);
        $this->assertTrue($expired->isExpired());
        $this->assertFalse($expired->isDownloadable());

        $pending = new ClinicaExport([
            'status' => ClinicaExport::STATUS_PENDING,
            'ruta_zip' => null,
            'expires_at' => null,
        ]);
        $this->assertFalse($pending->isDownloadable());
    }
}
