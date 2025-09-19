<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Cellphones\Mapper;

use Domain\Cellphone\Entity\Cellphone;
use Illuminate\Http\Request;

final class CellphoneHttpMapper
{
    public function toRegisterCommand(array $dto): array
    {
        // This returns an array / command that Application usecase expects.
        // Prefer creating a proper DTO/Command class in application/port/in
        return [
            'id' => $dto['id'] ?? \Illuminate\Support\Str::uuid()->toString(),
            'brand' => $dto['brand'],
            'imei' => $dto['imei'],
            'screen_size' => (float)$dto['screen_size'],
            'megapixels' => (float)$dto['megapixels'],
            'ram_mb' => (int)$dto['ram_mb'],
            'storage_primary_mb' => (int)$dto['storage_primary_mb'],
            'storage_secondary_mb' => isset($dto['storage_secondary_mb']) ? (int)$dto['storage_secondary_mb'] : null,
            'operating_system' => $dto['operating_system'],
            'operator' => $dto['operator'] ?? null,
            'network_technology' => $dto['network_technology'],
            'wifi' => (bool)($dto['wifi'] ?? false),
            'bluetooth' => (bool)($dto['bluetooth'] ?? false),
            'camera_count' => (int)$dto['camera_count'],
            'cpu_brand' => $dto['cpu_brand'],
            'cpu_speed_ghz' => (float)$dto['cpu_speed_ghz'],
            'nfc' => (bool)($dto['nfc'] ?? false),
            'fingerprint' => (bool)($dto['fingerprint'] ?? false),
            'ir' => (bool)($dto['ir'] ?? false),
            'water_resistant' => (bool)($dto['water_resistant'] ?? false),
            'sim_count' => (int)$dto['sim_count'],
        ];
    }

    public function toHttp(Cellphone $c): array
    {
        return [
            'id' => $c->id()->toString(),
            'brand' => $c->brand()->toString(),
            'imei' => $c->imei()->toString(),
            'screen_size' => $c->screenSize()->toFloat(),
            'megapixels' => $c->megapixels()->toFloat(),
            'ram_mb' => $c->ram()->toMegabytes(),
            'storage_primary_mb' => $c->primaryStorage()->toMegabytes(),
            'storage_secondary_mb' => $c->secondaryStorage()?->toMegabytes(),
            'operating_system' => $c->os()->toString(),
            'operator' => $c->operator()?->toString(),
            'network_technology' => $c->networkTechnology()->toString(),
            'wifi' => $c->connectivity()->hasWifi(),
            'bluetooth' => $c->connectivity()->hasBluetooth(),
            'camera_count' => $c->cameras()->toInt(),
            'cpu_brand' => $c->cpu()->brand(),
            'cpu_speed_ghz' => $c->cpu()->ghz(),
            'nfc' => $c->nfc()->value(),
            'fingerprint' => $c->fingerprint()->value(),
            'ir' => $c->ir()->value(),
            'water_resistant' => $c->waterResistant()->value(),
            'sim_count' => $c->simCount()->toInt(),
        ];
    }
}
