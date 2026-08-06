<?php

namespace App\Http\Controllers;

use App\Mail\PacientePortalOtpMail;
use App\Models\Paciente;
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
     * Alta pública desde la app: crea expediente + cuenta portal y envía OTP.
     * Si ya existe cuenta sin contraseña, reenvía el OTP (activar / primer acceso).
     */
    public function register(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'nombre' => 'required|string|max:120',
            'apellidoPat' => 'required|string|max:120',
            'apellidoMat' => 'nullable|string|max:120',
            'email' => 'required|email|max:190',
            'telefono' => 'nullable|string|max:40',
            'fechaNacimiento' => 'nullable|date|before:today',
            'genero' => 'nullable|in:0,1',
            'acepto_privacidad' => 'accepted',
        ], [
            'acepto_privacidad.accepted' => 'Debes aceptar el aviso de privacidad para continuar.',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $v->errors()], 422);
        }

        $email = strtolower(trim((string) $request->email));
        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            if (! $existing->paciente_id) {
                return response()->json([
                    'message' => 'Este correo ya está registrado en LynkaMed. Usa otro o inicia sesión en el portal de clínicas.',
                ], 422);
            }

            if ($existing->password_set_at) {
                return response()->json([
                    'message' => 'Ya tienes una cuenta. Inicia sesión o recupera tu contraseña.',
                    'code' => 'account_exists',
                ], 409);
            }

            $sent = $this->issueOtpForUser($existing);
            if ($sent !== true) {
                return $sent;
            }

            return response()->json([
                'message' => 'Te enviamos un código para activar tu cuenta.',
                'email' => $email,
                'requires_otp' => true,
            ]);
        }

        try {
            $user = DB::transaction(function () use ($request, $email) {
                $vAviso = (string) config('legal.version_aviso_privacidad', '1');
                $vTerm = (string) config('legal.version_terminos', '1');

                $paciente = new Paciente;
                $paciente->nombre = trim((string) $request->nombre);
                $paciente->apellidoPat = trim((string) $request->apellidoPat);
                $paciente->apellidoMat = trim((string) ($request->apellidoMat ?? ''));
                $paciente->email = $email;
                $paciente->telefono = $request->filled('telefono') ? trim((string) $request->telefono) : null;
                $paciente->fechaNacimiento = $request->filled('fechaNacimiento') ? $request->fechaNacimiento : null;
                if ($request->filled('genero')) {
                    $paciente->genero = (int) $request->genero === 1 ? 1 : 0;
                }
                $paciente->clinica_id = null;
                $paciente->sucursal_id = null;
                $paciente->aviso_privacidad_aceptado_at = now();
                $paciente->version_aviso = 'aviso:'.$vAviso.'|terminos:'.$vTerm.'|origen:app';
                $paciente->save();

                $user = new User;
                $user->nombre = $paciente->nombre ?? '';
                $user->apellidoPat = $paciente->apellidoPat ?? '';
                $user->apellidoMat = $paciente->apellidoMat ?? '';
                $user->email = $email;
                $user->cedula = null;
                $user->paciente_id = $paciente->id;
                $user->rol = 'paciente';
                $user->password = null;
                $user->password_set_at = null;
                $user->email_verified = false;
                $user->clinica_id = null;
                $user->clinica_activa_id = null;
                $user->sucursal_id = null;
                $user->isAdmin = false;
                $user->isSuperAdmin = false;
                $user->save();

                return $user;
            });
        } catch (\Throwable $e) {
            Log::error('PacientePortal register failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'No se pudo crear la cuenta. Intenta de nuevo.',
            ], 500);
        }

        $sent = $this->issueOtpForUser($user);
        if ($sent !== true) {
            return $sent;
        }

        return response()->json([
            'message' => 'Cuenta creada. Te enviamos un código a tu correo.',
            'email' => $email,
            'requires_otp' => true,
        ], 201);
    }

    /**
     * Solicitar OTP al correo (activar cuenta o recuperar contraseña).
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
            'purpose' => 'nullable|in:setup,reset',
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

        $sent = $this->issueOtpForUser($user);
        if ($sent !== true) {
            return $sent;
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

        if (! $user->email_verified) {
            $user->email_verified = true;
            $user->save();
        }

        $user->tokens()->delete();
        $plain = $user->createToken('portal-setup', [self::ABILITY_SET_PASSWORD])->plainTextToken;

        // Tras OTP siempre se define / restablece contraseña (alta, activación o recuperación).
        return response()->json([
            'token' => $plain,
            'requires_password' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'paciente_id' => $user->paciente_id,
            ],
        ]);
    }

    /**
     * @return true|\Illuminate\Http\JsonResponse
     */
    private function issueOtpForUser(User $user)
    {
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

        $email = strtolower(trim((string) $user->email));

        try {
            Mail::to($email)->send(new PacientePortalOtpMail(
                nombre: trim(($user->nombre ?? '').' '.($user->apellidoPat ?? '')),
                code: $code
            ));
        } catch (\Throwable $e) {
            Log::error('PacientePortal OTP mail failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'No se pudo enviar el correo. Intenta más tarde.',
            ], 503);
        }

        return true;
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
                $user->email_verified = true;
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
