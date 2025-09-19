<?php
declare(strict_types=1);

namespace Application\Users\Service;

use Application\Users\Port\In\ListUsersUseCase;
use Application\Users\Dto\Query\ListUserQuery;
use Application\Users\Response\UserListResponse;
use Application\Users\Mapper\UserMapper;
use Application\Users\Port\Out\UserRepository as OutUserRepository;

final class ListUsersService implements ListUsersUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private UserMapper $mapper
    ) {}

    public function execute(ListUserQuery $query): UserListResponse
    {
        $users = $this->userRepository->listAll($query->limit, $query->offset);
        $items = $this->mapper->toResponses($users);
        // repository could provide count; for now use count(items)
        return new UserListResponse($items, count($items));
    }
}
