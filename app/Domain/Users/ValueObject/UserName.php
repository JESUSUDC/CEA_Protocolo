<?php
declare(strict_types=1);

namespace App\Domain\Users\ValueObject;

use App\Domain\Users\Exception\InvalidUserName;

final class UserName
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) < 3) {
            throw new InvalidUserName('Username must be at least 3 characters.');
        }
        // optionally more validation (allowed chars)
        $this->value = $value;
    }

    public static function fromString(string $username): self
    {
        return new self($username);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(UserName $other): bool
    {
        return $this->value === $other->value;
    }
}
