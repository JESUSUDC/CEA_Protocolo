<?php
declare(strict_types=1);

namespace App\Infrastructure\Adapters\Security\Password;

use App\Application\Users\Port\Out\PasswordStrengthPolicyPort;

final class PasswordStrengthPolicyAdapter implements PasswordStrengthPolicyPort
{
    public function isStrong(string $password): bool
    {
        if (mb_strlen($password) < 8) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/\d/', $password)) return false;
        // opcional: símbolos
        return true;
    }

    public function getPolicyDescription(): string
    {
        return 'Minimum 8 chars, upper, lower and digit.';
    }
}
