<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Users\Request;

use Illuminate\Foundation\Http\FormRequest;

final class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ideal: verificar que el usuario autenticado coincide con {id} o tenga permisos.
        return true;
    }

    public function rules(): array
    {
        return [
            // El controller espera campos 'currentPassword' y 'newPassword'
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'currentPassword.required' => 'La contraseña actual es requerida.',
            'newPassword.required' => 'La nueva contraseña es requerida.',
            'newPassword.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
