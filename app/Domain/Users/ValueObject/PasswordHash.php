<?php
declare(strict_types=1);

namespace App\Domain\Users\ValueObject;

final class PasswordHash
{
    private string $hash;

    private function __construct(string $hash)
    {
        if (trim($hash) === '') {
            throw new \InvalidArgumentException('Password hash cannot be empty.');
        }
        $this->hash = $hash;
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function toString(): string
    {
        return $this->hash;
    }

    public function equals(PasswordHash $other): bool
    {
        return hash_equals($this->hash, $other->hash);
    }
}
