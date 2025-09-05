<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends FormRequest
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
            'cedula' => 'required|exists:users,cedula',
            'password' => 'required'
        ];
    }

    public function messages(){
        return [
            'cedula.required' => 'La cédula es obligatoria',
            'cedula.exists' => 'Usuario o contraseña incorrectos',
            'password.required' => 'El Password es Obligatorio',
        ];
    }
}
