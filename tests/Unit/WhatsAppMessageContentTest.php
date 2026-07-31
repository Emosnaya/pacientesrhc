<?php

namespace Tests\Unit;

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Paciente;
use App\Models\User;
use App\Services\WhatsAppService;
use ReflectionMethod;
use Tests\TestCase;

class WhatsAppMessageContentTest extends TestCase
{
    public function test_cita_confirmada_no_solicita_confirmacion(): void
    {
        $mensaje = $this->generarMensaje('confirmada');

        $this->assertStringNotContainsString('Confirmo mi asistencia', $mensaje);
        $this->assertStringNotContainsString('Con:', $mensaje);
        $this->assertStringNotContainsString('nuestro equipo', $mensaje);
        $this->assertStringContainsString('Necesito reagendar', $mensaje);
        $this->assertStringContainsString('Cancelar cita', $mensaje);
    }

    public function test_cita_pendiente_si_solicita_confirmacion(): void
    {
        $mensaje = $this->generarMensaje('pendiente');

        $this->assertStringContainsString('Confirmo mi asistencia', $mensaje);
        $this->assertStringContainsString('pendiente de confirmación', $mensaje);
    }

    public function test_reagenda_informa_nuevo_horario(): void
    {
        $mensaje = $this->generarMensaje('confirmada', 'generarMensajeReagendada');

        $this->assertStringContainsString('fue reagendada', $mensaje);
        $this->assertStringContainsString('10:00', $mensaje);
        $this->assertStringNotContainsString('Confirmo mi asistencia', $mensaje);
    }

    public function test_cancelacion_no_incluye_opciones_accionables(): void
    {
        $mensaje = $this->generarMensaje('cancelada', 'generarMensajeCancelacion', null, 'Cambio de agenda');

        $this->assertStringContainsString('fue cancelada', $mensaje);
        $this->assertStringContainsString('Cambio de agenda', $mensaje);
        $this->assertStringNotContainsString('Necesito reagendar', $mensaje);
    }

    public function test_asignacion_muestra_nombre_del_profesional(): void
    {
        $doctor = new User();
        $doctor->nombre = 'Ana';
        $doctor->apellidoPat = 'Pérez';

        $mensaje = $this->generarMensaje('confirmada', 'generarMensajeDoctorAsignado', $doctor);

        $this->assertStringContainsString('Se asignó un profesional', $mensaje);
        $this->assertStringContainsString('Ana Pérez', $mensaje);
    }

    private function generarMensaje(
        string $estado,
        string $metodo = 'generarMensajeConfirmacion',
        ?User $doctor = null,
        ?string $motivoCancelacion = null
    ): string
    {
        $paciente = new Paciente();
        $paciente->nombre = 'Paciente';

        $clinica = new Clinica(['nombre' => 'Clínica de prueba']);

        $cita = new Cita([
            'fecha' => '2030-01-15',
            'hora' => '10:00',
            'estado' => $estado,
            'motivo_cancelacion' => $motivoCancelacion,
        ]);
        $cita->setRelation('paciente', $paciente);
        $cita->setRelation('clinica', $clinica);
        $cita->setRelation('user', $doctor);

        $method = new ReflectionMethod(WhatsAppService::class, $metodo);
        $method->setAccessible(true);

        return $method->invoke(app(WhatsAppService::class), $cita);
    }
}
