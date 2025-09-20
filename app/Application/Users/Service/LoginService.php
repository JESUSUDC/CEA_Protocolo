<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\LoginUseCase;
use App\Application\Users\Port\Out\UserRepositoryPort;
use App\Application\Users\Port\Out\PasswordHasherPort;
use App\Application\Users\Port\Out\TokenIssuerPort;

final class LoginService implements LoginUseCase
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private PasswordHasherPort $hasher,
        private TokenIssuerPort $tokenIssuer
    ) {}

    public function execute(string $usernameOrEmail, string $password): array
    {
        $user = $this->userRepository->findByUsername($usernameOrEmail)
            ?? $this->userRepository->findByEmail($usernameOrEmail);

        if ($user === null) {
            throw new \RuntimeException('Invalid credentials');
        }

        if (!$this->hasher->verify($password, $user->passwordHash()->toString())) {
            throw new \RuntimeException('Invalid credentials');
        }

        if (!$user->isActive()) {
            throw new \RuntimeException('User is inactive');
        }

        $claims = [
            'sub' => $user->id()->toString(),
            'username' => $user->username()->toString(),
            'role' => $user->role()->toString(),
        ];

        return [
            'access_token' => $this->tokenIssuer->issueAccessToken($claims),
            'refresh_token' => $this->tokenIssuer->issueRefreshToken($claims),
            'token_type' => 'bearer',
            'expires_in' => 3600
        ];
    }
}