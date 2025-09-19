<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class SimCount
{
    private int $value;

    private function __construct(int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Sim count must be at least 1.');
        }
        $this->value = $value;
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function equals(SimCount $other): bool
    {
        return $this->value === $other->value;
    }
}
