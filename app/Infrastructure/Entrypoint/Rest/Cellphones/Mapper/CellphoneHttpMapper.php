<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Cellphones\Mapper;

use App\Domain\Cellphone\Entity\Cellphone;
use Illuminate\Http\Request;
use App\Application\Cellphone\Dto\Command\CreateCellphoneCommand;
use App\Application\Cellphone\Dto\Command\UpdateCellphoneCommand;
use App\Application\Cellphone\Dto\Response\CellphoneResponse as DtoCellphoneResponse;
use App\Application\Cellphone\Dto\Response\CellphoneListResponse as DtoCellphoneListResponse;

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

    /**
     * Convierte un CellphoneResponse (DTO), array u objeto a array HTTP-friendly.
     *
     * @param mixed $c
     * @return array<string,mixed>
     */
    public function toHttp(mixed $c): array
    {
        // Si ya es array, devolver tal cual (asumimos shape correcto).
        if (is_array($c)) {
            return $this->normalizeArrayShape($c);
        }

        // Si implementa JsonSerializable, aprovecharlo.
        if (is_object($c) && $c instanceof \JsonSerializable) {
            return (array) $c->jsonSerialize();
        }

        // Si es un objeto (DTO o entidad con propiedades públicas), intentar mapear por propiedades públicas
        if (is_object($c)) {
            // Normalizar nombres: cuidamos camelCase vs snake_case
            $props = get_object_vars($c);

            // Si no existen propiedades, intentar método toArray si existe
            if (empty($props) && method_exists($c, 'toArray')) {
                $props = $c->toArray();
            }

            return $this->normalizeArrayShape($props);
        }

        // Fallback: devolver vacío
        return [];
    }

    /**
     * Convierte la lista de CellphoneResponse (o arrays) a la forma HTTP.
     *
     * Acepta:
     *  - array de items + total,
     *  - o una instancia de CellphoneListResponse (si JsonSerializable).
     *
     * @param array|mixed $itemsOrList
     * @param int|null $total
     * @return array<string,mixed>
     */
    public function toHttpList(mixed $itemsOrList, ?int $total = null): array
    {
        // Si recibimos la Dto list (ej. JsonSerializable), usarla directamente
        if (is_object($itemsOrList) && $itemsOrList instanceof \JsonSerializable) {
            $data = $itemsOrList->jsonSerialize();
            return [
                'items' => array_map(fn($it) => $this->toHttp($it), $data['items'] ?? []),
                'total' => (int) ($data['total'] ?? 0),
            ];
        }

        // Si pasaron items y total por separado
        if (is_array($itemsOrList) && $total !== null) {
            $mapped = array_map(fn($it) => $this->toHttp($it), $itemsOrList);
            return ['items' => $mapped, 'total' => $total];
        }

        // Si nos dieron un array con keys items/total
        if (is_array($itemsOrList) && array_key_exists('items', $itemsOrList)) {
            $mapped = array_map(fn($it) => $this->toHttp($it), $itemsOrList['items'] ?? []);
            return ['items' => $mapped, 'total' => (int) ($itemsOrList['total'] ?? 0)];
        }

        // Fallback vacío
        return ['items' => [], 'total' => 0];
    }

    public function toUpdateCommand(array $dto, string $id): UpdateCellphoneCommand
    {
        return new UpdateCellphoneCommand(
            id: $id,
            brand: $dto['brand'] ?? null,
            imei: $dto['imei'] ?? null,
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

    /**
     * Normaliza shape y nombres de propiedades para salida HTTP.
     *
     * - acepta camelCase o snake_case en el input.
     * - fuerza tipos primitivos donde aplica.
     *
     * @param array<string,mixed> $props
     * @return array<string,mixed>
     */
    private function normalizeArrayShape(array $props): array
    {
        // Helper para leer prop con fallback de nombres
        $get = function(array $p, array $keys, $default = null) {
            foreach ($keys as $k) {
                if (array_key_exists($k, $p)) {
                    return $p[$k];
                }
            }
            return $default;
        };

        return [
            'id' => (string) $get($props, ['id', 'ID', 'Id'], ''),
            'brand' => (string) $get($props, ['brand', 'brandName'], ''),
            'imei' => (string) $get($props, ['imei'], ''),
            'screen_size' => isset($props['screenSize']) ? (float)$props['screenSize'] : (isset($props['screen_size']) ? (float)$props['screen_size'] : null),
            'megapixels' => isset($props['megapixels']) ? (float)$props['megapixels'] : (isset($props['megapixels']) ? (float)$props['megapixels'] : null),
            'ram_mb' => isset($props['ramMb']) ? (int)$props['ramMb'] : (isset($props['ram_mb']) ? (int)$props['ram_mb'] : 0),
            'storage_primary_mb' => isset($props['storagePrimaryMb']) ? (int)$props['storagePrimaryMb'] : (isset($props['storage_primary_mb']) ? (int)$props['storage_primary_mb'] : 0),
            'storage_secondary_mb' => isset($props['storageSecondaryMb']) ? (int)$props['storageSecondaryMb'] : (isset($props['storage_secondary_mb']) ? (int)$props['storage_secondary_mb'] : null),
            'operating_system' => (string) $get($props, ['operatingSystem','operating_system'], ''),
            'operator' => $get($props, ['operator'], null),
            'network_technology' => (string) $get($props, ['networkTechnology','network_technology'], ''),
            'wifi' => (bool) $get($props, ['wifi'], false),
            'bluetooth' => (bool) $get($props, ['bluetooth'], false),
            'camera_count' => (int) $get($props, ['cameraCount','camera_count'], 0),
            'cpu_brand' => (string) $get($props, ['cpuBrand','cpu_brand'], ''),
            'cpu_speed_ghz' => isset($props['cpuSpeedGhz']) ? (float)$props['cpuSpeedGhz'] : (isset($props['cpu_speed_ghz']) ? (float)$props['cpu_speed_ghz'] : null),
            'nfc' => (bool) $get($props, ['nfc'], false),
            'fingerprint' => (bool) $get($props, ['fingerprint'], false),
            'ir' => (bool) $get($props, ['ir'], false),
            'water_resistant' => (bool) $get($props, ['waterResistant','water_resistant'], false),
            'sim_count' => (int) $get($props, ['simCount','sim_count'], 0),
        ];
    }
}
