<?php
declare(strict_types=1);

namespace App\Domain\Users\ValueObject;

use App\Domain\Users\Exception\InvalidRole;

final class Role
{
    private const ALLOWED = ['admin', 'user', 'support'];

    private string $value;

    private function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (!in_array($value, self::ALLOWED, true)) {
            throw new InvalidRole(sprintf('Role "%s" is not allowed.', $value));
        }
        $this->value = $value;
    }

    public static function fromString(string $role): self
    {
        return new self($role);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(Role $other): bool
    {
        return $this->value === $other->value;
    }
}
