<?php

namespace App\Application\Users\Port\Out;

interface PasswordStrengthPolicyPort
{
    public function isStrong(string $password): bool;

    public function getPolicyDescription(): string;
}
