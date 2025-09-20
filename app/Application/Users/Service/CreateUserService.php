<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\CreateUserUseCase;
use App\Application\Users\Dto\Command\CreateUserCommand;
use App\Application\Users\Port\Out\UserRepositoryPort;
use App\Application\Users\Port\Out\PasswordHasherPort;
use App\Application\Users\Port\Out\PasswordStrengthPolicyPort;
use App\Application\Users\Port\Out\UnitOfWorkPort;
use App\Domain\Users\ValueObject\UserId;
use App\Domain\Users\ValueObject\UserName;
use App\Domain\Users\ValueObject\Role;
use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\ValueObject\PasswordHash;
use App\Domain\Users\Entity\User;

final class CreateUserService implements CreateUserUseCase
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private PasswordHasherPort $hasher,  // ✅ Interfaz de APLICACIÓN
        private PasswordStrengthPolicyPort $strengthEvaluator,  // ✅ Interfaz de APLICACIÓN
        private UnitOfWorkPort $uow
    ) {}

    public function execute(CreateUserCommand $command): string
    {
        // Validate password strength
        if (!$this->strengthEvaluator->isStrong($command->password)) {
            throw new \InvalidArgumentException('Password does not meet strength requirements.');
        }

        $id = $command->id ?? $this->generateId();

        $userId = UserId::fromString($id);
        $nameVo = UserName::fromString($command->name);
        $roleVo = Role::fromString($command->role);
        $emailVo = Email::fromString($command->email);
        $usernameVo = UserName::fromString($command->username);

        // Hash password y crear Value Object
        $hashedPassword = $this->hasher->hash($command->password);
        $passwordHashVo = PasswordHash::fromHash($hashedPassword);

        $user = User::register(
            $userId,
            $nameVo,
            $roleVo,
            $emailVo,
            $usernameVo,
            $passwordHashVo
        );

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
        return bin2hex(random_bytes(16));
    }
}