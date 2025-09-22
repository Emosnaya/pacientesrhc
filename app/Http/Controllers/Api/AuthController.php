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

class AuthController extends Controller
{
    public function signup(SignupRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $user = User::create([
            'nombre' => $validated['nombre'],
            'apellidoPat' => $validated['apellidoPat'],
            'apellidoMat' => $validated['apellidoMat'] ?? null,
            'email' => $validated['email'],
            'cedula' => $validated['cedula'],
            'password' => Hash::make($validated['password']),
            'isAdmin' => filter_var($validated['isAdmin'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'imagen' => 'perfiles/avatar-default.png' // Imagen por defecto
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->fresh()
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
}