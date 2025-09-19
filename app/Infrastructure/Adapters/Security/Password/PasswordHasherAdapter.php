<?php
declare(strict_types=1);

namespace Infrastructure\Adapters\Security\Password;

use Domain\Users\Service\Contracts\PasswordHasher;
use Domain\Users\ValueObject\PasswordHash;

final class PasswordHasherAdapter implements PasswordHasher
{
    private int $algo = PASSWORD_BCRYPT;
    private array $options;

    public function __construct(array $options = ['cost' => 12])
    {
        $this->options = $options;
    }

    public function hash(string $plain): PasswordHash
    {
        $h = password_hash($plain, $this->algo, $this->options);
        if ($h === false) {
            throw new \RuntimeException('Password hashing failed.');
        }
        return PasswordHash::fromHash($h);
    }

    public function verify(string $plain, PasswordHash $hash): bool
    {
        return password_verify($plain, $hash->toString());
    }
}
