<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EnsureInternalConsultorioUnlocked
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('services.internal_consultorio.secret');
        if (empty($secret)) {
            return $next($request);
        }

        $token = $request->header('X-Internal-Consultorio-Token');
        if (! $token || ! Cache::get($this->cacheKey($token))) {
            return response()->json([
                'message' => 'Acceso no autorizado a provisionamiento interno de consultorio.',
            ], 403);
        }

        return $next($request);
    }

    public static function cacheKey(string $plainToken): string
    {
        return 'internal_consultorio_unlock:'.hash('sha256', $plainToken);
    }
}
