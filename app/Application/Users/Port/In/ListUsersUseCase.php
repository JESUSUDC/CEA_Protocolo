<?php
declare(strict_types=1);

namespace App\Application\Users\Port\In;

use App\Application\Users\Dto\Query\ListUserQuery;
use App\Application\Users\Dto\Response\UserListResponse;

interface ListUsersUseCase
{
    public function execute(ListUserQuery $query): UserListResponse;
}
