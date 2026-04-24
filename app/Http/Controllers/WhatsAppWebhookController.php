<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Maneja los mensajes entrantes de WhatsApp vía Twilio
     */
    public function handle(Request $request)
    {
        $from = $request->input('From');
        $body = $request->input('Body');
        $messageId = $request->input('MessageSid');
        
        Log::info('WhatsApp mensaje recibido', [
            'from' => $from,
            'body' => $body,
            'message_id' => $messageId,
            'timestamp' => now()
        ]);
        
        $whatsapp = new WhatsAppService();
        $respuesta = $whatsapp->procesarRespuesta($from, $body);
        
        // Twilio espera respuesta en formato TwiML
        $twiml = new \Twilio\TwiML\MessagingResponse();
        $twiml->message($respuesta);
        
        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Maneja el status callback de Twilio (opcional)
     */
    public function status(Request $request)
    {
        $messageSid = $request->input('MessageSid');
        $messageStatus = $request->input('MessageStatus');
        $errorCode = $request->input('ErrorCode');
        
        Log::info('WhatsApp status callback', [
            'message_sid' => $messageSid,
            'status' => $messageStatus,
            'error_code' => $errorCode
        ]);
        
        return response()->json(['success' => true]);
    }
}
