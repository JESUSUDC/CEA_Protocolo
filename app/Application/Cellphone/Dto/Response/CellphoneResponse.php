<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Response;

final class CellphoneResponse
{
    public function __construct(
        public string $id,
        public string $brand,
        public string $imei,
        public float $screenSize,
        public float $megapixels,
        public int $ramMb,
        public int $storagePrimaryMb,
        public ?int $storageSecondaryMb,
        public string $operatingSystem,
        public ?string $operator,
        public string $networkTechnology,
        public bool $wifi,
        public bool $bluetooth,
        public int $cameraCount,
        public string $cpuBrand,
        public float $cpuSpeedGhz,
        public bool $nfc,
        public bool $fingerprint,
        public bool $ir,
        public bool $waterResistant,
        public int $simCount
    ) {}
}
