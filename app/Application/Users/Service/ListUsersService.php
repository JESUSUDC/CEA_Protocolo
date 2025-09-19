<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\ListUsersUseCase;
use App\Application\Users\Dto\Query\ListUserQuery;
use App\Application\Users\Response\UserListResponse;
use App\Application\Users\Mapper\UserMapper;
use App\Application\Users\Port\Out\UserRepositoryPort as OutUserRepository;

final class ListUsersService implements ListUsersUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private UserMapper $mapper
    ) {}

    public function execute(ListUserQuery $query): UserListResponse
    {
        $users = $this->userRepository->findAll($query->limit, $query->offset);
        $items = $this->mapper->toResponses($users);
        // repository could provide count; for now use count(items)
        return new UserListResponse($items, count($items));
    }
}
