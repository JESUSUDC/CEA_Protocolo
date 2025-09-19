<?php
declare(strict_types=1);

namespace Application\Cellphone\Service;

use Application\Cellphone\Port\In\RegisterCellphoneUseCase;
use Application\Cellphone\Dto\Command\CreateCellphoneCommand;
use Application\Cellphone\Port\Out\CellphoneRepositoryPort;
use Application\Port\Out\UnitOfWorkPort;
use Domain\Cellphone\ValueObject\CellphoneId;
use Domain\Cellphone\ValueObject\Brand;
use Domain\Cellphone\ValueObject\Imei;
use Domain\Cellphone\ValueObject\ScreenSize;
use Domain\Cellphone\ValueObject\Megapixels;
use Domain\Cellphone\ValueObject\RAM;
use Domain\Cellphone\ValueObject\Storage;
use Domain\Cellphone\ValueObject\OperatingSystem;
use Domain\Cellphone\ValueObject\Operator as OperatorVO;
use Domain\Cellphone\ValueObject\NetworkTechnology;
use Domain\Cellphone\ValueObject\Connectivity;
use Domain\Cellphone\ValueObject\Cameras;
use Domain\Cellphone\ValueObject\Cpu;
use Domain\Cellphone\ValueObject\BooleanFeature;
use Domain\Cellphone\ValueObject\SimCount;
use Domain\Cellphone\Entity\Cellphone;

final class RegisterCellphoneService implements RegisterCellphoneUseCase
{
    public function __construct(
        private CellphoneRepositoryPort $repo,
        private UnitOfWorkPort $uow
    ) {}

    public function execute(CreateCellphoneCommand $command): string
    {
        // Create domain VOs
        $id = CellphoneId::fromString($command->id);
        $brand = Brand::fromString($command->brand);
        $imei = Imei::fromString($command->imei);
        $screen = ScreenSize::fromFloat($command->screenSize);
        $mp = Megapixels::fromFloat($command->megapixels);
        $ram = RAM::fromMegabytes($command->ramMb);
        $primary = Storage::fromMegabytes($command->storagePrimaryMb);
        $secondary = $command->storageSecondaryMb !== null ? Storage::fromMegabytes($command->storageSecondaryMb) : null;
        $os = OperatingSystem::fromString($command->operatingSystem);
        $operator = $command->operator !== null ? OperatorVO::fromString($command->operator) : null;
        $net = NetworkTechnology::fromString($command->networkTechnology);
        $conn = Connectivity::fromBooleans($command->wifi, $command->bluetooth);
        $cams = Cameras::fromInt($command->cameraCount);
        $cpu = Cpu::from($command->cpuBrand, $command->cpuSpeedGhz);
        $nfc = BooleanFeature::fromBool($command->nfc);
        $finger = BooleanFeature::fromBool($command->fingerprint);
        $ir = BooleanFeature::fromBool($command->ir);
        $water = BooleanFeature::fromBool($command->waterResistant);
        $sims = SimCount::fromInt($command->simCount);

        $cell = Cellphone::register(
            $id,
            $brand,
            $imei,
            $screen,
            $mp,
            $ram,
            $primary,
            $secondary,
            $os,
            $operator,
            $net,
            $conn,
            $cams,
            $cpu,
            $nfc,
            $finger,
            $ir,
            $water,
            $sims
        );

        // Persist in transaction
        $this->uow->begin();
        try {
            $this->repo->save($cell);
            $this->uow->commit();
        } catch (\Throwable $e) {
            $this->uow->rollback();
            throw $e;
        }

        return $id->toString();
    }
}
