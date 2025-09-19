<?php
declare(strict_types=1);

namespace App\Application\Users\Port\In;

interface ResetPasswordUseCase
{
    public function execute(string $token, string $newPassword): void;
}
