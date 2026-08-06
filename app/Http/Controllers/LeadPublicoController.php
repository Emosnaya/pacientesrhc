<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Paciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Captura de prospectos desde superficies públicas (pasaporte de salud escaneado
 * por un profesional sin suscripción, landing, etc.).
 */
class LeadPublicoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|max:191',
            'telefono' => 'nullable|string|max:40',
            'establecimiento' => 'nullable|string|max:191',
            'especialidad' => 'nullable|string|max:120',
            'mensaje' => 'nullable|string|max:2000',
            'paciente_uuid' => 'nullable|uuid',
            'origen' => 'nullable|string|max:40',
        ]);

        $email = strtolower(trim($validated['email']));
        $pacienteUuid = $validated['paciente_uuid'] ?? null;

        // Evitar duplicados por email+origen en la última hora (doble envío del formulario).
        $reciente = Lead::query()
            ->where('email', $email)
            ->where('created_at', '>=', now()->subHour())
            ->first();

        if ($reciente) {
            return response()->json([
                'success' => true,
                'message' => 'Ya recibimos tus datos. Nuestro equipo te contactará muy pronto.',
                'lead_id' => $reciente->id,
            ], 200);
        }

        $lead = Lead::create([
            'origen' => $validated['origen'] ?? Lead::ORIGEN_PASAPORTE,
            'nombre' => $validated['nombre'],
            'email' => $email,
            'telefono' => $validated['telefono'] ?? null,
            'establecimiento' => $validated['establecimiento'] ?? null,
            'especialidad' => $validated['especialidad'] ?? null,
            'mensaje' => $validated['mensaje'] ?? null,
            'paciente_uuid' => $pacienteUuid,
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'pasaporte_valido' => $pacienteUuid
                    ? Paciente::where('uuid_publico', $pacienteUuid)->exists()
                    : null,
            ],
        ]);

        $this->notificarEquipoComercial($lead);

        return response()->json([
            'success' => true,
            'message' => 'Gracias. Nuestro equipo te contactará para darte acceso a LynkaMed.',
            'lead_id' => $lead->id,
        ], 201);
    }

    private function notificarEquipoComercial(Lead $lead): void
    {
        $destino = config('mail.commercial_team_email', 'ventas@lynkamed.mx');

        $cuerpo = "Nuevo lead desde pasaporte de salud\n"
            ."==================================\n\n"
            .'Origen: '.$lead->origen."\n"
            .'Nombre: '.$lead->nombre."\n"
            .'Email: '.$lead->email."\n"
            .'Teléfono: '.($lead->telefono ?: 'No proporcionado')."\n"
            .'Establecimiento: '.($lead->establecimiento ?: 'No proporcionado')."\n"
            .'Especialidad: '.($lead->especialidad ?: 'No proporcionada')."\n"
            .'Pasaporte escaneado: '.($lead->paciente_uuid ?: 'N/A')."\n\n"
            .'Mensaje: '.($lead->mensaje ?: '—')."\n\n"
            .'ID de lead: '.$lead->id."\n";

        try {
            Mail::raw($cuerpo, function ($message) use ($destino, $lead) {
                $message->to($destino)
                    ->subject('Nuevo lead LynkaMed · '.Str::limit($lead->establecimiento ?: $lead->nombre, 60));
            });
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar lead al equipo comercial', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
