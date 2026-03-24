<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PacienteOtpVerification;
use App\Mail\PacienteOtpMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PacienteVerificationController extends Controller
{
    /**
     * Verificar si un email de paciente ya existe en el sistema.
     * Si existe, devuelve info parcial y permite solicitar OTP.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $paciente = Paciente::where('email', $email)->first();

        if (!$paciente) {
            return response()->json([
                'exists' => false,
                'message' => 'Email disponible, puede registrar nuevo paciente'
            ]);
        }

        // Verificar si ya está vinculado a esta clínica
        $yaVinculado = $paciente->clinicas()
            ->where('clinicas.id', $clinicaId)
            ->exists();

        if ($yaVinculado) {
            return response()->json([
                'exists' => true,
                'already_linked' => true,
                'paciente_id' => $paciente->id,
                'message' => 'Este paciente ya está registrado en su clínica'
            ]);
        }

        // Paciente existe pero no está vinculado a esta clínica
        return response()->json([
            'exists' => true,
            'already_linked' => false,
            'paciente_preview' => [
                'nombre' => $paciente->nombre,
                'apellidoPat' => $paciente->apellidoPat,
                'email_masked' => $this->maskEmail($email),
            ],
            'message' => 'Paciente encontrado. Se requiere verificación OTP para vincularlo a su clínica.'
        ]);
    }

    /**
     * Solicitar envío de OTP al email del paciente existente.
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $paciente = Paciente::where('email', $email)->first();

        if (!$paciente) {
            return response()->json([
                'error' => 'No se encontró paciente con ese email'
            ], 404);
        }

        // Verificar si ya está vinculado
        if ($paciente->clinicas()->where('clinicas.id', $clinicaId)->exists()) {
            return response()->json([
                'error' => 'Este paciente ya está vinculado a su clínica'
            ], 400);
        }

        // Invalidar OTPs anteriores no usados
        PacienteOtpVerification::where('paciente_id', $paciente->id)
            ->where('clinica_id', $clinicaId)
            ->whereNull('verified_at')
            ->delete();

        // Generar nuevo OTP (6 dígitos)
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $verification = PacienteOtpVerification::create([
            'paciente_id' => $paciente->id,
            'clinica_id' => $clinicaId,
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Enviar email con OTP
        try {
            Mail::to($email)->send(new PacienteOtpMail(
                $paciente,
                $otpCode,
                $user->clinica_activa ?? $user->clinica
            ));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al enviar el código de verificación',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Código OTP enviado al email del paciente',
            'email_masked' => $this->maskEmail($email),
            'expires_in_minutes' => 15
        ]);
    }

    /**
     * Verificar OTP y vincular paciente a la clínica.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->email));
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $paciente = Paciente::where('email', $email)->first();

        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        $verification = PacienteOtpVerification::where('paciente_id', $paciente->id)
            ->where('clinica_id', $clinicaId)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$verification) {
            return response()->json([
                'error' => 'No hay código OTP válido. Solicite uno nuevo.'
            ], 400);
        }

        if (!$verification->isValid($request->otp_code)) {
            return response()->json([
                'error' => 'Código OTP incorrecto o expirado'
            ], 400);
        }

        // Marcar como verificado
        $verification->markAsVerified();

        // Vincular paciente a la clínica
        $sucursalId = $user->sucursal_id;

        // Evitar duplicados en pivot
        if (!$paciente->clinicas()->where('clinicas.id', $clinicaId)->exists()) {
            $paciente->clinicas()->attach($clinicaId, [
                'sucursal_id' => $sucursalId,
                'user_id' => $user->id,
                'vinculado_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Paciente verificado y vinculado exitosamente',
            'paciente' => $paciente->load('clinicas')
        ]);
    }

    /**
     * Enmascarar email para mostrar parcialmente.
     * ejemplo@test.com -> e***o@t***.com
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) return '***@***.***';

        $local = $parts[0];
        $domain = $parts[1];

        $maskedLocal = strlen($local) > 2
            ? $local[0] . str_repeat('*', strlen($local) - 2) . $local[strlen($local) - 1]
            : str_repeat('*', strlen($local));

        $domainParts = explode('.', $domain);
        $maskedDomain = strlen($domainParts[0]) > 1
            ? $domainParts[0][0] . str_repeat('*', strlen($domainParts[0]) - 1)
            : '*';

        return $maskedLocal . '@' . $maskedDomain . '.' . end($domainParts);
    }
}
