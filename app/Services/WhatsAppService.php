<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\WhatsappMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class WhatsAppService
{
    protected ?Client $client = null;
    protected ?string $from = null;
    protected bool $enabled = false;
    protected PhoneHashService $phoneHash;

    public function __construct(?PhoneHashService $phoneHash = null)
    {
        $this->phoneHash = $phoneHash ?: app(PhoneHashService::class);
        $this->enabled = (bool) (
            config('services.twilio.enabled', false)
            || config('services.twilio.whatsapp_enabled', false)
        );

        if ($this->enabled) {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.auth_token');
            if ($sid && $token) {
                $this->client = new Client($sid, $token);
                $this->from = config('services.twilio.whatsapp_from');
            } else {
                $this->enabled = false;
            }
        }
    }

    public function isGloballyEnabled(): bool
    {
        return $this->enabled && ! empty($this->from);
    }

    public function clinicaPuedeEnviar($clinica): bool
    {
        return $this->isGloballyEnabled()
            && (bool) ($clinica?->whatsapp_notificaciones_activas ?? false);
    }

    public function pacientePuedeRecibir(Paciente $paciente): bool
    {
        return (bool) $paciente->whatsapp_notificaciones
            && (bool) $this->phoneHash->normalize($paciente->telefono);
    }

    /**
     * Envía confirmación/solicitud según estado de la cita.
     * @return array{ok: bool, message_id?: int, error?: string}
     */
    public function enviarNotificacionCita(Cita $cita, string $tipo = 'confirmacion'): array
    {
        $cita->loadMissing(['paciente', 'user', 'clinica']);
        $paciente = $cita->paciente;
        $clinica = $cita->clinica;

        if (! $this->clinicaPuedeEnviar($clinica)) {
            return ['ok' => false, 'error' => 'WhatsApp no habilitado para la clínica'];
        }

        if (! $paciente || ! $this->pacientePuedeRecibir($paciente)) {
            return ['ok' => false, 'error' => 'Paciente sin WhatsApp autorizado o teléfono inválido'];
        }

        $telefono = $this->phoneHash->normalize($paciente->telefono);
        $telefonoTwilio = $this->telefonoParaTwilio($telefono);
        $mensaje = match ($tipo) {
            'recordatorio' => $this->generarMensajeRecordatorio($cita),
            'reagendada' => $this->generarMensajeReagendada($cita),
            'cancelacion' => $this->generarMensajeCancelacion($cita),
            'doctor_asignado' => $this->generarMensajeDoctorAsignado($cita),
            'estado' => $this->generarMensajeEstado($cita),
            default => $this->generarMensajeConfirmacion($cita),
        };

        $duplicadoReciente = WhatsappMessage::query()
            ->where('cita_id', $cita->id)
            ->where('tipo', $tipo)
            ->where('body', $mensaje)
            ->whereIn('estado', ['queued', 'sent'])
            ->where('created_at', '>=', now()->subMinute())
            ->exists();

        if ($duplicadoReciente) {
            return ['ok' => true, 'skipped' => true];
        }

        if ($cita->reagendada_de_cita_id) {
            WhatsappMessage::where('cita_id', $cita->reagendada_de_cita_id)
                ->where('accionable', true)
                ->update(['accionable' => false]);
        }

        $accionable = in_array($cita->estado, ['pendiente', 'confirmada'], true)
            && ! in_array($tipo, ['cancelacion'], true);

        $log = WhatsappMessage::create([
            'cita_id' => $cita->id,
            'paciente_id' => $paciente->id,
            'clinica_id' => $clinica->id,
            'tipo' => $tipo,
            'direccion' => 'outbound',
            'estado' => 'queued',
            'telefono_to' => $telefonoTwilio,
            'body' => $mensaje,
            'accionable' => $accionable,
        ]);

        // Solo un mensaje accionable por cita a la vez
        WhatsappMessage::where('cita_id', $cita->id)
            ->where('id', '!=', $log->id)
            ->where('accionable', true)
            ->update(['accionable' => false]);

        try {
            $message = $this->client->messages->create(
                "whatsapp:{$telefonoTwilio}",
                [
                    'from' => $this->from,
                    'body' => $mensaje,
                ]
            );

            $log->update([
                'estado' => 'sent',
                'twilio_sid' => $message->sid,
            ]);

            if ($tipo === 'recordatorio') {
                $cita->update([
                    'recordatorio_enviado' => true,
                    'recordatorio_enviado_at' => now(),
                ]);
            }

            return ['ok' => true, 'message_id' => $log->id];
        } catch (\Throwable $e) {
            $log->update([
                'estado' => 'failed',
                'error' => $e->getMessage(),
                'accionable' => false,
            ]);

            Log::error('Error enviando WhatsApp', [
                'paciente_id' => $paciente->id,
                'cita_id' => $cita->id,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'error' => $e->getMessage(), 'message_id' => $log->id];
        }
    }

    /**
     * El Sandbox compartido de Twilio todavía identifica algunos números
     * mexicanos con el prefijo móvil legacy +521. La normalización y los
     * hashes internos permanecen en el E.164 moderno +52.
     */
    protected function telefonoParaTwilio(string $telefono): string
    {
        $sandboxCompartido = config('services.twilio.whatsapp_from') === 'whatsapp:+14155238886';

        if ($sandboxCompartido && preg_match('/^\+52(\d{10})$/', $telefono, $matches)) {
            return '+521' . $matches[1];
        }

        return $telefono;
    }

    /** Alias legacy */
    public function enviarRecordatorioCita($cita)
    {
        $result = $this->enviarNotificacionCita($cita, 'recordatorio');

        return $result['ok'] ?? false;
    }

    protected function generarMensajeConfirmacion(Cita $cita): string
    {
        $esPendiente = ($cita->estado ?? '') === 'pendiente';
        $intro = $esPendiente
            ? "Tienes una cita pendiente de confirmación:"
            : "Tu cita quedó agendada:";
        $accion = $esPendiente
            ? "Por favor confirma tu asistencia respondiendo:"
            : "Si necesitas cambios, responde:";

        return $this->generarMensajeConOpciones($cita, $intro, $accion);
    }

    protected function generarMensajeRecordatorio(Cita $cita): string
    {
        return $this->generarMensajeConOpciones(
            $cita,
            'Recordatorio de tu cita:',
            $cita->estado === 'pendiente'
                ? 'Por favor confirma tu asistencia respondiendo:'
                : 'Si necesitas cambios, responde:'
        );
    }

    protected function generarMensajeReagendada(Cita $cita): string
    {
        return $this->generarMensajeConOpciones(
            $cita,
            'Tu cita fue reagendada:',
            $cita->estado === 'pendiente'
                ? 'Confirma el nuevo horario respondiendo:'
                : 'Si necesitas cambios, responde:'
        );
    }

    protected function generarMensajeDoctorAsignado(Cita $cita): string
    {
        return $this->generarMensajeConOpciones(
            $cita,
            'Se asignó un profesional a tu cita:',
            $cita->estado === 'pendiente'
                ? 'Por favor confirma tu asistencia respondiendo:'
                : 'Si necesitas cambios, responde:'
        );
    }

    protected function generarMensajeEstado(Cita $cita): string
    {
        if ($cita->estado === 'cancelada') {
            return $this->generarMensajeCancelacion($cita);
        }

        if ($cita->estado === 'completada') {
            return $this->generarMensajeInformativo($cita, 'Tu cita fue marcada como completada.');
        }

        if ($cita->estado === 'pendiente') {
            return $this->generarMensajeConOpciones(
                $cita,
                'Tu cita está pendiente de confirmación:',
                'Por favor confirma tu asistencia respondiendo:'
            );
        }

        return $this->generarMensajeConOpciones(
            $cita,
            'Tu cita fue confirmada:',
            'Si necesitas cambios, responde:'
        );
    }

    protected function generarMensajeCancelacion(Cita $cita): string
    {
        $motivo = trim((string) ($cita->motivo_cancelacion ?? ''));
        $detalle = $motivo !== '' && $motivo !== 'Sin motivo especificado'
            ? "\nMotivo: *{$motivo}*"
            : '';

        return $this->generarMensajeInformativo(
            $cita,
            "Tu cita fue cancelada.{$detalle}\n\nSi deseas agendar nuevamente, contacta a la clínica."
        );
    }

    protected function generarMensajeConOpciones(Cita $cita, string $intro, string $accion): string
    {
        $opciones = $cita->estado === 'pendiente'
            ? "*1* - Confirmo mi asistencia ✅\n" .
                "*2* - Necesito reagendar 📅\n" .
                "*3* - Cancelar cita ❌"
            : "*2* - Necesito reagendar 📅\n" .
                "*3* - Cancelar cita ❌";

        return $this->generarMensajeInformativo($cita, "{$intro}\n{$this->resumenCita($cita)}\n{$accion}\n{$opciones}\n\n_Responde con el número correspondiente._", false);
    }

    protected function generarMensajeInformativo(Cita $cita, string $contenido, bool $incluirResumen = true): string
    {
        $paciente = $cita->paciente;
        $clinica = $cita->clinica->nombre ?? 'la clínica';
        $resumen = $incluirResumen ? "\n{$this->resumenCita($cita)}" : '';

        return "🏥 *{$clinica}*\n\n" .
            "Hola *{$paciente->nombre}*,\n\n" .
            "{$contenido}{$resumen}";
    }

    protected function resumenCita(Cita $cita): string
    {
        $fecha = Carbon::parse($cita->fecha)->locale('es')->isoFormat('dddd D [de] MMMM');
        $hora = Carbon::parse($cita->hora)->format('H:i');
        $doctor = $cita->user
            ? trim(($cita->user->nombre ?? '') . ' ' . ($cita->user->apellidoPat ?? ''))
            : '';
        $doctorLinea = $doctor !== '' ? "\n👨‍⚕️ Con: *{$doctor}*" : '';

        return "📅 *{$fecha}*\n🕐 *{$hora}*{$doctorLinea}";
    }

    public function procesarRespuesta($from, $body): string
    {
        $normalized = $this->phoneHash->fromTwilio($from);
        $hash = $this->phoneHash->hash($normalized);
        $respuesta = trim((string) $body);

        if (! $hash) {
            return 'No encontramos tu registro. Por favor contacta directamente a la clínica.';
        }

        $paciente = Paciente::where('telefono_search_hash', $hash)->first();
        if (! $paciente) {
            Log::warning('Paciente no encontrado para WhatsApp', ['telefono' => $normalized]);
            return 'No encontramos tu registro. Por favor contacta directamente a la clínica.';
        }

        // Preferir la última notificación accionable
        $mensaje = WhatsappMessage::query()
            ->where('paciente_id', $paciente->id)
            ->where('accionable', true)
            ->where('direccion', 'outbound')
            ->whereIn('estado', ['sent', 'queued'])
            ->orderByDesc('id')
            ->first();

        $cita = $mensaje?->cita;
        if (! $cita) {
            $cita = $paciente->citas()
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->where('fecha', '>=', now()->format('Y-m-d'))
                ->orderBy('fecha')
                ->orderBy('hora')
                ->first();
        }

        if (! $cita) {
            return 'No tienes citas programadas próximamente.';
        }

        WhatsappMessage::create([
            'cita_id' => $cita->id,
            'paciente_id' => $paciente->id,
            'clinica_id' => $cita->clinica_id,
            'tipo' => 'respuesta',
            'direccion' => 'inbound',
            'estado' => 'received',
            'telefono_to' => $normalized,
            'body' => $respuesta,
            'accionable' => false,
        ]);

        $fechaFormateada = Carbon::parse($cita->fecha)->format('d/m/Y');
        $horaFormateada = Carbon::parse($cita->hora)->format('H:i');

        switch ($respuesta) {
            case '1':
                $cita->update([
                    'confirmacion_whatsapp' => 'confirmada',
                    'estado' => 'confirmada',
                ]);
                if ($mensaje) {
                    $mensaje->update(['accionable' => false]);
                }
                return "✅ ¡Perfecto! Tu cita está confirmada para el *{$fechaFormateada}* a las *{$horaFormateada}*\n\nTe esperamos. ¡Gracias!";

            case '2':
                $cita->update(['confirmacion_whatsapp' => 'reagendar']);
                if ($mensaje) {
                    $mensaje->update(['accionable' => false]);
                }
                return "📅 Entendido. Nuestro equipo se pondrá en contacto contigo para reagendar tu cita.\n\nGracias por avisarnos.";

            case '3':
                $cita->update([
                    'confirmacion_whatsapp' => 'cancelar',
                    'estado' => 'cancelada',
                    'motivo_cancelacion' => 'Cancelada por el paciente vía WhatsApp',
                ]);
                if ($mensaje) {
                    $mensaje->update(['accionable' => false]);
                }
                return "❌ Tu cita del *{$fechaFormateada}* ha sido cancelada.\n\nSi deseas agendar nuevamente, contacta a la clínica.";

            default:
                if ($cita->estado === 'pendiente') {
                    return "Por favor responde con:\n*1* para confirmar ✅\n*2* para reagendar 📅\n*3* para cancelar ❌";
                }

                return "Por favor responde con:\n*2* para reagendar 📅\n*3* para cancelar ❌";
        }
    }
}
