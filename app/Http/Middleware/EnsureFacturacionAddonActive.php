<?php

namespace App\Http\Middleware;

use App\Models\Clinica;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureFacturacionAddonActive
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $clinicaId = $user->clinica_efectiva_id;
        if (! $clinicaId) {
            return response()->json([
                'success' => false,
                'message' => 'Facturación no disponible: sin clínica efectiva.',
            ], 403);
        }

        $clinica = Clinica::query()->find($clinicaId);
        if (! $clinica || ! $clinica->facturacion_addon_activo) {
            return response()->json([
                'success' => false,
                'message' => 'El módulo de facturación electrónica requiere un plan complementario activo para este espacio de trabajo.',
                'requires_facturacion_addon' => true,
            ], 403);
        }

        return $next($request);
    }
}
