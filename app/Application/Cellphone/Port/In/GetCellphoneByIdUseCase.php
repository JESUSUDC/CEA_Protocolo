<?php
declare(strict_types=1);

namespace Application\Cellphone\Port\In;

use Application\Cellphone\Dto\Query\GetCellphoneByIdQuery;
use Application\Cellphone\Response\CellphoneResponse;

interface GetCellphoneByIdUseCase
{
    public function execute(GetCellphoneByIdQuery $query): ?CellphoneResponse;
}
