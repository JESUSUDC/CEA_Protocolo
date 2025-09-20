<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Service;

use App\Application\Cellphone\Port\In\UpdateCellphoneUseCase;
use App\Application\Cellphone\Dto\Command\UpdateCellphoneCommand;
use App\Application\Cellphone\Port\Out\CellphoneRepositoryPort;
use App\Application\Users\Port\Out\UnitOfWorkPort; // ✅ Corregido
use App\Domain\Cellphone\ValueObject\Brand;
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

final class UpdateCellphoneService implements UpdateCellphoneUseCase
{
    public function __construct(
        private CellphoneRepositoryPort $repo,
        private UnitOfWorkPort $uow
    ) {}

    public function execute(UpdateCellphoneCommand $command): void // ✅ Quita CellphoneResponse
    {
        $this->uow->begin();
        try {
            // 1. Buscar la entidad existente
            $cell = $this->repo->findById($command->id);
            if ($cell === null) {
                throw new \RuntimeException('Cellphone not found');
            }

            // 2. Crear VOs solo con los campos que vienen en el comando
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
                ? Connectivity::fromBooleans($command->wifi, $command->bluetooth)
                : null;
                
            $cams = $command->cameraCount !== null ? Cameras::fromInt($command->cameraCount) : null;
            $cpu = ($command->cpuBrand !== null || $command->cpuSpeedGhz !== null)
                ? Cpu::from($command->cpuBrand, $command->cpuSpeedGhz)
                : null;
                
            $nfc = $command->nfc !== null ? BooleanFeature::fromBool($command->nfc) : null;
            $finger = $command->fingerprint !== null ? BooleanFeature::fromBool($command->fingerprint) : null;
            $ir = $command->ir !== null ? BooleanFeature::fromBool($command->ir) : null;
            $water = $command->waterResistant !== null ? BooleanFeature::fromBool($command->waterResistant) : null;
            $sims = $command->simCount !== null ? SimCount::fromInt($command->simCount) : null;

            // 3. Actualizar la entidad existente
            $cell->updateSpecifications(
                $brand,
                null, // imei (si no viene en el comando)
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

            // 4. Actualizar operador por separado si viene
            if ($operator !== null) {
                $cell->assignOperator($operator);
            }

            // 5. Guardar
            $this->repo->update($cell);
            $this->uow->commit();
            
        } catch (\Throwable $e) {
            $this->uow->rollback();
            throw $e;
        }
    }
}