<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Service;

use App\Application\Cellphone\Port\In\ListCellphonesUseCase;
use App\Application\Cellphone\Dto\Query\ListCellphonesQuery;
use App\Application\Cellphone\Dto\Response\CellphoneListResponse;
use App\Application\Cellphone\Mapper\CellphoneMapper;
use App\Application\Cellphone\Port\Out\CellphoneRepositoryPort;

final class ListCellphoneService implements ListCellphonesUseCase
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

    /*public function Hola(string $query) : string
    {
        return "Hola ".$query;
    }*/
}
