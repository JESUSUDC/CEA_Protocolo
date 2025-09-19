<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\LogoutUseCase;

final class LogoutService implements LogoutUseCase
{
    // If JWT is stateless, logout may be a no-op or push token to blacklist via TokenRepository (Port Out).
    public function __construct()
    {
    }

    public function execute(string $userId): void
    {
        // Implementation depends on token invalidation strategy.
        // For stateless JWTs do nothing (client deletes token).
        // Alternatively, push token to blacklist repository (not implemented here).
    }
}
