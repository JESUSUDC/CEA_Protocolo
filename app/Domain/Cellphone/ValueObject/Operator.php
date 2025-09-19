<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class Operator
{
    private ?string $value;

    private function __construct(?string $value)
    {
        $this->value = $value === null ? null : trim($value);
    }

    public static function fromString(?string $op): self
    {
        return new self($op);
    }

    public function toString(): ?string
    {
        return $this->value;
    }

    public function equals(Operator $other): bool
    {
        return $this->value === $other->value;
    }
}
