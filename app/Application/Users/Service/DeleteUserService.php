<?php
declare(strict_types=1);

namespace Application\Users\Service;

use Application\Users\Port\In\DeleteUserUseCase;
use Application\Users\Dto\Command\DeleteUserCommand;
use Application\Users\Port\Out\UserRepository as OutUserRepository;
use Application\Port\Out\UnitOfWork;
use Domain\Users\ValueObject\UserId;

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
