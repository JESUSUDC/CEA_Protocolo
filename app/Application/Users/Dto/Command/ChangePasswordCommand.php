<?php
declare(strict_types=1);

namespace App\Application\Users\Dto\Command;

final class ChangePasswordCommand
{
    public function __construct(
        public string $userId,
        public string $currentPassword,
        public string $newPassword
    ) {}
}
