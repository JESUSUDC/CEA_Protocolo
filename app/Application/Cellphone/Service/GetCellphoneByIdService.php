<?php
declare(strict_types=1);

namespace Application\Cellphone\Service;

use Application\Cellphone\Port\In\GetCellphoneByIdUseCase;
use Application\Cellphone\Dto\Query\GetCellphoneByIdQuery;
use Application\Cellphone\Response\CellphoneResponse;
use Application\Cellphone\Mapper\CellphoneMapper;
use Application\Cellphone\Port\Out\CellphoneRepositoryPort;

final class GetCellphoneByIdService implements GetCellphoneByIdUseCase
{
    public function __construct(
        private CellphoneRepositoryPort $repo,
        private CellphoneMapper $mapper
    ) {}

    public function execute(GetCellphoneByIdQuery $query): ?CellphoneResponse
    {
        $cell = $this->repo->findById($query->id);
        if ($cell === null) {
            return null;
        }
        return $this->mapper->toResponse($cell);
    }
}
