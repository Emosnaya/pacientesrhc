<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;

/**
 * Clase base para todos los correos de LynkaMed.
 *
 * Añade headers que mejoran la entregabilidad en Outlook/Hotmail/Gmail:
 *  - List-Unsubscribe: requerido por Microsoft para no marcar como spam
 *  - X-Mailer: identifica el origen del envío
 *  - Precedence: indica que es correo transaccional, no bulk
 *  - X-Priority / Importance: prioridad normal (evita filtros)
 *  - Auto-Submitted: previene respuestas automáticas / loops
 */
abstract class BaseMail extends Mailable
{
    public function headers(): Headers
    {
        $appName    = config('app.name', 'LynkaMed');
        $fromEmail  = config('mail.from.address', 'contacto@lynkamed.mx');
        $appUrl     = rtrim(config('app.url', 'https://lynkamed.mx'), '/');

        return new Headers(
            text: [
                // Microsoft SmartScreen / Outlook requiere esto para confiar en el remitente
                'List-Unsubscribe'       => "<mailto:{$fromEmail}?subject=unsubscribe>",
                'List-Unsubscribe-Post'  => 'List-Unsubscribe=One-Click',

                // Identifica el sistema de envío (ayuda a Outlook a no marcar como phishing)
                'X-Mailer'               => "{$appName} Mailer 1.0",

                // Transaccional = nunca bulk
                'Precedence'             => 'transactional',

                // Prioridad normal — evita filtros de spam agresivos
                'X-Priority'             => '3 (Normal)',
                'Importance'             => 'Normal',

                // Evita loops de respuesta automática
                'Auto-Submitted'         => 'auto-generated',

                // Identifica el origen de la aplicación
                'X-Application'          => $appName,
                'X-Application-URL'      => $appUrl,
            ],
        );
    }
}
