<?php

namespace App\Http\Middleware;

use App\Models\Clinica;
use App\Models\SuscripcionFacturas;
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
                'message' => 'Facturación no disponible: sin espacio de trabajo.',
            ], 403);
        }

        $clinica = Clinica::query()->find($clinicaId);
        if (! $clinica) {
            return response()->json([
                'success' => false,
                'message' => 'Facturación no disponible: espacio no encontrado.',
            ], 403);
        }

        if (! SuscripcionFacturas::usuarioTieneModuloActivo($user, $clinica)) {
            $message = SuscripcionFacturas::facturacionEsPorUsuario($clinica)
                ? 'Contrata tu plan de facturación personal en Perfil → Facturación CFDI.'
                : 'El módulo de facturación requiere un plan activo para esta clínica.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'requires_facturacion_addon' => true,
            ], 403);
        }

        return $next($request);
    }
}
