<?php

namespace Tests\Unit;

use App\Models\Cita;
use App\Models\Clinica;
use App\Services\CitaAvailabilityService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Pruebas de solapamiento con tablas mínimas en sqlite :memory:
 * para no tocar la BD de desarrollo.
 */
class CitaAvailabilityCanBookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        // Reconectar a sqlite
        $this->app['db']->purge();
        $this->app['db']->reconnect();

        Schema::dropIfExists('citas');
        Schema::dropIfExists('eventos');
        Schema::dropIfExists('clinicas');

        Schema::create('clinicas', function (Blueprint $table) {
            $table->id();
            $table->string('citas_solapamiento_modo')->nullable();
            $table->boolean('portal_permite_multiples_citas_mismo_horario')->default(true);
            $table->string('cita_estado_inicial')->default('confirmada');
            $table->timestamps();
        });

        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinica_id');
            $table->unsignedBigInteger('paciente_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->date('fecha');
            $table->string('hora', 8);
            $table->string('estado', 20);
            $table->timestamps();
        });

        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->unsignedBigInteger('clinica_id');
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->date('fecha');
            $table->string('hora', 8)->nullable();
            $table->string('hora_fin', 8)->nullable();
            $table->boolean('todo_el_dia')->default(false);
            $table->string('titulo')->nullable();
            $table->timestamps();
        });
    }

    public function test_modo_permitir_permite_solapamiento_entre_profesionales(): void
    {
        $clinica = Clinica::create(['citas_solapamiento_modo' => 'permitir']);
        Cita::create([
            'clinica_id' => $clinica->id,
            'paciente_id' => 1,
            'user_id' => 10,
            'fecha' => '2030-01-15',
            'hora' => '10:00',
            'estado' => 'confirmada',
        ]);

        $service = new CitaAvailabilityService();
        $check = $service->canBook($clinica, '2030-01-15', '10:00', null, 20, 2);

        $this->assertTrue($check['ok']);
    }

    public function test_modo_profesional_bloquea_mismo_doctor(): void
    {
        $clinica = Clinica::create(['citas_solapamiento_modo' => 'profesional']);
        Cita::create([
            'clinica_id' => $clinica->id,
            'paciente_id' => 1,
            'user_id' => 10,
            'fecha' => '2030-01-15',
            'hora' => '10:00',
            'estado' => 'confirmada',
        ]);

        $service = new CitaAvailabilityService();
        $blocked = $service->canBook($clinica, '2030-01-15', '10:00', null, 10, 2);
        $allowed = $service->canBook($clinica, '2030-01-15', '10:00', null, 20, 2);

        $this->assertFalse($blocked['ok']);
        $this->assertTrue($allowed['ok']);
    }

    public function test_modo_clinica_bloquea_cualquier_cita_activa(): void
    {
        $clinica = Clinica::create(['citas_solapamiento_modo' => 'clinica']);
        Cita::create([
            'clinica_id' => $clinica->id,
            'paciente_id' => 1,
            'user_id' => 10,
            'fecha' => '2030-01-15',
            'hora' => '10:00',
            'estado' => 'pendiente',
        ]);

        $service = new CitaAvailabilityService();
        $check = $service->canBook($clinica, '2030-01-15', '10:00', null, 99, 2);

        $this->assertFalse($check['ok']);
        $this->assertStringContainsString('clínica', $check['message']);
    }

    public function test_cita_cancelada_no_bloquea(): void
    {
        $clinica = Clinica::create(['citas_solapamiento_modo' => 'clinica']);
        Cita::create([
            'clinica_id' => $clinica->id,
            'paciente_id' => 1,
            'user_id' => 10,
            'fecha' => '2030-01-15',
            'hora' => '10:00',
            'estado' => 'cancelada',
        ]);

        $service = new CitaAvailabilityService();
        $check = $service->canBook($clinica, '2030-01-15', '10:00', null, 10, 2);

        $this->assertTrue($check['ok']);
    }

    public function test_mismo_paciente_no_puede_duplicar_horario(): void
    {
        $clinica = Clinica::create(['citas_solapamiento_modo' => 'permitir']);
        Cita::create([
            'clinica_id' => $clinica->id,
            'paciente_id' => 5,
            'user_id' => 10,
            'fecha' => '2030-01-15',
            'hora' => '10:00',
            'estado' => 'confirmada',
        ]);

        $service = new CitaAvailabilityService();
        $check = $service->canBook($clinica, '2030-01-15', '10:00', null, 20, 5);

        $this->assertFalse($check['ok']);
        $this->assertStringContainsString('paciente', strtolower($check['message']));
    }
}
