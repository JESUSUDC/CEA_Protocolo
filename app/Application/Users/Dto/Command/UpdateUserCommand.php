<?php
declare(strict_types=1);

namespace Application\Users\Dto\Command;

final class UpdateUserCommand
{
    public function __construct(
        public string $userId,
        public ?string $name = null,
        public ?string $role = null,
        public ?string $email = null,
        public ?string $username = null
    ) {}
}
