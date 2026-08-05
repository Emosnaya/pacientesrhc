<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WhatsAppDiagnoseCommand extends Command
{
    protected $signature = 'whatsapp:diagnose';

    protected $description = 'Verifica la configuración de Twilio WhatsApp (sin exponer secretos)';

    public function handle(): int
    {
        $sid = (string) config('services.twilio.sid');
        $token = (string) config('services.twilio.auth_token');
        $from = (string) config('services.twilio.whatsapp_from');
        $enabled = (bool) (
            config('services.twilio.enabled')
            || config('services.twilio.whatsapp_enabled')
        );
        $callback = (string) config('services.twilio.status_callback');
        $appUrl = (string) config('app.url');

        $this->info('=== Twilio WhatsApp diagnose ===');
        $this->line('WHATSAPP_ENABLED: ' . ($enabled ? 'true' : 'false'));
        $this->line('TWILIO_SID: ' . ($sid ? (substr($sid, 0, 4) . '…' . substr($sid, -4) . ' (' . strlen($sid) . ' chars)') : 'MISSING'));
        $this->line('TWILIO_AUTH_TOKEN: ' . ($token ? ('set (' . strlen($token) . ' chars)') : 'MISSING'));
        $this->line('TWILIO_WHATSAPP_FROM: ' . ($from ?: 'MISSING'));
        $this->line('APP_URL: ' . ($appUrl ?: 'MISSING'));
        $this->line('status_callback: ' . ($callback ?: 'MISSING'));

        $isSandbox = $from === 'whatsapp:+14155238886';
        if ($isSandbox) {
            $this->warn('FROM es el sandbox de Twilio. En prod usa tu número WhatsApp Business (whatsapp:+52…).');
        }

        if ($enabled && $sid && $token && $from && ! $isSandbox) {
            $this->info('Config lista para producción (revisa webhooks en Twilio Console).');
        } elseif ($enabled && $isSandbox) {
            $this->warn('Habilitado, pero aún en sandbox — solo números unidos al sandbox recibirán mensajes.');
        } else {
            $this->warn('WhatsApp no está listo: revisa WHATSAPP_ENABLED / SID / TOKEN / FROM.');
        }

        $this->newLine();
        $this->line('Webhooks esperados (PROD):');
        $base = rtrim($appUrl ?: 'https://api.lynkamed.mx', '/');
        $this->line("  Incoming → POST {$base}/api/whatsapp/webhook");
        $this->line("  Status   → POST {$base}/api/whatsapp/status");

        return self::SUCCESS;
    }
}
