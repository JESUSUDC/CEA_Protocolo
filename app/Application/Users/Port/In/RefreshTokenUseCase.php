<?php
declare(strict_types=1);

namespace App\Application\Users\Port\In;

interface RefreshTokenUseCase
{
    public function execute(string $refreshToken): array;
}