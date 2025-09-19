<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\DeleteUserUseCase;
use App\Application\Users\Dto\Command\DeleteUserCommand;
use App\Application\Users\Port\Out\UserRepositoryPort as OutUserRepository;
use App\Application\Users\Port\Out\UnitOfWorkPort as UnitOfWork;
use App\Domain\Users\ValueObject\UserId;

final class DeleteUserService implements DeleteUserUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private UnitOfWork $uow
    ) {}

    public function execute(DeleteUserCommand $command): void
    {
        $this->uow->transactional(function() use ($command) {
            $user = $this->userRepository->findById(UserId::fromString($command->userId));
            if ($user === null) {
                throw new \RuntimeException('User not found');
            }
            $this->userRepository->remove($user);
        });
    }
}
