<?php
declare(strict_types=1);

namespace Application\Cellphone\Port\In;

use Application\Cellphone\Dto\Query\ListCellphonesQuery;
use Application\Cellphone\Response\CellphoneListResponse;

interface ListCellphonesUseCase
{
    public function execute(ListCellphonesQuery $query): CellphoneListResponse;
}
