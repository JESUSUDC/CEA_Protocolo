<?php
declare(strict_types=1);

namespace App\Domain\Users\Service\Contracts;

interface PasswordStrengthEvaluator
{
    /**
     * Returns true if password meets strength policy.
     */
    public function isStrongEnough(string $plain): bool;
}
