<?php
declare(strict_types=1);

namespace Application\Cellphone\Service;

use Application\Cellphone\Port\In\ListCellphonesUseCase;
use Application\Cellphone\Dto\Query\ListCellphonesQuery;
use Application\Cellphone\Response\CellphoneListResponse;
use Application\Cellphone\Mapper\CellphoneMapper;
use Application\Cellphone\Port\Out\CellphoneRepositoryPort;

final class ListCellphonesService implements ListCellphonesUseCase
{
    public function __construct(
        private CellphoneRepositoryPort $repo,
        private CellphoneMapper $mapper
    ) {}

    public function execute(ListCellphonesQuery $query): CellphoneListResponse
    {
        $cells = $this->repo->listAll($query->limit, $query->offset);
        $items = $this->mapper->toResponses($cells);
        return new CellphoneListResponse($items, count($items));
    }
}
