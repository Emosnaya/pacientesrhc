<?php

namespace App\Http\Controllers;

use App\Models\ContactoComercialSolicitud;
use App\Models\Clinica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ContactoComercialController
 * 
 * Maneja las solicitudes de contacto comercial para clínicas con suscripción vencida.
 * Las clínicas empresariales no pueden renovar online, deben contactar al equipo de ventas.
 */
class ContactoComercialController extends Controller
{
    /**
     * POST /api/clinica-contacto-comercial
     * 
     * Crear solicitud de contacto comercial (para renovar suscripción de clínica)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre_contacto' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'cargo' => 'nullable|string|max:100',
            'tipo_solicitud' => 'required|in:renovacion,upgrade,informacion,otro',
            'mensaje' => 'nullable|string|max:2000',
            'plan_interes' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        if (!$clinicaId) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró una clínica asociada a tu cuenta.',
            ], 400);
        }

        $clinica = Clinica::find($clinicaId);

        if (!$clinica) {
            return response()->json([
                'success' => false,
                'message' => 'Clínica no encontrada.',
            ], 404);
        }

        // Verificar que no haya una solicitud pendiente reciente (últimas 24 horas)
        $solicitudReciente = ContactoComercialSolicitud::where('clinica_id', $clinicaId)
            ->where('estado', 'pendiente')
            ->where('created_at', '>=', now()->subDay())
            ->first();

        if ($solicitudReciente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una solicitud de contacto pendiente. Nuestro equipo te contactará pronto.',
                'solicitud_id' => $solicitudReciente->id,
                'creada_hace' => $solicitudReciente->created_at->diffForHumans(),
            ], 409);
        }

        // Crear la solicitud
        $solicitud = ContactoComercialSolicitud::create([
            'clinica_id' => $clinicaId,
            'user_id' => $user->id,
            'nombre_contacto' => $request->nombre_contacto,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'cargo' => $request->cargo,
            'tipo_solicitud' => $request->tipo_solicitud,
            'mensaje' => $request->mensaje,
            'plan_actual' => $clinica->plan,
            'plan_interes' => $request->plan_interes,
            'metadata' => [
                'clinica_nombre' => $clinica->nombre,
                'fecha_vencimiento' => $clinica->fecha_vencimiento?->format('Y-m-d'),
                'dias_vencido' => $clinica->fecha_vencimiento 
                    ? max(0, now()->diffInDays($clinica->fecha_vencimiento, false) * -1) 
                    : null,
                'tipo_clinica' => $clinica->tipo_clinica,
                'total_pacientes' => $clinica->pacientes()->count(),
                'total_sucursales' => $clinica->sucursales()->count(),
                'ip_solicitud' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        // Enviar notificación al equipo comercial
        try {
            $this->notificarEquipoComercial($solicitud, $clinica, $user);
        } catch (\Exception $e) {
            Log::warning('Error enviando notificación de contacto comercial', [
                'solicitud_id' => $solicitud->id,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Nueva solicitud de contacto comercial creada', [
            'solicitud_id' => $solicitud->id,
            'clinica_id' => $clinicaId,
            'tipo' => $request->tipo_solicitud,
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Solicitud enviada! Nuestro equipo comercial te contactará en las próximas 24 horas.',
            'solicitud' => [
                'id' => $solicitud->id,
                'estado' => $solicitud->estado,
                'creada' => $solicitud->created_at->format('Y-m-d H:i'),
            ],
        ], 201);
    }

    /**
     * GET /api/clinica-contacto-comercial/estado
     * 
     * Obtener estado de solicitudes pendientes de la clínica
     */
    public function estado(): JsonResponse
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        if (!$clinicaId) {
            return response()->json([
                'success' => true,
                'tiene_solicitud_pendiente' => false,
                'solicitudes' => [],
            ]);
        }

        $solicitudes = ContactoComercialSolicitud::where('clinica_id', $clinicaId)
            ->whereIn('estado', ['pendiente', 'contactado', 'en_negociacion'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'tipo_solicitud', 'estado', 'created_at', 'fecha_contacto']);

        return response()->json([
            'success' => true,
            'tiene_solicitud_pendiente' => $solicitudes->where('estado', 'pendiente')->isNotEmpty(),
            'solicitudes' => $solicitudes->map(fn ($s) => [
                'id' => $s->id,
                'tipo' => $s->tipo_solicitud,
                'estado' => $s->estado,
                'creada' => $s->created_at->format('Y-m-d H:i'),
                'creada_hace' => $s->created_at->diffForHumans(),
                'contactado' => $s->fecha_contacto?->format('Y-m-d H:i'),
            ]),
        ]);
    }

    /**
     * Notificar al equipo comercial de la nueva solicitud
     */
    protected function notificarEquipoComercial(ContactoComercialSolicitud $solicitud, Clinica $clinica, $user): void
    {
        $emailComercial = config('mail.commercial_team_email', 'ventas@lynkamed.mx');
        
        // Usar Mailable simple o notificación
        Mail::raw(
            $this->generarMensajeEmail($solicitud, $clinica, $user),
            function ($message) use ($emailComercial, $clinica, $solicitud) {
                $message->to($emailComercial)
                    ->subject("🔔 Solicitud de {$solicitud->tipo_solicitud}: {$clinica->nombre}");
            }
        );
    }

    /**
     * Generar contenido del email
     */
    protected function generarMensajeEmail(ContactoComercialSolicitud $solicitud, Clinica $clinica, $user): string
    {
        $metadata = $solicitud->metadata ?? [];
        
        return <<<EMAIL
        Nueva solicitud de contacto comercial
        ======================================
        
        Tipo de solicitud: {$solicitud->tipo_solicitud}
        Estado: {$solicitud->estado}
        
        DATOS DE LA CLÍNICA
        -------------------
        Nombre: {$clinica->nombre}
        Plan actual: {$solicitud->plan_actual}
        Plan de interés: {$solicitud->plan_interes}
        Fecha vencimiento: {$metadata['fecha_vencimiento']}
        Días vencido: {$metadata['dias_vencido']}
        
        DATOS DE CONTACTO
        -----------------
        Nombre: {$solicitud->nombre_contacto}
        Email: {$solicitud->email}
        Teléfono: {$solicitud->telefono}
        Cargo: {$solicitud->cargo}
        
        MÉTRICAS
        --------
        Total pacientes: {$metadata['total_pacientes']}
        Total sucursales: {$metadata['total_sucursales']}
        
        MENSAJE
        -------
        {$solicitud->mensaje}
        
        ---
        Esta solicitud fue generada desde el sistema LynkaMed.
        ID de solicitud: {$solicitud->id}
        EMAIL;
    }
}
