<?php
declare(strict_types=1);

namespace App\Domain\Users\Service\Contracts;

use App\Domain\Users\ValueObject\PasswordHash;

interface PasswordHasher
{
    /**
     * Hash plain password and return PasswordHash VO.
     */
    public function hash(string $plain): PasswordHash;

    /**
     * Verify plain against hash.
     */
    public function verify(string $plain, PasswordHash $hash): bool;
}
