<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class Brand
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException('Brand cannot be empty.');
        }
        $this->value = $value;
    }

    public static function fromString(string $brand): self
    {
        return new self($brand);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(Brand $other): bool
    {
        return $this->value === $other->value;
    }
}
