<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\GetUserByIdUseCase;
use App\Application\Users\Dto\Query\GetUserByIdQuery;
use App\Application\Users\Response\UserResponse;
use App\Application\Users\Mapper\UserMapper;
use App\Application\Users\Port\Out\UserRepositoryPort as OutUserRepository;
use App\Domain\Users\ValueObject\UserId;

final class GetUserByIdService implements GetUserByIdUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private UserMapper $mapper
    ) {}

    public function execute(GetUserByIdQuery $query): ?UserResponse
    {
        $user = $this->userRepository->findById($query->userId);
        if ($user === null) {
            return null;
        }
        return $this->mapper->toResponse($user);
    }
}
