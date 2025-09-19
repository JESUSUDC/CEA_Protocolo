<?php
declare(strict_types=1);

namespace App\Infrastructure\Adapters\Database\Eloquent\Repository;

use App\Infrastructure\Adapters\Database\Eloquent\Model\Cellphone as CellphoneModel;
use App\Domain\Cellphone\ValueObject\CellphoneId;
use App\Domain\Cellphone\ValueObject\Brand;
use App\Domain\Cellphone\ValueObject\Imei;
use App\Domain\Cellphone\ValueObject\ScreenSize;
use App\Domain\Cellphone\ValueObject\Megapixels;
use App\Domain\Cellphone\ValueObject\RAM;
use App\Domain\Cellphone\ValueObject\Storage;
use App\Domain\Cellphone\ValueObject\OperatingSystem;
use App\Domain\Cellphone\ValueObject\Operator as OperatorVO;
use App\Domain\Cellphone\ValueObject\NetworkTechnology;
use App\Domain\Cellphone\ValueObject\Connectivity;
use App\Domain\Cellphone\ValueObject\Cameras;
use App\Domain\Cellphone\ValueObject\Cpu;
use App\Domain\Cellphone\ValueObject\BooleanFeature;
use App\Domain\Cellphone\ValueObject\SimCount;
use App\Domain\Cellphone\Entity\Cellphone as CellphoneEntity;
use Illuminate\Support\Collection;
use Carbon\Carbon;

// NOTE: adapt the implemented interface name to your project
use Application\Cellphone\Port\Out\CellphoneRepositoryPort;

final class EloquentCellphoneRepositoryAdapter implements CellphoneRepositoryPort
{
    public function __construct(private CellphoneModel $model)
    {
    }

    public function save(CellphoneEntity $cellphone): void
    {
        $m = $this->toModel($cellphone);
        // upsert (update or create)
        /** @var CellphoneModel $existing */
        $existing = CellphoneModel::find($cellphone->id()->toString());
        if ($existing) {
            $existing->fill($m->toArray());
            $existing->save();
        } else {
            $m->save();
        }
    }

    public function findById(CellphoneId $id): ?CellphoneEntity
    {
        $m = CellphoneModel::find($id->toString());
        if (!$m) {
            return null;
        }
        return $this->toDomain($m);
    }

    public function listAll(int $limit = 50, int $offset = 0): array
    {
        $models = CellphoneModel::query()
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $models->map(fn($m) => $this->toDomain($m))->all();
    }

    public function delete(CellphoneEntity $cellphone): void
    {
        CellphoneModel::destroy($cellphone->id()->toString());
    }

    private function toModel(CellphoneEntity $cellphone): CellphoneModel
    {
        $m = new CellphoneModel();
        $m->id = $cellphone->id()->toString();
        $m->brand = $cellphone->brand()->toString();
        $m->imei = $cellphone->imei()->toString();
        $m->screen_size = $cellphone->screenSize()->toFloat();
        $m->megapixels = $cellphone->megapixels()->toFloat();
        $m->ram_mb = $cellphone->ram()->toMegabytes();
        $m->storage_primary_mb = $cellphone->primaryStorage()->toMegabytes();
        $m->storage_secondary_mb = $cellphone->secondaryStorage() ? $cellphone->secondaryStorage()->toMegabytes() : null;
        $m->operating_system = $cellphone->os()->toString();
        $m->operator = $cellphone->operator()?->toString();
        $m->network_technology = $cellphone->networkTechnology()->toString();
        $m->wifi = $cellphone->connectivity()->hasWifi();
        $m->bluetooth = $cellphone->connectivity()->hasBluetooth();
        $m->camera_count = $cellphone->cameras()->toInt();
        $m->cpu_brand = $cellphone->cpu()->brand();
        $m->cpu_speed_ghz = $cellphone->cpu()->ghz();
        $m->nfc = $cellphone->nfc()->value();
        $m->fingerprint = $cellphone->fingerprint()->value();
        $m->ir = $cellphone->ir()->value();
        $m->water_resistant = $cellphone->waterResistant()->value();
        $m->sim_count = $cellphone->simCount()->toInt();
        $m->updated_at = Carbon::now();
        return $m;
    }

    private function toDomain(CellphoneModel $m): CellphoneEntity
    {
        // map Eloquent model -> Domain VO and Entity via register factory
        return CellphoneEntity::register(
            CellphoneId::fromString($m->id),
            Brand::fromString($m->brand),
            Imei::fromString($m->imei),
            ScreenSize::fromFloat((float)$m->screen_size),
            Megapixels::fromFloat((float)$m->megapixels),
            RAM::fromMegabytes((int)$m->ram_mb),
            Storage::fromMegabytes((int)$m->storage_primary_mb),
            $m->storage_secondary_mb !== null ? Storage::fromMegabytes((int)$m->storage_secondary_mb) : null,
            OperatingSystem::fromString($m->operating_system),
            $m->operator ? OperatorVO::fromString($m->operator) : null,
            NetworkTechnology::fromString($m->network_technology),
            Connectivity::fromBooleans((bool)$m->wifi, (bool)$m->bluetooth),
            Cameras::fromInt((int)$m->camera_count),
            Cpu::from($m->cpu_brand, (float)$m->cpu_speed_ghz),
            BooleanFeature::fromBool((bool)$m->nfc),
            BooleanFeature::fromBool((bool)$m->fingerprint),
            BooleanFeature::fromBool((bool)$m->ir),
            BooleanFeature::fromBool((bool)$m->water_resistant),
            SimCount::fromInt((int)$m->sim_count),
        );
    }
}
