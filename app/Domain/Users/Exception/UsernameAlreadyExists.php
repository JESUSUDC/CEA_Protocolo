<?php
declare(strict_types=1);

namespace App\Domain\Users\Exception;

final class UsernameAlreadyExists extends DomainException
{
    public function __construct(string $username)
    {
        parent::__construct("El nombre de usuario '{$username}' ya está en uso.");
    }
}
