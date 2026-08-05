<?php

namespace Tests\Unit;

use App\Models\Clinica;
use App\Models\Sucursal;
use App\Services\SucursalHorarioService;
use Tests\TestCase;

class SucursalHorarioServiceTest extends TestCase
{
    public function test_default_horarios_tiene_todos_los_dias(): void
    {
        $service = new SucursalHorarioService();
        $horarios = $service->defaultHorarios();

        foreach (SucursalHorarioService::DIAS as $dia) {
            $this->assertArrayHasKey($dia, $horarios);
        }

        $this->assertTrue($horarios['lun']['abierto']);
        $this->assertFalse($horarios['dom']['abierto']);
    }

    public function test_slots_para_fecha_respeta_horario(): void
    {
        $service = new SucursalHorarioService();
        $sucursal = new Sucursal([
            'horarios_atencion' => [
                'lun' => ['abierto' => true, 'inicio' => '09:00', 'fin' => '11:00'],
                'mar' => ['abierto' => false, 'inicio' => null, 'fin' => null],
                'mie' => ['abierto' => false, 'inicio' => null, 'fin' => null],
                'jue' => ['abierto' => false, 'inicio' => null, 'fin' => null],
                'vie' => ['abierto' => false, 'inicio' => null, 'fin' => null],
                'sab' => ['abierto' => false, 'inicio' => null, 'fin' => null],
                'dom' => ['abierto' => false, 'inicio' => null, 'fin' => null],
            ],
        ]);

        // 2026-08-03 was a Monday
        $slots = $service->slotsParaFecha($sucursal, '2026-08-03', 30);
        $this->assertSame(['09:00', '09:30', '10:00', '10:30'], $slots);

        // 2026-08-04 was a Tuesday (cerrado)
        $this->assertSame([], $service->slotsParaFecha($sucursal, '2026-08-04', 30));
    }

    public function test_normalize_rechaza_rango_invalido(): void
    {
        $service = new SucursalHorarioService();
        $normalized = $service->normalize([
            'lun' => ['abierto' => true, 'inicio' => '18:00', 'fin' => '09:00'],
        ]);

        $this->assertFalse($normalized['lun']['abierto']);
    }
}
