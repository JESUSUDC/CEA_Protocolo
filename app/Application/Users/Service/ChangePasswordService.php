<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\ChangePasswordUseCase;
use App\Application\Users\Dto\Command\ChangePasswordCommand;
use App\Application\Users\Port\Out\UserRepositoryPort as OutUserRepository;
use App\Application\Users\Port\Out\UnitOfWorkPort as UnitOfWork;
use App\Domain\Users\ValueObject\UserId;
use App\Domain\Users\Service\Contracts\PasswordHasher;
use App\Domain\Users\Service\Contracts\PasswordStrengthEvaluator;

final class ChangePasswordService implements ChangePasswordUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private PasswordHasher $hasher,
        private PasswordStrengthEvaluator $strengthEvaluator,
        private UnitOfWork $uow
    ) {}

    public function execute(ChangePasswordCommand $command): void
    {
        if (!$this->strengthEvaluator->isStrongEnough($command->newPassword)) {
            throw new \InvalidArgumentException('New password does not meet strength policy.');
        }

        $this->uow->begin();
        try {
            $user = $this->userRepository->findById($command->userId);
            if ($user === null) {
                throw new \RuntimeException('User not found');
            }

            // verify current password
            if (!$this->hasher->verify($command->currentPassword, $user->passwordHash())) {
                throw new \RuntimeException('Current password is invalid.');
            }

            $newHash = $this->hasher->hash($command->newPassword);
            $user->changePassword($newHash);
            $this->userRepository->save($user);

            $this->uow->commit();
        } catch (\Throwable $e) {
            $this->uow->rollback();
            throw $e;
        }
    }
}
