<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureClinicRegistrationUnlocked;
use App\Http\Middleware\EnsureInternalConsultorioUnlocked;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Desbloqueo por clave de operador (secreto solo en servidor) para rutas públicas sensibles.
 */
class InternalAccessController extends Controller
{
    public function unlockClinicRegistration(Request $request): JsonResponse
    {
        $request->validate(['access_key' => 'required|string']);

        $secret = config('services.clinic_registration.secret');
        if (empty($secret)) {
            if (app()->environment('production')) {
                return response()->json([
                    'message' => 'Registro de clínica no configurado en el servidor.',
                ], 503);
            }

            return response()->json([
                'token' => 'dev-open',
                'expires_in' => 86400,
                'message' => 'CLINIC_REGISTRATION_SECRET no está definido: registro abierto (solo desarrollo).',
            ]);
        }

        if (! hash_equals($secret, $request->access_key)) {
            return response()->json(['message' => 'Clave incorrecta.'], 403);
        }

        $plain = Str::random(64);
        Cache::put(EnsureClinicRegistrationUnlocked::cacheKey($plain), true, now()->addHours(8));

        return response()->json([
            'token' => $plain,
            'expires_in' => 28800,
        ]);
    }

    public function unlockInternalConsultorio(Request $request): JsonResponse
    {
        $request->validate(['access_key' => 'required|string']);

        $secret = config('services.internal_consultorio.secret');
        if (empty($secret)) {
            if (app()->environment('production')) {
                return response()->json([
                    'message' => 'Provisionamiento interno no configurado en el servidor.',
                ], 503);
            }

            return response()->json([
                'token' => 'dev-open',
                'expires_in' => 86400,
                'message' => 'INTERNAL_CONSULTORIO_SETUP_SECRET no está definido: provisionamiento abierto (solo desarrollo).',
            ]);
        }

        if (! hash_equals($secret, $request->access_key)) {
            return response()->json(['message' => 'Clave incorrecta.'], 403);
        }

        $plain = Str::random(64);
        Cache::put(EnsureInternalConsultorioUnlocked::cacheKey($plain), true, now()->addHours(8));

        return response()->json([
            'token' => $plain,
            'expires_in' => 28800,
        ]);
    }
}
