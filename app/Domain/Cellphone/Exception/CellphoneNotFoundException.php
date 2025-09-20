<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\Exception;

final class CellphoneNotFoundException extends \DomainException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Cellphone not found: %s', $id));
    }
}
