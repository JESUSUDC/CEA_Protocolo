<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\UpdateUserUseCase;
use App\Application\Users\Dto\Command\UpdateUserCommand;
use App\Application\Users\Port\Out\UserRepositoryPort as OutUserRepository;
use App\Application\Users\Port\Out\UnitOfWorkPort as UnitOfWork;
use App\Domain\Users\Exception\DomainException;
use App\Domain\Users\Exception\EmailAlreadyExists;
use App\Domain\Users\Exception\UsernameAlreadyExists;
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
                throw new DomainException('User not found'); // or create NotFoundUserException
            }

            if ($command->username !== null) {
                $user2 = $this->userRepository->findByUsername($command->username);
                if ($user2 && $user2->id()->toString() !== $command->userId) {
                    throw new UsernameAlreadyExists($command->username);
                }
            }

            if ($command->email !== null) {
                $user2 = $this->userRepository->findByEmail($command->email);
                if ($user2 && $user2->id()->toString() !== $command->userId) {
                    throw new EmailAlreadyExists($command->email);
                }
            }

            // Delegamos a la entidad: construir un array de cambios y aplicar
            $changes = [
                'name' => $command->name ?? null,
                'username' => $command->username ?? null,
                'email' => $command->email ?? null,
                'role' => $command->role ?? null,
            ];

            $user->updateProfile($changes);

            $this->userRepository->update($user);
            $this->uow->commit();
        } catch (\Throwable $e) {
            $this->uow->rollback();
            throw $e;
        }
    }
}
