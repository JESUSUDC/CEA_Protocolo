<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\CreateUserUseCase;
use App\Application\Users\Dto\Command\CreateUserCommand;
use App\Application\Users\Port\Out\UserRepositoryPort as OutUserRepository;
use App\Application\Users\Port\Out\UnitOfWorkPort as UnitOfWork;
use App\Domain\Users\ValueObject\UserId;
use App\Domain\Users\ValueObject\UserName;
use App\Domain\Users\ValueObject\Role;
use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\ValueObject\PasswordHash;
use App\Domain\Users\Entity\User;
use App\Domain\Users\Service\Contracts\PasswordHasher;
use App\Domain\Users\Service\Contracts\PasswordStrengthEvaluator;

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

        // Transactional save using begin/commit/rollback
        $this->uow->begin();
        try {
            $this->userRepository->save($user);
            $this->uow->commit();
        } catch (\Throwable $e) {
            $this->uow->rollback();
            throw $e;
        }

        return $userId->toString();
    }

    private function generateId(): string
    {
        // domain-agnostic random id (UUID-like) - simple and deterministic
        return bin2hex(random_bytes(16));
    }
}
