<?php
declare(strict_types=1);

namespace Application\Users\Service;

use Application\Users\Port\In\GetUserByIdUseCase;
use Application\Users\Dto\Query\GetUserByIdQuery;
use Application\Users\Response\UserResponse;
use Application\Users\Mapper\UserMapper;
use Application\Users\Port\Out\UserRepositoryPort as OutUserRepository;
use Domain\Users\ValueObject\UserId;

final class GetUserByIdService implements GetUserByIdUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private UserMapper $mapper
    ) {}

    public function execute(GetUserByIdQuery $query): ?UserResponse
    {
        $user = $this->userRepository->findById(UserId::fromString($query->userId));
        if ($user === null) {
            return null;
        }
        return $this->mapper->toResponse($user);
    }
}
