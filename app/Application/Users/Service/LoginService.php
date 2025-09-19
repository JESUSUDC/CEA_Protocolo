<?php
declare(strict_types=1);

namespace Application\Users\Service;

use Application\Users\Port\In\LoginUseCase;
use Application\Users\Port\Out\UserRepository as OutUserRepository;
use Domain\Users\Service\Contracts\PasswordHasher;
use Application\Security\JwtTokenIssuer;

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
