<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Mapper;

use App\Domain\Cellphone\Entity\Cellphone;
use App\Application\Cellphone\Dto\Response\CellphoneResponse;

final class CellphoneMapper
{
    public function toResponse(Cellphone $c): CellphoneResponse
    {
        return new CellphoneResponse(
            id: $c->id()->toString(),
            brand: $c->brand()->toString(),
            imei: $c->imei()->toString(),
            screenSize: $c->screenSize()->toFloat(),
            megapixels: $c->megapixels()->toFloat(),
            ramMb: $c->ram()->toMegabytes(),
            storagePrimaryMb: $c->primaryStorage()->toMegabytes(),
            storageSecondaryMb: $c->secondaryStorage()?->toMegabytes(),
            operatingSystem: $c->os()->toString(),
            operator: $c->operator()?->toString(),
            networkTechnology: $c->networkTechnology()->toString(),
            wifi: $c->connectivity()->hasWifi(),
            bluetooth: $c->connectivity()->hasBluetooth(),
            cameraCount: $c->cameras()->toInt(),
            cpuBrand: $c->cpu()->brand(),
            cpuSpeedGhz: $c->cpu()->ghz(),
            nfc: $c->nfc()->value(),
            fingerprint: $c->fingerprint()->value(),
            ir: $c->ir()->value(),
            waterResistant: $c->waterResistant()->value(),
            simCount: $c->simCount()->toInt()
        );
    }

    /**
     * @param Cellphone[] $cells
     * @return CellphoneResponse[]
     */
    public function toResponses(array $cells): array
    {
        return array_map(fn(Cellphone $c) => $this->toResponse($c), $cells);
    }
}
