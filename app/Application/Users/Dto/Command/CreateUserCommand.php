<?php
declare(strict_types=1);

namespace App\Application\Users\Dto\Command;

final class CreateUserCommand
{
    public function __construct(
        public string $name,
        public string $role,
        public string $email,
        public string $username,
        public string $password,
        public ?string $id = null
    ) {}
}
