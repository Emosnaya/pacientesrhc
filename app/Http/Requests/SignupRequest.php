<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nombre' => ['required', 'string'],
            'apellidoPat' => 'required',
            'apellidoMat' => 'required',
            'cedula' => 'required|unique:users,cedula',
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->symbols()
                    ->numbers() 
            ]
        ];
    }
    public function messages(){
        return [
            'nombre' => 'El nombre es obligatorio',
            'apellidoPat' => 'El Apellido Paterno es obligatorio',
            'cedula' => 'La cédula es obligatoria',
            'cedula.unique' => 'El usuario ya está registrado',
            'email.required' => 'El Email es obligatorio',
            'email.email' => 'El Email No es válido',
            'password' => 'El Password debe contener al menos 8 caracteres, un simbolo y un número',
        ];
    }
}
