<?php
declare(strict_types=1);

namespace App\Application\Users\Port\In;

interface LogoutUseCase
{
    public function execute(string $userId): void;
}
