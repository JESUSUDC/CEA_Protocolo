<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\ChangePasswordUseCase;
use App\Application\Users\Dto\Command\ChangePasswordCommand;
use App\Application\Users\Port\Out\PasswordHasherPort;
use App\Application\Users\Port\Out\PasswordStrengthPolicyPort;
use App\Application\Users\Port\Out\UserRepositoryPort as OutUserRepository;
use App\Application\Users\Port\Out\UnitOfWorkPort;
use App\Domain\Users\Exception\DomainException;
use App\Domain\Users\Exception\InvalidPassword;
use App\Domain\Users\ValueObject\PasswordHash;
use App\Domain\Users\ValueObject\UserId;

final class ChangePasswordService implements ChangePasswordUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private PasswordHasherPort $hasher,
        private PasswordStrengthPolicyPort $strengthEvaluator,
        private UnitOfWorkPort $uow
    ) {}

    public function execute(ChangePasswordCommand $command): void
    {
        if (!$this->strengthEvaluator->isStrong($command->newPassword)) {
            throw new \InvalidArgumentException('New password does not meet strength policy.');
        }

        $this->uow->begin();
        try {
            $user = $this->userRepository->findById($command->userId);
            if ($user === null) {
                throw new DomainException('User not found');
            }

            // verify current password
            if (!$this->hasher->verify($command->currentPassword, $user->passwordHash()->toString())) {
                throw new InvalidPassword();
            }

            // Hash password y crear Value Object
            $hashedPassword = $this->hasher->hash($command->newPassword);
            $passwordHashVo = PasswordHash::fromHash($hashedPassword);

            $user->changePassword($passwordHashVo);
            $this->userRepository->update($user);

            $this->uow->commit();
        } catch (\Throwable $e) {
            $this->uow->rollback();
            throw $e;
        }
    }
}
