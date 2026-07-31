<?php

namespace App\Jobs;

use App\Models\Cita;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCitaWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $citaId,
        public string $tipo = 'confirmacion'
    ) {
    }

    public function handle(WhatsAppService $whatsApp): void
    {
        $cita = Cita::with(['paciente', 'user', 'clinica'])->find($this->citaId);
        if (! $cita) {
            return;
        }

        $result = $whatsApp->enviarNotificacionCita($cita, $this->tipo);

        if (! ($result['ok'] ?? false)) {
            Log::info('WhatsApp no enviado; se mantiene fallback de correo', [
                'cita_id' => $this->citaId,
                'tipo' => $this->tipo,
                'error' => $result['error'] ?? null,
            ]);
        }
    }
}
