<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EnsureClinicRegistrationUnlocked
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('services.clinic_registration.secret');
        if (empty($secret)) {
            return $next($request);
        }

        $token = $request->header('X-Clinic-Registration-Token');
        if (! $token || ! Cache::get($this->cacheKey($token))) {
            return response()->json([
                'message' => 'Acceso no autorizado al registro de clínicas.',
            ], 403);
        }

        return $next($request);
    }

    public static function cacheKey(string $plainToken): string
    {
        return 'clinic_registration_unlock:'.hash('sha256', $plainToken);
    }
}
