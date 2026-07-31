<?php

namespace Tests\Unit;

use App\Models\Clinica;
use App\Models\User;
use App\Services\ExpedienteClinicExportService;
use Mockery;
use Tests\TestCase;

class ExpedienteClinicExportOwnerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_owner_by_propietario_user_id(): void
    {
        $clinica = new Clinica(['propietario_user_id' => 10]);
        $clinica->id = 1;

        $owner = Mockery::mock(User::class)->makePartial();
        $owner->id = 10;

        $other = Mockery::mock(User::class)->makePartial();
        $other->id = 99;
        $relation = Mockery::mock();
        $other->shouldReceive('clinicas')->andReturn($relation);
        $relation->shouldReceive('where')->andReturnSelf();
        $relation->shouldReceive('wherePivot')->andReturnSelf();
        $relation->shouldReceive('exists')->andReturn(false);

        $this->assertTrue(ExpedienteClinicExportService::userIsClinicaOwner($owner, $clinica));
        $this->assertFalse(ExpedienteClinicExportService::userIsClinicaOwner($other, $clinica));
    }

    public function test_owner_by_pivot_rol(): void
    {
        $clinica = new Clinica(['propietario_user_id' => null]);
        $clinica->id = 5;

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 22;

        $relation = Mockery::mock();
        $user->shouldReceive('clinicas')->andReturn($relation);
        $relation->shouldReceive('where')->with('clinicas.id', 5)->andReturnSelf();
        $relation->shouldReceive('wherePivot')->with('rol_en_clinica', 'propietario')->andReturnSelf();
        $relation->shouldReceive('exists')->andReturn(true);

        $this->assertTrue(ExpedienteClinicExportService::userIsClinicaOwner($user, $clinica));
    }

    public function test_colaborador_and_visor_are_rejected(): void
    {
        $clinica = new Clinica(['propietario_user_id' => 1]);
        $clinica->id = 3;

        foreach (['colaborador', 'visor'] as $rol) {
            $user = Mockery::mock(User::class)->makePartial();
            $user->id = 40;
            $relation = Mockery::mock();
            $user->shouldReceive('clinicas')->andReturn($relation);
            $relation->shouldReceive('where')->andReturnSelf();
            $relation->shouldReceive('wherePivot')->andReturnSelf();
            $relation->shouldReceive('exists')->andReturn(false);

            $this->assertFalse(
                ExpedienteClinicExportService::userIsClinicaOwner($user, $clinica),
                "Rol {$rol} no debe ser propietario"
            );
        }
    }
}
