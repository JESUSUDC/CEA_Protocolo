<?php
declare(strict_types=1);

namespace App\Domain\Users\ValueObject;

final class UserId
{
    private string $value;

    private function __construct(string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('UserId cannot be empty.');
        }
        $this->value = $value;
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }
}
