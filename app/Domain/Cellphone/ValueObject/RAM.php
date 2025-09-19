<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class RAM
{
    private int $megabytes;

    private function __construct(int $megabytes)
    {
        if ($megabytes <= 0) {
            throw new \InvalidArgumentException('RAM must be positive integer (MB).');
        }
        $this->megabytes = $megabytes;
    }

    public static function fromMegabytes(int $mb): self
    {
        return new self($mb);
    }

    public function toMegabytes(): int
    {
        return $this->megabytes;
    }

    public function equals(RAM $other): bool
    {
        return $this->megabytes === $other->megabytes;
    }
}
