<?php

namespace App\Http\Controllers;

use App\Mail\PacientePortalOtpMail;
use App\Models\PacientePortalOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PacientePortalAuthController extends Controller
{
    private const ABILITY_SET_PASSWORD = 'paciente-portal:set-password';

    /**
     * Solicitar OTP al correo (solo cuentas portal con paciente_id).
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $v->errors()], 422);
        }

        $email = strtolower(trim($request->email));
        $user = User::query()->where('email', $email)->whereNotNull('paciente_id')->first();

        if (! $user) {
            return response()->json([
                'message' => 'Si existe una cuenta asociada a este correo, recibirás un código de verificación.',
            ]);
        }

        PacientePortalOtp::query()
            ->where('paciente_id', $user->paciente_id)
            ->whereNull('consumed_at')
            ->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PacientePortalOtp::create([
            'paciente_id' => $user->paciente_id,
            'otp_hash' => PacientePortalOtp::hashCode($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        try {
            Mail::to($email)->send(new PacientePortalOtpMail(
                nombre: trim(($user->nombre ?? '').' '.($user->apellidoPat ?? '')),
                code: $code
            ));
        } catch (\Throwable $e) {
            \Log::error('PacientePortal OTP mail failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'No se pudo enviar el correo. Intenta más tarde.',
            ], 503);
        }

        return response()->json([
            'message' => 'Si existe una cuenta asociada a este correo, recibirás un código de verificación.',
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $v->errors()], 422);
        }

        $email = strtolower(trim($request->email));
        $user = User::query()->where('email', $email)->whereNotNull('paciente_id')->first();

        if (! $user) {
            return response()->json(['message' => 'Código incorrecto o vencido.'], 422);
        }

        $otp = PacientePortalOtp::query()
            ->where('paciente_id', $user->paciente_id)
            ->whereNull('consumed_at')
            ->orderByDesc('id')
            ->first();

        if (! $otp || ! $otp->isUsable() || ! $otp->matches($request->code)) {
            return response()->json(['message' => 'Código incorrecto o vencido.'], 422);
        }

        $otp->markConsumed();

        $user->tokens()->delete();
        $plain = $user->createToken('portal-setup', [self::ABILITY_SET_PASSWORD])->plainTextToken;

        return response()->json([
            'token' => $plain,
            'requires_password' => $user->password_set_at === null,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'paciente_id' => $user->paciente_id,
            ],
        ]);
    }

    public function setPassword(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->paciente_id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if (! $user->tokenCan(self::ABILITY_SET_PASSWORD)) {
            return response()->json(['message' => 'Token inválido para este paso.'], 403);
        }

        $v = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $v->errors()], 422);
        }

        try {
            $plain = DB::transaction(function () use ($user, $request) {
                $user->password = Hash::make($request->password);
                $user->password_set_at = now();
                $user->save();

                $user->currentAccessToken()->delete();

                return $user->createToken('auth_token')->plainTextToken;
            });
        } catch (\Throwable $e) {
            Log::error('PacientePortal setPassword: transacción fallida (contraseña/token no aplicados)', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'No se pudo completar el registro de contraseña. Intenta de nuevo o solicita un nuevo código desde el portal.',
            ], 500);
        }

        // JSON desde fila SQL: evita accessors/appends del modelo User y relaciones.
        return response()->json([
            'token' => $plain,
            'user' => $this->portalUserJsonArrayFromDb($user->id),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $v->errors()], 422);
        }

        $credentials = [
            'email' => strtolower(trim($request->email)),
            'password' => $request->password,
        ];

        $found = User::query()->where('email', $credentials['email'])->first();
        if ($found && $found->paciente_id && ! $found->password_set_at) {
            return response()->json([
                'message' => 'Completa el acceso al portal con el código que enviamos a tu correo.',
                'requires_portal_setup' => true,
            ], 422);
        }

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 422);
        }

        $user = Auth::user();
        if (! $user->paciente_id) {
            Auth::logout();

            return response()->json([
                'message' => 'Esta cuenta no es del portal de paciente.',
            ], 403);
        }

        $user->tokens()->delete();
        $plain = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $plain,
            'user' => $this->portalUserJsonArrayFromDb($user->id),
        ]);
    }

    /**
     * Payload estable para el front del portal (lectura directa en users, sin Eloquent).
     */
    private function portalUserJsonArrayFromDb(int $userId): array
    {
        $row = DB::table('users')->where('id', $userId)->first();

        if (! $row) {
            Log::error('PacientePortal: fila users no encontrada al armar JSON', ['user_id' => $userId]);

            return [
                'id' => $userId,
                'email' => '',
                'nombre' => '',
                'apellidoPat' => '',
                'apellidoMat' => '',
                'paciente_id' => null,
                'rol' => 'paciente',
                'password_set_at' => null,
                'es_paciente_portal' => true,
                'titulo_profesional' => '',
                'nombre_con_titulo' => '',
                'clinica_efectiva_id' => null,
            ];
        }

        $nombre = (string) ($row->nombre ?? '');
        $apPat = (string) ($row->apellidoPat ?? '');
        $apMat = (string) ($row->apellidoMat ?? '');

        $pwdIso = null;
        if (! empty($row->password_set_at)) {
            try {
                $pwdIso = Carbon::parse($row->password_set_at)->toIso8601String();
            } catch (\Throwable $e) {
                $pwdIso = is_string($row->password_set_at) ? $row->password_set_at : null;
            }
        }

        $clinicaEfectiva = $row->clinica_activa_id ?? $row->clinica_id ?? null;

        return [
            'id' => (int) $row->id,
            'email' => (string) ($row->email ?? ''),
            'nombre' => $nombre,
            'apellidoPat' => $apPat,
            'apellidoMat' => $apMat,
            'paciente_id' => $row->paciente_id !== null ? (int) $row->paciente_id : null,
            'rol' => (string) ($row->rol ?? 'paciente'),
            'password_set_at' => $pwdIso,
            'es_paciente_portal' => true,
            'titulo_profesional' => '',
            'nombre_con_titulo' => trim($nombre.' '.$apPat.' '.$apMat),
            'clinica_efectiva_id' => $clinicaEfectiva !== null ? (int) $clinicaEfectiva : null,
        ];
    }
}
