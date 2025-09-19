<?php
declare(strict_types=1);

namespace Application\Users\Service;

use Application\Users\Port\In\CreateUserUseCase;
use Application\Users\Dto\Command\CreateUserCommand;
use Application\Users\Port\Out\UserRepository as OutUserRepository;
use Application\Port\Out\UnitOfWork;
use Domain\Users\ValueObject\UserId;
use Domain\Users\ValueObject\UserName;
use Domain\Users\ValueObject\Role;
use Domain\Users\ValueObject\Email;
use Domain\Users\ValueObject\PasswordHash;
use Domain\Users\Entity\User;
use Domain\Users\Service\Contracts\PasswordHasher;
use Domain\Users\Service\Contracts\PasswordStrengthEvaluator;

final class CreateUserService implements CreateUserUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private PasswordHasher $hasher,
        private PasswordStrengthEvaluator $strengthEvaluator,
        private UnitOfWork $uow
    ) {}

    public function execute(CreateUserCommand $command): string
    {
        // Validate password strength (application-level policy)
        if (!$this->strengthEvaluator->isStrongEnough($command->password)) {
            throw new \InvalidArgumentException('Password does not meet strength requirements.');
        }

        // Generate ID if not present
        $id = $command->id ?? $this->generateId();

        $userId = UserId::fromString($id);
        $nameVo = UserName::fromString($command->name);
        $roleVo = Role::fromString($command->role);
        $emailVo = Email::fromString($command->email);

        // Hash password using domain contract
        $passwordHash = $this->hasher->hash($command->password);

        $user = User::register(
            $userId,
            $nameVo,
            $roleVo,
            $emailVo,
            UserName::fromString($command->username),
            $passwordHash
        );

        // Transactional save
        $this->uow->transactional(function() use ($user) {
            $this->userRepository->save($user);
        });

        return $userId->toString();
    }

    private function generateId(): string
    {
        // domain-agnostic random id (UUID-like) - simple and deterministic
        return bin2hex(random_bytes(16));
    }
}
