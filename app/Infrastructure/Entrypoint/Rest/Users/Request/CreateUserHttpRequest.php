<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Users\Request;

use Illuminate\Foundation\Http\FormRequest;

final class CreateUserHttpRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'role' => 'nullable|string|in:admin,user,support',
            'email' => 'required|email',
            'username' => 'required|string|min:3',
            'password' => 'required|string|min:8'
        ];
    }
}
