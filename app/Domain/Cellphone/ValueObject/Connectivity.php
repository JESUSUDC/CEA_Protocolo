<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\ValueObject;

final class Connectivity
{
    private bool $wifi;
    private bool $bluetooth;

    private function __construct(bool $wifi, bool $bluetooth)
    {
        $this->wifi = $wifi;
        $this->bluetooth = $bluetooth;
    }

    public static function fromBooleans(bool $wifi, bool $bluetooth): self
    {
        return new self($wifi, $bluetooth);
    }

    public function hasWifi(): bool { return $this->wifi; }
    public function hasBluetooth(): bool { return $this->bluetooth; }

    public function equals(Connectivity $other): bool
    {
        return $this->wifi === $other->wifi && $this->bluetooth === $other->bluetooth;
    }
}
