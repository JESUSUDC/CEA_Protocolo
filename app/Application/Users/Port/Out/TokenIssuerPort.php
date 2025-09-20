<?php
declare(strict_types=1);

namespace App\Application\Users\Port\Out;

interface TokenIssuerPort
{
    public function issueAccessToken(array $claims): string;
    public function issueRefreshToken(array $claims): string;
    public function validateAccessToken(string $token): bool;
    public function validateRefreshToken(string $token): bool;
    public function getClaimsFromToken(string $token): array;
}