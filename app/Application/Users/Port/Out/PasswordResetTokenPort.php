<?php

namespace App\Application\Users\Port\Out;

use App\Domain\Users\Entity\User;

interface PasswordResetTokenPort
{
    public function generateToken(User $user): string;

    public function validateToken(User $user, string $token): bool;

    public function invalidateToken(User $user): void;
}
