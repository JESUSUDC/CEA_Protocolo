<?php
declare(strict_types=1);

namespace Application\Users\Response;

final class UserResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $role,
        public string $email,
        public string $username,
        public bool $active
    ) {}
}
