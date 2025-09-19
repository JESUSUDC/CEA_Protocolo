<?php
declare(strict_types=1);

namespace Application\Users\Dto\Command;

final class DeleteUserCommand
{
    public function __construct(public string $userId) {}
}
