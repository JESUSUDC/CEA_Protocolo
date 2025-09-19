<?php
declare(strict_types=1);

namespace Application\Cellphone\Service;

use Application\Cellphone\Port\In\UpdateCellphoneUseCase;
use Application\Cellphone\Dto\Command\UpdateCellphoneCommand;
use Application\Cellphone\Port\Out\CellphoneRepositoryPort;
use Application\Port\Out\UnitOfWorkPort;
use Domain\Cellphone\ValueObject\Brand;
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

final class UpdateCellphoneService implements UpdateCellphoneUseCase
{
    public function __construct(
        private CellphoneRepositoryPort $repo,
        private UnitOfWorkPort $uow
    ) {}

    public function execute(UpdateCellphoneCommand $command): void
    {
        $this->uow->begin();
        try {
            $cell = $this->repo->findById($command->id);
            if ($cell === null) {
                throw new \RuntimeException('Cellphone not found');
            }

            // create optional VOs when fields provided
            $brand = $command->brand !== null ? Brand::fromString($command->brand) : null;
            $screen = $command->screenSize !== null ? ScreenSize::fromFloat($command->screenSize) : null;
            $mp = $command->megapixels !== null ? Megapixels::fromFloat($command->megapixels) : null;
            $ram = $command->ramMb !== null ? RAM::fromMegabytes($command->ramMb) : null;
            $primary = $command->storagePrimaryMb !== null ? Storage::fromMegabytes($command->storagePrimaryMb) : null;
            $secondary = $command->storageSecondaryMb !== null ? Storage::fromMegabytes($command->storageSecondaryMb) : null;
            $os = $command->operatingSystem !== null ? OperatingSystem::fromString($command->operatingSystem) : null;
            $operator = $command->operator !== null ? OperatorVO::fromString($command->operator) : null;
            $net = $command->networkTechnology !== null ? NetworkTechnology::fromString($command->networkTechnology) : null;
            $conn = ($command->wifi !== null || $command->bluetooth !== null)
                ? Connectivity::fromBooleans($command->wifi ?? $cell->connectivity()->hasWifi(), $command->bluetooth ?? $cell->connectivity()->hasBluetooth())
                : null;
            $cams = $command->cameraCount !== null ? Cameras::fromInt($command->cameraCount) : null;
            $cpu = ($command->cpuBrand !== null || $command->cpuSpeedGhz !== null)
                ? Cpu::from($command->cpuBrand ?? $cell->cpu()->brand(), $command->cpuSpeedGhz ?? $cell->cpu()->ghz())
                : null;
            $nfc = $command->nfc !== null ? BooleanFeature::fromBool($command->nfc) : null;
            $finger = $command->fingerprint !== null ? BooleanFeature::fromBool($command->fingerprint) : null;
            $ir = $command->ir !== null ? BooleanFeature::fromBool($command->ir) : null;
            $water = $command->waterResistant !== null ? BooleanFeature::fromBool($command->waterResistant) : null;
            $sims = $command->simCount !== null ? SimCount::fromInt($command->simCount) : null;

            $cell->updateSpecifications(
                $brand,
                $screen,
                $mp,
                $ram,
                $primary,
                $secondary,
                $os,
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

            $this->repo->save($cell);
            $this->uow->commit();
        } catch (\Throwable $e) {
            $this->uow->rollback();
            throw $e;
        }
    }
}
