<?php
declare(strict_types=1);

namespace Domain\Users\ValueObject;

use Domain\Users\Exception\InvalidUserName;

final class Email
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim($value);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format.');
        }
        $this->value = mb_strtolower($value);
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
