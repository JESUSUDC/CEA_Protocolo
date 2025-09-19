<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class NetworkTechnology
{
    private string $value;

    private function __construct(string $value)
    {
        $v = strtolower(trim($value));
        if ($v === '') {
            throw new \InvalidArgumentException('Network technology cannot be empty.');
        }
        $this->value = $v;
    }

    public static function fromString(string $tech): self
    {
        return new self($tech);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(NetworkTechnology $other): bool
    {
        return $this->value === $other->value;
    }
}
