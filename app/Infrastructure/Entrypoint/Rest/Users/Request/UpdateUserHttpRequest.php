<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Users\Request;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserHttpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // validar permisos en middleware si aplica
    }

    public function rules(): array
    {
        return [
            // "sometimes" permite peticiones parciales (PATCH/PUT semantics)
            'name' => 'sometimes|string|min:3',
            'role' => 'sometimes|string|in:admin,user,support',
            'email' => 'sometimes|email',
            'username' => 'sometimes|string|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'role.in' => 'El rol debe ser uno de: admin, user, support.',
            'email.email' => 'El correo no tiene un formato válido.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
        ];
    }
}
