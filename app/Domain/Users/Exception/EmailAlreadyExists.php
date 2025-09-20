<?php
declare(strict_types=1);

namespace App\Domain\Users\Exception;

final class EmailAlreadyExists extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct("El correo '{$email}' ya está en uso.");
    }
}
