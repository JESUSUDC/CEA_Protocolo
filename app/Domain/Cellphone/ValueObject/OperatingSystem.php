<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class OperatingSystem
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException('Operating system cannot be empty.');
        }
        $this->value = $value;
    }

    public static function fromString(string $os): self
    {
        return new self($os);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(OperatingSystem $other): bool
    {
        return $this->value === $other->value;
    }
}
