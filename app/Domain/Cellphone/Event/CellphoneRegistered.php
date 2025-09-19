<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\Event;

use App\Domain\Cellphone\ValueObject\CellphoneId;

final class CellphoneRegistered
{
    private CellphoneId $id;
    private string $imei;
    private string $brand;

    public function __construct(CellphoneId $id, string $imei, string $brand)
    {
        $this->id = $id;
        $this->imei = $imei;
        $this->brand = $brand;
    }

    public function id(): CellphoneId { return $this->id; }
    public function imei(): string { return $this->imei; }
    public function brand(): string { return $this->brand; }
}
