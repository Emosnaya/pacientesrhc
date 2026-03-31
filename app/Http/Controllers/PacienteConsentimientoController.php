<?php

namespace App\Http\Controllers;

use App\Services\PacienteConsentimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints públicos (sin sesión) para aceptar aviso LFPDPPP / términos vía enlace del correo.
 */
class PacienteConsentimientoController extends Controller
{
    public function __construct(
        protected PacienteConsentimientoService $consentimientoService
    ) {}

    public function verificar(Request $request): JsonResponse
    {
        $token = (string) $request->query('token', '');
        $token = urldecode($token);
        if (strlen($token) < 32) {
            return response()->json(['valid' => false, 'message' => 'Token inválido'], 422);
        }

        $paciente = $this->consentimientoService->verificarToken($token);
        if (! $paciente) {
            return response()->json(['valid' => false, 'message' => 'Enlace inválido o vencido'], 404);
        }

        $clinica = $paciente->clinica;

        return response()->json([
            'valid' => true,
            'ya_aceptado' => (bool) $paciente->aviso_privacidad_aceptado_at,
            'clinica_nombre' => $clinica?->nombre,
            'invitacion_contexto' => $paciente->consentimiento_invitacion_contexto ?: 'registro',
            'version_aviso_config' => config('legal.version_aviso_privacidad'),
            'version_terminos_config' => config('legal.version_terminos'),
        ]);
    }

    public function aceptar(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|min:32',
        ]);

        $result = $this->consentimientoService->aceptarConToken($request->input('token'));

        $status = $result['ok'] ? 200 : 422;

        return response()->json($result, $status);
    }
}
