<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Models\Paciente;

class WhatsAppService
{
    protected $client;
    protected $from;
    protected $enabled;

    public function __construct()
    {
        $this->enabled = config('services.twilio.enabled', false);
        
        if ($this->enabled) {
            $this->client = new Client(
                config('services.twilio.sid'),
                config('services.twilio.auth_token')
            );
            $this->from = config('services.twilio.whatsapp_from');
        }
    }

    public function enviarRecordatorioCita($cita)
    {
        if (!$this->enabled) {
            Log::warning('WhatsApp deshabilitado en configuración');
            return false;
        }

        $paciente = $cita->paciente;
        $telefono = $this->formatearTelefono($paciente->telefono);
        
        if (!$telefono) {
            Log::warning("Paciente {$paciente->id} sin teléfono válido para WhatsApp", [
                'telefono_original' => $paciente->telefono
            ]);
            return false;
        }

        $mensaje = $this->generarMensajeRecordatorio($cita);
        
        try {
            $message = $this->client->messages->create(
                "whatsapp:{$telefono}",
                [
                    'from' => $this->from,
                    'body' => $mensaje
                ]
            );
            
            Log::info("WhatsApp enviado exitosamente", [
                'paciente' => $paciente->nombre . ' ' . $paciente->apellidoPat,
                'cita_id' => $cita->id,
                'telefono' => $telefono,
                'message_sid' => $message->sid
            ]);
            
            // Marcar que se envió recordatorio
            $cita->update([
                'recordatorio_enviado' => true,
                'recordatorio_enviado_at' => now()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error enviando WhatsApp", [
                'paciente_id' => $paciente->id,
                'cita_id' => $cita->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function generarMensajeRecordatorio($cita)
    {
        $paciente = $cita->paciente;
        $fecha = \Carbon\Carbon::parse($cita->fecha)->locale('es')->isoFormat('dddd D [de] MMMM');
        $hora = \Carbon\Carbon::parse($cita->hora)->format('H:i');
        $doctor = $cita->user->nombre . ' ' . $cita->user->apellidoPat;
        $clinica = $cita->clinica->nombre;

        return "🏥 *Recordatorio de Cita - {$clinica}*\n\n" .
               "Hola *{$paciente->nombre}*,\n\n" .
               "Te recordamos tu cita:\n" .
               "📅 *{$fecha}*\n" .
               "🕐 *{$hora}*\n" .
               "👨‍⚕️ Con: *{$doctor}*\n\n" .
               "Por favor confirma tu asistencia respondiendo:\n" .
               "*1* - Confirmo mi asistencia ✅\n" .
               "*2* - Necesito reagendar 📅\n" .
               "*3* - Cancelar cita ❌\n\n" .
               "_Responde con el número correspondiente._";
    }

    protected function formatearTelefono($telefono)
    {
        if (!$telefono) {
            return null;
        }

        // Eliminar caracteres especiales
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        
        // Si ya empieza con 52 (México) y tiene 12 dígitos
        if (strlen($telefono) === 12 && substr($telefono, 0, 2) === '52') {
            return '+' . $telefono;
        }
        
        // Si tiene 10 dígitos, agregar +52 (México)
        if (strlen($telefono) === 10) {
            return '+52' . $telefono;
        }
        
        // Si tiene 11 dígitos y empieza con 1 (podría ser 1 + 10 dígitos)
        if (strlen($telefono) === 11 && substr($telefono, 0, 1) === '1') {
            return '+52' . substr($telefono, 1);
        }
        
        return null;
    }

    public function procesarRespuesta($from, $body)
    {
        $telefono = str_replace('whatsapp:', '', $from);
        $telefono = str_replace('+', '', $telefono);
        $respuesta = trim($body);
        
        // Buscar paciente por teléfono (comparar últimos 10 dígitos)
        $paciente = Paciente::whereRaw(
            "REPLACE(REPLACE(REPLACE(REPLACE(telefono, ' ', ''), '-', ''), '+', ''), '(', '') LIKE ?",
            ['%' . substr($telefono, -10) . '%']
        )->first();
        
        if (!$paciente) {
            Log::warning('Paciente no encontrado para WhatsApp', ['telefono' => $telefono]);
            return "No encontramos tu registro. Por favor contacta directamente a la clínica.";
        }
        
        // Buscar última cita pendiente o confirmada
        $cita = $paciente->citas()
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where('fecha', '>=', now()->format('Y-m-d'))
            ->orderBy('fecha')
            ->orderBy('hora')
            ->first();
            
        if (!$cita) {
            return "No tienes citas programadas próximamente.";
        }
        
        $fechaFormateada = \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y');
        $horaFormateada = \Carbon\Carbon::parse($cita->hora)->format('H:i');
        
        switch ($respuesta) {
            case '1':
                $cita->update([
                    'confirmacion_whatsapp' => 'confirmada',
                    'estado' => 'confirmada'
                ]);
                Log::info('Cita confirmada vía WhatsApp', [
                    'paciente_id' => $paciente->id,
                    'cita_id' => $cita->id
                ]);
                return "✅ ¡Perfecto! Tu cita está confirmada para el *{$fechaFormateada}* a las *{$horaFormateada}*\n\n" .
                       "Te esperamos. ¡Gracias! 😊";
                
            case '2':
                $cita->update(['confirmacion_whatsapp' => 'reagendar']);
                Log::info('Cita marcada para reagendar vía WhatsApp', [
                    'paciente_id' => $paciente->id,
                    'cita_id' => $cita->id
                ]);
                return "📅 Entendido. Nuestro equipo se pondrá en contacto contigo para reagendar tu cita.\n\n" .
                       "Gracias por avisarnos.";
                
            case '3':
                $cita->update([
                    'confirmacion_whatsapp' => 'cancelar',
                    'estado' => 'cancelada'
                ]);
                Log::info('Cita cancelada vía WhatsApp', [
                    'paciente_id' => $paciente->id,
                    'cita_id' => $cita->id
                ]);
                return "❌ Tu cita del *{$fechaFormateada}* ha sido cancelada.\n\n" .
                       "Si deseas agendar nuevamente, contacta a la clínica.";
                
            default:
                return "Por favor responde con:\n" .
                       "*1* para confirmar ✅\n" .
                       "*2* para reagendar 📅\n" .
                       "*3* para cancelar ❌";
        }
    }
}
