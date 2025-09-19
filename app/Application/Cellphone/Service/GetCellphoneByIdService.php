<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Service;

use App\Application\Cellphone\Port\In\GetCellphoneByIdUseCase;
use App\Application\Cellphone\Dto\Query\GetCellphoneByIdQuery;
use App\Application\Cellphone\Response\CellphoneResponse;
use App\Application\Cellphone\Mapper\CellphoneMapper;
use App\Application\Cellphone\Port\Out\CellphoneRepositoryPort;

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
