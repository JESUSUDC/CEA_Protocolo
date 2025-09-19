<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\LoginUseCase;
use App\Application\Users\Port\Out\UserRepositoryPort as OutUserRepository;
use App\Domain\Users\Service\Contracts\PasswordHasher;
use App\Application\Security\JwtTokenIssuer;

final class LoginService implements LoginUseCase
{
    public function __construct(
        private OutUserRepository $userRepository,
        private PasswordHasher $hasher,
        private JwtTokenIssuer $jwtIssuer
    ) {}

    public function execute(string $usernameOrEmail, string $password): string
    {
        // repository provides findByUsername and findByEmail
        $user = $this->userRepository->findByUsername($usernameOrEmail)
            ?? $this->userRepository->findByEmail($usernameOrEmail);

        if ($user === null) {
            throw new \RuntimeException('Invalid credentials'); // avoid leaking whether user exists
        }

        if (!$this->hasher->verify($password, $user->passwordHash())) {
            throw new \RuntimeException('Invalid credentials');
        }

        if (!$user->isActive()) {
            throw new \RuntimeException('User is inactive');
        }

        // Issue JWT with minimal claims
        $claims = [
            'sub' => $user->id()->toString(),
            'username' => $user->username()->toString(),
            'role' => $user->role()->toString(),
        ];

        return $this->jwtIssuer->issue($claims);
    }
}
