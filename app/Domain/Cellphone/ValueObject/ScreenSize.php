<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class ScreenSize
{
    private float $inches;

    private function __construct(float $inches)
    {
        if ($inches <= 0 || $inches > 10) {
            throw new \InvalidArgumentException('Screen size must be >0 and reasonable (<=10).');
        }
        $this->inches = $inches;
    }

    public static function fromFloat(float $inches): self
    {
        return new self($inches);
    }

    public function toFloat(): float
    {
        return $this->inches;
    }

    public function equals(ScreenSize $other): bool
    {
        return $this->inches === $other->inches;
    }
}
