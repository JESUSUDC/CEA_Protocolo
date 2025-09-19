<?php
declare(strict_types=1);

namespace Domain\Cellphone\ValueObject;

final class Cameras
{
    private int $count;

    private function __construct(int $count)
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('Cameras count cannot be negative.');
        }
        $this->count = $count;
    }

    public static function fromInt(int $count): self
    {
        return new self($count);
    }

    public function toInt(): int
    {
        return $this->count;
    }

    public function equals(Cameras $other): bool
    {
        return $this->count === $other->count;
    }
}
