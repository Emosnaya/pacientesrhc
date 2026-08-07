<?php

namespace App\Services;

use App\Models\PacienteDeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    private const EXPO_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Envía push a todos los dispositivos registrados del paciente.
     */
    public function sendToPaciente(
        int $pacienteId,
        string $title,
        ?string $body = null,
        ?array $data = null
    ): void {
        $tokens = PacienteDeviceToken::query()
            ->where('paciente_id', $pacienteId)
            ->pluck('token')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($tokens)) {
            return;
        }

        $messages = array_map(function (string $token) use ($title, $body, $data) {
            return [
                'to' => $token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body ?: '',
                'data' => $data ?: new \stdClass(),
                'priority' => 'high',
                'channelId' => 'default',
            ];
        }, $tokens);

        try {
            // Expo acepta hasta 100 mensajes por request
            foreach (array_chunk($messages, 100) as $chunk) {
                $response = Http::timeout(12)
                    ->acceptJson()
                    ->post(self::EXPO_URL, $chunk);

                if (! $response->successful()) {
                    Log::warning('Expo push falló', [
                        'paciente_id' => $pacienteId,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    continue;
                }

                $tickets = $response->json('data') ?? [];
                $invalid = [];
                foreach ($tickets as $i => $ticket) {
                    if (($ticket['status'] ?? '') === 'error') {
                        $err = $ticket['details']['error'] ?? ($ticket['message'] ?? '');
                        if (in_array($err, ['DeviceNotRegistered', 'InvalidCredentials'], true)) {
                            $invalid[] = $chunk[$i]['to'] ?? null;
                        }
                    }
                }
                $invalid = array_filter($invalid);
                if ($invalid) {
                    PacienteDeviceToken::whereIn('token', $invalid)->delete();
                }
            }

            PacienteDeviceToken::where('paciente_id', $pacienteId)
                ->whereIn('token', $tokens)
                ->update(['last_used_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Expo push excepción: ' . $e->getMessage(), [
                'paciente_id' => $pacienteId,
            ]);
        }
    }
}
