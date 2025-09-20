<?php
declare(strict_types=1);

namespace App\Application\Users\Service;

use App\Application\Users\Port\In\RefreshTokenUseCase;
use App\Application\Users\Port\Out\TokenIssuerPort;
use App\Application\Users\Port\Out\UserRepositoryPort;

final class RefreshTokenService implements RefreshTokenUseCase
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private TokenIssuerPort $tokenIssuer
    ) {}

    public function execute(string $refreshToken): array
    {
        if (!$this->tokenIssuer->validateRefreshToken($refreshToken)) {
            throw new \RuntimeException('Invalid refresh token');
        }

        $claims = $this->tokenIssuer->getClaimsFromToken($refreshToken);
        
        if (empty($claims['sub'])) {
            throw new \RuntimeException('Invalid token claims');
        }

        $user = $this->userRepository->findById($claims['sub']);
        
        if ($user === null || !$user->isActive()) {
            throw new \RuntimeException('User not found or inactive');
        }

        $accessClaims = [
            'sub' => $user->id()->toString(),
            'username' => $user->username()->toString(),
            'role' => $user->role()->toString(),
            'type' => 'access'
        ];

        return [
            'access_token' => $this->tokenIssuer->issueAccessToken($accessClaims),
            'token_type' => 'bearer',
            'expires_in' => 3600
        ];
    }
}