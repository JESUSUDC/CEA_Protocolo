<?php
declare(strict_types=1);

namespace Application\Users\Port\In;

use Application\Users\Dto\Query\ListUserQuery;
use Application\Users\Response\UserListResponse;

interface ListUsersUseCase
{
    public function execute(ListUserQuery $query): UserListResponse;
}
