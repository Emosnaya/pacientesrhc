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
        'cedula' => 'required|string',
        'password' => 'required|string',
    ];
}

public function messages()
{
    return [
        'cedula.required' => 'La Cédula es Obligatoria',
        'password.required' => 'El Password es Obligatorio',
    ];
}
}
