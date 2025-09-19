<?php
declare(strict_types=1);

namespace App\Application\Users\Dto\Command;

final class DeleteUserCommand
{
    public function __construct(public string $userId) {}
}
