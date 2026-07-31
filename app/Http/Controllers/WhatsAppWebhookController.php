<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Maneja los mensajes entrantes de WhatsApp vía Twilio
     */
    public function handle(Request $request)
    {
        if (! $this->validateTwilioSignature($request)) {
            Log::warning('WhatsApp webhook con firma inválida', [
                'ip' => $request->ip(),
            ]);

            return response('Forbidden', 403);
        }

        $from = $request->input('From');
        $body = $request->input('Body');
        $messageId = $request->input('MessageSid');

        Log::info('WhatsApp mensaje recibido', [
            'from' => $from,
            'body' => $body,
            'message_id' => $messageId,
            'timestamp' => now(),
        ]);

        $whatsapp = new WhatsAppService();
        $respuesta = $whatsapp->procesarRespuesta($from, $body);

        $twiml = new \Twilio\TwiML\MessagingResponse();
        $twiml->message($respuesta);

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Maneja el status callback de Twilio
     */
    public function status(Request $request)
    {
        if (! $this->validateTwilioSignature($request)) {
            return response('Forbidden', 403);
        }

        $messageSid = $request->input('MessageSid');
        $messageStatus = $request->input('MessageStatus');
        $errorCode = $request->input('ErrorCode');

        Log::info('WhatsApp status callback', [
            'message_sid' => $messageSid,
            'status' => $messageStatus,
            'error_code' => $errorCode,
        ]);

        if ($messageSid) {
            $estado = match ($messageStatus) {
                'delivered', 'read', 'sent' => 'sent',
                'failed', 'undelivered' => 'failed',
                default => null,
            };

            if ($estado) {
                \App\Models\WhatsappMessage::where('twilio_sid', $messageSid)
                    ->update([
                        'estado' => $estado,
                        'error' => $errorCode ? (string) $errorCode : null,
                    ]);
            }
        }

        return response()->json(['success' => true]);
    }

    protected function validateTwilioSignature(Request $request): bool
    {
        $authToken = config('services.twilio.auth_token');
        $signature = $request->header('X-Twilio-Signature');

        // En local/dev sin token o sin header, permitir si WhatsApp está deshabilitado
        if (! config('services.twilio.enabled') && ! config('services.twilio.whatsapp_enabled')) {
            return true;
        }

        if (! $authToken || ! $signature) {
            return false;
        }

        try {
            $validator = new \Twilio\Security\RequestValidator($authToken);
            $url = $request->fullUrl();
            $params = $request->post();

            return $validator->validate($signature, $url, $params);
        } catch (\Throwable $e) {
            Log::error('Error validando firma Twilio', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
