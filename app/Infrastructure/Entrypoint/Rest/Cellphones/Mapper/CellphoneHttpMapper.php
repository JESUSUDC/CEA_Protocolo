<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Cellphones\Mapper;

use App\Domain\Cellphone\Entity\Cellphone;
use Illuminate\Http\Request;
use App\Application\Cellphone\Dto\Command\CreateCellphoneCommand;
use App\Application\Cellphone\Dto\Command\UpdateCellphoneCommand;
use App\Application\Cellphone\Response\CellphoneResponse;

final class CellphoneHttpMapper
{
    public function toRegisterCommand(array $dto): CreateCellphoneCommand
    {
        return new CreateCellphoneCommand(
            id: $dto['id'] ?? \Illuminate\Support\Str::uuid()->toString(),
            brand: $dto['brand'],
            imei: $dto['imei'],
            screenSize: (float)$dto['screen_size'],
            megapixels: (float)$dto['megapixels'],
            ramMb: (int)$dto['ram_mb'],
            storagePrimaryMb: (int)$dto['storage_primary_mb'],
            storageSecondaryMb: isset($dto['storage_secondary_mb']) ? (int)$dto['storage_secondary_mb'] : null,
            operatingSystem: $dto['operating_system'],
            operator: $dto['operator'] ?? null,
            networkTechnology: $dto['network_technology'],
            wifi: (bool)($dto['wifi'] ?? false),
            bluetooth: (bool)($dto['bluetooth'] ?? false),
            cameraCount: (int)$dto['camera_count'],
            cpuBrand: $dto['cpu_brand'],
            cpuSpeedGhz: (float)$dto['cpu_speed_ghz'],
            nfc: (bool)($dto['nfc'] ?? false),
            fingerprint: (bool)($dto['fingerprint'] ?? false),
            ir: (bool)($dto['ir'] ?? false),
            waterResistant: (bool)($dto['water_resistant'] ?? false),
            simCount: (int)$dto['sim_count']
        );
    }

    public function toHttp(CellphoneResponse $c): array
    {
        return [
            'id' => $c->id,
            'brand' => $c->brand,
            'imei' => $c->imei,
            'screen_size' => $c->screenSize,
            'megapixels' => $c->megapixels,
            'ram_mb' => $c->ramMb,
            'storage_primary_mb' => $c->storagePrimaryMb,
            'storage_secondary_mb' => $c->storageSecondaryMb,
            'operating_system' => $c->operatingSystem,
            'operator' => $c->operator,
            'network_technology' => $c->networkTechnology,
            'wifi' => $c->wifi,
            'bluetooth' => $c->bluetooth,
            'camera_count' => $c->cameraCount,
            'cpu_brand' => $c->cpuBrand,
            'cpu_speed_ghz' => $c->cpuSpeedGhz,
            'nfc' => $c->nfc,
            'fingerprint' => $c->fingerprint,
            'ir' => $c->ir,
            'water_resistant' => $c->waterResistant,
            'sim_count' => $c->simCount,
        ];
    }

    public function toUpdateCommand(array $dto, string $id): UpdateCellphoneCommand
    {
        return new UpdateCellphoneCommand(
            id: $id,
            brand: $dto['brand'] ?? null,
            //imei: $dto['imei'] ?? null,
            screenSize: isset($dto['screen_size']) ? (float)$dto['screen_size'] : null,
            megapixels: isset($dto['megapixels']) ? (float)$dto['megapixels'] : null,
            ramMb: isset($dto['ram_mb']) ? (int)$dto['ram_mb'] : null,
            storagePrimaryMb: isset($dto['storage_primary_mb']) ? (int)$dto['storage_primary_mb'] : null,
            storageSecondaryMb: isset($dto['storage_secondary_mb']) ? (int)$dto['storage_secondary_mb'] : null,
            operatingSystem: $dto['operating_system'] ?? null,
            operator: $dto['operator'] ?? null,
            networkTechnology: $dto['network_technology'] ?? null,
            wifi: isset($dto['wifi']) ? (bool)$dto['wifi'] : null,
            bluetooth: isset($dto['bluetooth']) ? (bool)$dto['bluetooth'] : null,
            cameraCount: isset($dto['camera_count']) ? (int)$dto['camera_count'] : null,
            cpuBrand: $dto['cpu_brand'] ?? null,
            cpuSpeedGhz: isset($dto['cpu_speed_ghz']) ? (float)$dto['cpu_speed_ghz'] : null,
            nfc: isset($dto['nfc']) ? (bool)$dto['nfc'] : null,
            fingerprint: isset($dto['fingerprint']) ? (bool)$dto['fingerprint'] : null,
            ir: isset($dto['ir']) ? (bool)$dto['ir'] : null,
            waterResistant: isset($dto['water_resistant']) ? (bool)$dto['water_resistant'] : null,
            simCount: isset($dto['sim_count']) ? (int)$dto['sim_count'] : null,
        );
    }

}

