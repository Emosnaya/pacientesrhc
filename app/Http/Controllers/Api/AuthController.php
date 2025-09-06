<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signup(SignupRequest $request)
    {
        $data = $request->validated();

        // Crea el usuario
        /** @var \App\Models\User $user */
        $user = User::create([
            'nombre' => $data['nombre'],
            'apellidoPat' => $data['apellidoPat'],
            'apellidoMat' => $data['apellidoMat'] ?? null,
            'email' => $data['email'],
            'cedula' => $data['cedula'],
            'password' => bcrypt($data['password']),
            'isAdmin' => $data['isAdmin'] === 'true' ? true : false,
        ]);

        // Retorna token y usuario
        return response()->json([
            'token' => $user->createToken('token')->plainTextToken,
            'user' => $user
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('cedula', 'password');

        // Intentamos autenticar
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'cedula o password incorrectos',
                'errors' => [
                    'error' => ['cedula o password incorrectos.']
                ]
            ], 422);
        }

        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'token' => $user->createToken('token')->plainTextToken,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // Corregir para llamar al método delete()
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
            'user' => null
        ]);
    }
}
