<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function signup(SignupRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Generar token de verificación
        $verificationToken = Str::random(60);
        
        $user = User::create([
            'nombre' => $validated['nombre'],
            'apellidoPat' => $validated['apellidoPat'],
            'apellidoMat' => $validated['apellidoMat'] ?? null,
            'email' => $validated['email'],
            'cedula' => $validated['cedula'],
            'password' => Hash::make($validated['password']),
            'isAdmin' => filter_var($validated['isAdmin'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'imagen' => 'perfiles/avatar-default.png', // Imagen por defecto
            'email_verification_token' => $verificationToken,
            'email_verified' => false
        ]);

        // Enviar correo de verificación
        $verificationUrl = env('FRONTEND_URL', 'http://localhost:3000') . "/verify-email/{$verificationToken}";
        
        try {
            Mail::send('emails.verify-email', [
                'user' => $user,
                'verificationUrl' => $verificationUrl
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Verifica tu correo electrónico - CERCAP');
            });
        } catch (\Exception $e) {
            // Log error but don't fail registration
            \Log::error('Error sending verification email: ' . $e->getMessage());
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->fresh(),
            'message' => 'Usuario creado exitosamente. Se ha enviado un correo de verificación.'
        ], JsonResponse::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
    $credentials = $request->only('cedula', 'password');

    $userExists = User::where('cedula', $credentials['cedula'])->exists();



    if (!$userExists) {
        return response()->json([
            'message' => 'Usuario no encontrado',
            'errors' => [
                'cedula' => ['No existe ningún usuario registrado con esta cédula. ¿Deseas crear una cuenta?']
            ]
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Credenciales incorrectas',
            'errors' => [
                'auth' => ['Cédula o Contraseña incorrectas.']
            ]
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @var User $user */
    $user = Auth::user();
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user
    ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
            'user' => null
        ]);
    }

    /**
     * Verificar correo electrónico
     */
    public function verifyEmail($token): JsonResponse
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Token de verificación inválido o expirado'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->update([
            'email_verified' => true,
            'email_verified_at' => now(),
            'email_verification_token' => null
        ]);

        return response()->json([
            'message' => 'Correo electrónico verificado exitosamente'
        ]);
    }

    /**
     * Solicitar restablecimiento de contraseña
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('email', $request->email)->first();
        
        // Generar token de restablecimiento
        $resetToken = Str::random(60);
        $user->update(['email_verification_token' => $resetToken]);

        $resetUrl = env('FRONTEND_URL', 'http://localhost:3000') . "/reset-password/{$resetToken}";

        try {
            Mail::send('emails.reset-password', [
                'user' => $user,
                'resetUrl' => $resetUrl
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Restablecer contraseña - CERCAP');
            });

            return response()->json([
                'message' => 'Se ha enviado un enlace de restablecimiento a tu correo electrónico'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending password reset email: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al enviar el correo de restablecimiento'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restablecer contraseña
     */
    public function resetPassword(Request $request, $token): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Token de restablecimiento inválido o expirado'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'email_verification_token' => null
        ]);

        return response()->json([
            'message' => 'Contraseña restablecida exitosamente'
        ]);
    }
}