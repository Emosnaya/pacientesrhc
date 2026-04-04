<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SoporteController extends Controller
{
    /**
     * Recibir ticket de soporte
     */
    public function crearTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categoria' => 'required|string|in:duda,error,sugerencia,otro',
            'mensaje' => 'required|string|min:10|max:2000',
            'asunto' => 'nullable|string|max:255',
            'usuario_nombre' => 'nullable|string|max:255',
            'usuario_email' => 'nullable|email|max:255',
            'clinica_nombre' => 'nullable|string|max:255',
            'clinica_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Log del ticket para revisión
        Log::channel('soporte')->info('Nuevo ticket de soporte', [
            'categoria' => $data['categoria'],
            'asunto' => $data['asunto'] ?? 'Sin asunto',
            'mensaje' => $data['mensaje'],
            'usuario' => $data['usuario_nombre'] ?? 'Anónimo',
            'email' => $data['usuario_email'] ?? 'No proporcionado',
            'clinica' => $data['clinica_nombre'] ?? 'No especificada',
            'clinica_id' => $data['clinica_id'] ?? null,
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Enviar email de notificación al equipo de soporte
        try {
            $this->enviarNotificacionSoporte($data);
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de soporte', [
                'error' => $e->getMessage()
            ]);
            // No falla aunque el email no se envíe
        }

        return response()->json([
            'success' => true,
            'message' => 'Tu solicitud ha sido recibida. Te contactaremos pronto.'
        ]);
    }

    /**
     * Enviar notificación por email al equipo de soporte
     */
    private function enviarNotificacionSoporte(array $data): void
    {
        $categoriaLabels = [
            'duda' => '❓ Duda',
            'error' => '🐛 Error reportado',
            'sugerencia' => '💡 Sugerencia',
            'otro' => '📝 Otro',
        ];

        $asunto = "[LynkaMed Soporte] " . ($categoriaLabels[$data['categoria']] ?? 'Ticket') . " - " . ($data['asunto'] ?? 'Nueva solicitud');

        $contenido = "
        <h2>Nuevo ticket de soporte</h2>
        <p><strong>Categoría:</strong> {$categoriaLabels[$data['categoria']]}</p>
        <p><strong>Asunto:</strong> " . ($data['asunto'] ?? 'No especificado') . "</p>
        <p><strong>Usuario:</strong> " . ($data['usuario_nombre'] ?? 'No especificado') . "</p>
        <p><strong>Email:</strong> " . ($data['usuario_email'] ?? 'No proporcionado') . "</p>
        <p><strong>Clínica:</strong> " . ($data['clinica_nombre'] ?? 'No especificada') . " (ID: " . ($data['clinica_id'] ?? '-') . ")</p>
        <hr>
        <p><strong>Mensaje:</strong></p>
        <p>" . nl2br(e($data['mensaje'])) . "</p>
        <hr>
        <p><small>Recibido: " . now()->format('d/m/Y H:i:s') . "</small></p>
        ";

        Mail::html($contenido, function ($message) use ($asunto, $data) {
            $message->to(config('mail.soporte_email', 'soporte@lynkamed.com'))
                    ->subject($asunto);
            
            // Si el usuario proporcionó email, agregar como reply-to
            if (!empty($data['usuario_email'])) {
                $message->replyTo($data['usuario_email'], $data['usuario_nombre'] ?? '');
            }
        });
    }
}
