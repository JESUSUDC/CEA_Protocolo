<?php
declare(strict_types=1);

namespace Domain\Cellphone\ValueObject;

final class Megapixels
{
    private float $value;

    private function __construct(float $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Megapixels cannot be negative.');
        }
        $this->value = $value;
    }

    public static function fromFloat(float $mp): self
    {
        return new self($mp);
    }

    public function toFloat(): float
    {
        return $this->value;
    }

    public function equals(Megapixels $other): bool
    {
        return $this->value === $other->value;
    }
}
