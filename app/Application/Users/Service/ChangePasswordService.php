<?php
declare(strict_types=1);

namespace Application\Users\Service;

use Application\Users\Port\In\ChangePasswordUseCase;
use Application\Users\Dto\Command\ChangePasswordCommand;
use Application\Users\Port\Out\UserRepository as OutUserRepository;
use Application\Port\Out\UnitOfWork;
use Domain\Users\ValueObject\UserId;
use Domain\Users\Service\Contracts\PasswordHasher;
use Domain\Users\Service\Contracts\PasswordStrengthEvaluator;

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

        $this->uow->transactional(function() use ($command) {
            $user = $this->userRepository->findById(UserId::fromString($command->userId));
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
        });
    }
}
