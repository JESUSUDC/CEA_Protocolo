<?php

namespace Application\Security\Port\Out;

use Domain\Users\Entity\User;

interface TokenIssuerPort
{
    public function issue(User $user): string;

    public function validate(string $token): bool;
}
