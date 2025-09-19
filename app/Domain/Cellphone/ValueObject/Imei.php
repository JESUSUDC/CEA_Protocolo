<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

use App\Domain\Cellphone\Exception\InvalidImei;

final class Imei
{
    private string $value;

    private function __construct(string $value)
    {
        $v = preg_replace('/\s+/', '', $value);
        if (!preg_match('/^\d{14,16}$/', $v)) {
            throw new InvalidImei('IMEI must be 14-16 digits.');
        }
        $this->value = $v;
    }

    public static function fromString(string $imei): self
    {
        return new self($imei);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(Imei $other): bool
    {
        return $this->value === $other->value;
    }
}
