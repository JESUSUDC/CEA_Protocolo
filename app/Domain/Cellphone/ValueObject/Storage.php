<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class Storage
{
    private int $megabytes;

    private function __construct(int $megabytes)
    {
        if ($megabytes < 0) {
            throw new \InvalidArgumentException('Storage cannot be negative.');
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

    public function equals(Storage $other): bool
    {
        return $this->megabytes === $other->megabytes;
    }
}
