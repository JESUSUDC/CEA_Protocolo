<?php
declare(strict_types=1);

namespace Application\Users\Service;

use Application\Users\Port\In\UpdateUserUseCase;
use Application\Users\Dto\Command\UpdateUserCommand;
use Application\Users\Port\Out\UserRepository as OutUserRepository;
use Application\Port\Out\UnitOfWork;
use Domain\Users\ValueObject\UserId;
use Domain\Users\ValueObject\UserName;
use Domain\Users\ValueObject\Role;
use Domain\Users\ValueObject\Email;

final class UpdateUserService implements UpdateUserUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private UnitOfWork $uow
    ) {}

    public function execute(UpdateUserCommand $command): void
    {
        $this->uow->transactional(function() use ($command) {
            $user = $this->userRepository->findById(UserId::fromString($command->userId));
            if ($user === null) {
                throw new \RuntimeException('User not found');
            }

            if ($command->name !== null) {
                $user->rename(UserName::fromString($command->name));
            }
            if ($command->role !== null) {
                $user->assignRole(Role::fromString($command->role));
            }
            if ($command->email !== null) {
                $user->changeEmail(Email::fromString($command->email));
            }
            if ($command->username !== null) {
                $user->rename(UserName::fromString($command->username)); // we used rename for username too; consider separate VO if needed
            }

            $this->userRepository->save($user);
        });
    }
}
