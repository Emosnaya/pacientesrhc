<?php

namespace App\Http\Controllers;

use App\Mail\PacientePortalOtpMail;
use App\Models\PacientePortalOtp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $user->password = Hash::make($request->password);
        $user->password_set_at = now();
        $user->save();

        $user->currentAccessToken()->delete();

        $plain = $user->createToken('auth_token')->plainTextToken;
        $user->load('pacienteRecord');

        return response()->json([
            'token' => $plain,
            'user' => $user,
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
        $user->load('pacienteRecord');

        return response()->json([
            'token' => $plain,
            'user' => $user,
        ]);
    }
}
