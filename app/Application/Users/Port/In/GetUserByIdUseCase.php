<?php
declare(strict_types=1);

namespace App\Application\Users\Port\In;

use App\Application\Users\Dto\Query\GetUserByIdQuery;
use App\Application\Users\Dto\Response\UserResponse;

interface GetUserByIdUseCase
{
    public function execute(GetUserByIdQuery $query): ?UserResponse;
}
