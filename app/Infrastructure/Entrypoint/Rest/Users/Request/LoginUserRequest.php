<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Users\Request;

use Illuminate\Foundation\Http\FormRequest;

final class LoginUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autorización por middleware si aplica
    }

    public function rules(): array
    {
        return [
            // Campo usado en controller: 'username_or_email'
            'username_or_email' => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'username_or_email.required' => 'El usuario o correo es requerido.',
            'password.required' => 'La contraseña es requerida.',
        ];
    }
}
