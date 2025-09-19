<?php
declare(strict_types=1);

namespace Infrastructure\Adapters\Security\Password;

use Domain\Users\Service\Contracts\PasswordStrengthEvaluator;

final class PasswordStrengthPolicyAdapter implements PasswordStrengthEvaluator
{
    // Example: at least 8 chars, upper, lower, digit
    public function isStrongEnough(string $plain): bool
    {
        if (mb_strlen($plain) < 8) return false;
        if (!preg_match('/[A-Z]/', $plain)) return false;
        if (!preg_match('/[a-z]/', $plain)) return false;
        if (!preg_match('/\d/', $plain)) return false;
        return true;
    }
}
