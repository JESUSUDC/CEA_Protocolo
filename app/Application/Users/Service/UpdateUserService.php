<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\UpdateUserUseCase;
use App\Application\Users\Dto\Command\UpdateUserCommand;
use App\Application\Users\Port\Out\UserRepositoryPort as OutUserRepository;
use App\Application\Users\Port\Out\UnitOfWorkPort as UnitOfWork;
use App\Domain\Users\ValueObject\UserId;
use App\Domain\Users\ValueObject\UserName;
use App\Domain\Users\ValueObject\Role;
use App\Domain\Users\ValueObject\Email;

final class UpdateUserService implements UpdateUserUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private UnitOfWork $uow
    ) {}

    public function execute(UpdateUserCommand $command): void
    {
        $this->uow->transactional(function() use ($command) {
            $user = $this->userRepository->findById($command->userId);
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
