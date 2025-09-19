<?php

namespace App\Application\Security\Port\Out;

use App\Domain\Users\Entity\User;

interface TokenIssuerPort
{
    public function issue(User $user): string;

    public function validate(string $token): bool;
}
