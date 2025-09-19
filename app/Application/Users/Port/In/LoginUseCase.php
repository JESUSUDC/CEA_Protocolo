<?php
declare(strict_types=1);

namespace Application\Users\Port\In;

interface LoginUseCase
{
    /**
     * Returns JWT token on success.
     */
    public function execute(string $usernameOrEmail, string $password): string;
}
