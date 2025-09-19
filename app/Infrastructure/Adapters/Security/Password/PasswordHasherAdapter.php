<?php
declare(strict_types=1);

namespace App\Infrastructure\Adapters\Security\Password;

use App\Application\Users\Port\Out\PasswordHasherPort;

final class PasswordHasherAdapter implements PasswordHasherPort
{
    private int $algo;
    private array $options;

    public function __construct(array $options = ['cost' => 12])
    {
        $this->algo = PASSWORD_BCRYPT;
        $this->options = $options;
    }

    public function hash(string $plainPassword): string
    {
        $h = password_hash($plainPassword, $this->algo, $this->options);
        if ($h === false) {
            throw new \RuntimeException('Password hashing failed.');
        }
        return $h;
    }

    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}
