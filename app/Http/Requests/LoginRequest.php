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
            'cedula' => 'La cédula es obligatoria',
            'cedula.unique' => 'El usuario ya está registrado',
            'cedula.exists' => 'El usuario no existe',
            'password' => 'El Password es Obligatorio',
        ];
    }
}
