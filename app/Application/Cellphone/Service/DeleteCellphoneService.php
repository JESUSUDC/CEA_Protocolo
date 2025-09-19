<?php
declare(strict_types=1);

namespace Application\Cellphone\Service;

use Application\Cellphone\Port\In\DeleteCellphoneUseCase;
use Application\Cellphone\Dto\Command\DeleteCellphoneCommand;
use Application\Cellphone\Port\Out\CellphoneRepositoryPort;
use Application\Port\Out\UnitOfWorkPort;
use Domain\Cellphone\ValueObject\CellphoneId;

final class DeleteCellphoneService implements DeleteCellphoneUseCase
{
    public function __construct(
        private CellphoneRepositoryPort $repo,
        private UnitOfWorkPort $uow
    ) {}

    public function execute(DeleteCellphoneCommand $command): void
    {
        $this->uow->begin();
        try {
            $cell = $this->repo->findById($command->id);
            if ($cell === null) {
                throw new \RuntimeException('Cellphone not found');
            }
            $this->repo->remove($cell);
            $this->uow->commit();
        } catch (\Throwable $e) {
            $this->uow->rollback();
            throw $e;
        }
    }
}
