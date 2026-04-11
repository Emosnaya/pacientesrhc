<?php

namespace App\Http\Controllers;

use App\Models\SoporteTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SoporteController extends Controller
{
    /**
     * Recibir ticket de soporte - Guarda en BD y notifica por email
     */
    public function crearTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categoria' => 'required|string|in:duda,error,sugerencia,otro',
            'mensaje' => 'required|string|min:10|max:5000',
            'asunto' => 'nullable|string|max:255',
            'clinica_id' => 'nullable|integer|exists:clinicas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $user = Auth::user();

        // Obtener nombre de clínica
        $clinicaNombre = null;
        if (!empty($data['clinica_id'])) {
            $clinica = \App\Models\Clinica::find($data['clinica_id']);
            $clinicaNombre = $clinica?->nombre;
        }

        // Crear ticket en BD
        $ticket = SoporteTicket::create([
            'categoria' => $data['categoria'],
            'asunto' => $data['asunto'] ?? 'Sin asunto',
            'mensaje' => $data['mensaje'],
            'user_id' => $user->id,
            'clinica_id' => $data['clinica_id'] ?? null,
            'usuario_nombre' => $user->name,
            'usuario_email' => $user->email,
            'clinica_nombre' => $clinicaNombre,
            'status' => 'nuevo',
            'prioridad' => 'media',
        ]);

        // Log del ticket
        Log::channel('soporte')->info('Nuevo ticket de soporte', [
            'ticket_id' => $ticket->id,
            'numero_ticket' => $ticket->numero_ticket,
            'categoria' => $data['categoria'],
            'asunto' => $data['asunto'] ?? 'Sin asunto',
            'usuario' => $user->name,
            'email' => $user->email,
            'clinica' => $clinicaNombre ?? 'No especificada',
            'timestamp' => now()->toIso8601String(),
        ]);

        // Enviar email de notificación al equipo de soporte
        try {
            $this->enviarNotificacionSoporte($ticket);
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de soporte', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
            // No falla aunque el email no se envíe
        }

        return response()->json([
            'success' => true,
            'message' => 'Tu solicitud ha sido recibida. Te contactaremos pronto.',
            'ticket' => [
                'id' => $ticket->id,
                'numero_ticket' => $ticket->numero_ticket,
            ]
        ]);
    }

    /**
     * Obtener tickets del usuario autenticado
     */
    public function misTickets()
    {
        $user = Auth::user();
        
        $tickets = SoporteTicket::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
        ]);
    }

    /**
     * Ver detalle de un ticket propio
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $ticket = SoporteTicket::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
        ]);
    }

    /**
     * Enviar notificación por email al equipo de soporte
     */
    private function enviarNotificacionSoporte(SoporteTicket $ticket): void
    {
        $categoriaLabels = [
            'duda' => '❓ Duda',
            'error' => '🐛 Error reportado',
            'sugerencia' => '💡 Sugerencia',
            'otro' => '📝 Otro',
        ];

        $asunto = "[LynkaMed #{$ticket->numero_ticket}] " . ($categoriaLabels[$ticket->categoria] ?? 'Ticket') . " - " . $ticket->asunto;

        $contenido = "
        <h2>Nuevo ticket de soporte #{$ticket->numero_ticket}</h2>
        <p><strong>Categoría:</strong> {$categoriaLabels[$ticket->categoria]}</p>
        <p><strong>Asunto:</strong> {$ticket->asunto}</p>
        <p><strong>Usuario:</strong> {$ticket->usuario_nombre}</p>
        <p><strong>Email:</strong> {$ticket->usuario_email}</p>
        <p><strong>Clínica:</strong> " . ($ticket->clinica_nombre ?? 'No especificada') . " (ID: " . ($ticket->clinica_id ?? '-') . ")</p>
        <hr>
        <p><strong>Mensaje:</strong></p>
        <p>" . nl2br(e($ticket->mensaje)) . "</p>
        <hr>
        <p><small>Ticket: {$ticket->numero_ticket} | Recibido: " . now()->format('d/m/Y H:i:s') . "</small></p>
        <p><a href='" . config('app.admin_url', 'https://admin.lynkamed.com') . "/soporte/{$ticket->id}'>Ver en panel de administración</a></p>
        ";

        Mail::html($contenido, function ($message) use ($asunto, $ticket) {
            $message->to(config('mail.soporte_email', 'soporte@lynkamed.com'))
                    ->subject($asunto);
            
            // Agregar reply-to del usuario
            if ($ticket->usuario_email) {
                $message->replyTo($ticket->usuario_email, $ticket->usuario_nombre ?? '');
            }
        });
    }
}
