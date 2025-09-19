<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Port\In;

use App\Application\Cellphone\Dto\Query\GetCellphoneByIdQuery;
use App\Application\Cellphone\Dto\Response\CellphoneResponse;

interface GetCellphoneByIdUseCase
{
    public function execute(GetCellphoneByIdQuery $query): ?CellphoneResponse;
}
