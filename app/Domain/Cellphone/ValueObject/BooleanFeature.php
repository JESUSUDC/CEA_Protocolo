<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class BooleanFeature
{
    private bool $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function fromBool(bool $b): self
    {
        return new self($b);
    }

    public function value(): bool
    {
        return $this->value;
    }

    public function equals(BooleanFeature $other): bool
    {
        return $this->value === $other->value;
    }
}
