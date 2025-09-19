<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class CellphoneId
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException('CellphoneId cannot be empty.');
        }
        $this->value = $value;
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
