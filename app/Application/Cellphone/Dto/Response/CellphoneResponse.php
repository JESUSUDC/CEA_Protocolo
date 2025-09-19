<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Dto\Response;

final class CellphoneResponse implements \JsonSerializable
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

    /**
     * Serialización para API (JSON).
     *
     * Usamos camelCase intencionalmente porque tu mapper lo normaliza;
     * si prefieres snake_case muévelo aquí.
     *
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'brand' => $this->brand,
            'imei' => $this->imei,
            'screenSize' => $this->screenSize,
            'megapixels' => $this->megapixels,
            'ramMb' => $this->ramMb,
            'storagePrimaryMb' => $this->storagePrimaryMb,
            'storageSecondaryMb' => $this->storageSecondaryMb,
            'operatingSystem' => $this->operatingSystem,
            'operator' => $this->operator,
            'networkTechnology' => $this->networkTechnology,
            'wifi' => $this->wifi,
            'bluetooth' => $this->bluetooth,
            'cameraCount' => $this->cameraCount,
            'cpuBrand' => $this->cpuBrand,
            'cpuSpeedGhz' => $this->cpuSpeedGhz,
            'nfc' => $this->nfc,
            'fingerprint' => $this->fingerprint,
            'ir' => $this->ir,
            'waterResistant' => $this->waterResistant,
            'simCount' => $this->simCount,
        ];
    }
}
