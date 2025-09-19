<?php
declare(strict_types=1);

namespace Domain\Cellphone\ValueObject;

final class Cpu
{
    private string $brand;
    private float $ghz;

    private function __construct(string $brand, float $ghz)
    {
        if (trim($brand) === '') {
            throw new \InvalidArgumentException('CPU brand cannot be empty.');
        }
        if ($ghz <= 0) {
            throw new \InvalidArgumentException('CPU speed must be positive.');
        }
        $this->brand = $brand;
        $this->ghz = $ghz;
    }

    public static function from(string $brand, float $ghz): self
    {
        return new self($brand, $ghz);
    }

    public function brand(): string { return $this->brand; }
    public function ghz(): float { return $this->ghz; }

    public function equals(Cpu $other): bool
    {
        return $this->brand === $other->brand && $this->ghz === $other->ghz;
    }
}
