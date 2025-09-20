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
        $this->uow->begin();
        try {
            $user = $this->userRepository->findById($command->userId);
            if ($user === null) {
                throw new \RuntimeException('User not found');
            }

            $user2 = $this->userRepository->findByUsername($command->username);
            if ($user2) {
                throw new \RuntimeException('El nombre de usuario ya está en uso.');
            }

            $user2 = $this->userRepository->findByEmail($command->email);
            if ($user2) {
                throw new \RuntimeException('El correo ya está en uso.');
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
                $user->rename(UserName::fromString($command->username)); // considera usar VO separado para username
            }

            $this->userRepository->update($user);
            $this->uow->commit();
        } catch (\Throwable $e) {
            $this->uow->rollback();
            throw $e;
        }
    }
}
