<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Dto\Command;

final class UpdateCellphoneCommand
{
    public function __construct(
        public string $id,
        public ?string $brand = null,
        public ?float $screenSize = null,
        public ?float $megapixels = null,
        public ?int $ramMb = null,
        public ?int $storagePrimaryMb = null,
        public ?int $storageSecondaryMb = null,
        public ?string $operatingSystem = null,
        public ?string $operator = null,
        public ?string $networkTechnology = null,
        public ?bool $wifi = null,
        public ?bool $bluetooth = null,
        public ?int $cameraCount = null,
        public ?string $cpuBrand = null,
        public ?float $cpuSpeedGhz = null,
        public ?bool $nfc = null,
        public ?bool $fingerprint = null,
        public ?bool $ir = null,
        public ?bool $waterResistant = null,
        public ?int $simCount = null
    ) {}
}
