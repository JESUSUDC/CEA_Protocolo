<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Port\In;

use App\Application\Cellphone\Dto\Query\ListCellphonesQuery;
use App\Application\Cellphone\Dto\Response\CellphoneListResponse;

interface ListCellphonesUseCase
{
    public function execute(ListCellphonesQuery $query): CellphoneListResponse;
    //public function Hola(string $query) : string;
}
