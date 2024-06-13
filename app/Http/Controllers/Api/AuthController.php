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
    public function signup(SignupRequest $request){
        
        $data = $request->validated();

        /** @var \App\Models\User $user */
        $user = User::create([
            'nombre' => $data['nombre'],
            'apellidoPat' => $data['apellidoPat'],
            'apellidoMat' => $data['apellidoMat'],
            'email' => $data['email'],
            'cedula' => $data['cedula'],
            'password'=> bcrypt($data['password'])
        ]);


        return [
            'token' => $user->createToken('token')->plainTextToken,
            'user' => $user
        ];
    }

    public function login(LoginRequest $request){
        $credentials = $request->validated();

        if(!Auth::attempt($credentials)){
            return response()->json(['error' => 'Cedula o Password Incorrecto'],422);
        }

        /** @var User $user */
        $user = Auth::user();

        return [
            'token' => $user->createToken('token')->plainTextToken,
            'user' => $user
        ];
    }

    public function logout(Request $request){
         /** @var User $user */
         $user = $request->user();
         $user->currentAccessToken()->delete;

         return [
            'user' => null
        ];
    }

}
