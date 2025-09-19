<?php
declare(strict_types=1);

namespace Application\Users\Port\In;

use Application\Users\Dto\Query\GetUserByIdQuery;
use Application\Users\Response\UserResponse;

interface GetUserByIdUseCase
{
    public function execute(GetUserByIdQuery $query): ?UserResponse;
}
