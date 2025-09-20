<?php
declare(strict_types=1);

namespace App\Infrastructure\Adapters\Security\Jwt;

use App\Application\Users\Port\Out\TokenIssuerPort;

final class JwtTokenIssuerAdapter implements TokenIssuerPort
{
    public function __construct(
        private string $secret,
        private int $accessTokenTtl = 3600, // 1 hora
        private int $refreshTokenTtl = 2592000 // 30 días
    ) {}

    public function issueAccessToken(array $claims): string
    {
        // Garantizar claim type
        if (!isset($claims['type'])) {
            $claims['type'] = 'access';
        }

        return $this->issueToken($claims, $this->accessTokenTtl);
    }

    public function issueRefreshToken(array $claims): string
    {
        // Para refresh tokens, normalmente solo necesitamos el user ID
        $refreshClaims = [
            'sub' => $claims['sub'],
            'type' => 'refresh'
        ];
        
        return $this->issueToken($refreshClaims, $this->refreshTokenTtl);
    }

    public function validateAccessToken(string $token): bool
    {
        return $this->validateToken($token, 'access');
    }

    public function validateRefreshToken(string $token): bool
    {
        return $this->validateToken($token, 'refresh');
    }

    public function getClaimsFromToken(string $token): array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) return [];

            $payload = $parts[1];
            $decoded = $this->b64urldecode($payload);
            if ($decoded === false) return [];
            $data = json_decode($decoded, true);

            return is_array($data) ? $data : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function issueToken(array $claims, int $ttl): string
    {
        $claims['iat'] = time();
        $claims['exp'] = time() + $ttl;

        $header = $this->b64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->b64url(json_encode($claims));
        $signature = $this->b64url(hash_hmac('sha256', "$header.$payload", $this->secret, true));

        return "$header.$payload.$signature";
    }

    private function validateToken(string $token, string $expectedType = null): bool
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) return false;
            [$headerB64, $payloadB64, $sig] = $parts;

            $headerJson = $this->b64urldecode($headerB64);
            $header = json_decode($headerJson, true);
            if (!is_array($header) || ($header['alg'] ?? '') !== 'HS256') return false;

            $expected = $this->b64url(hash_hmac('sha256', "$headerB64.$payloadB64", $this->secret, true));
            if (!hash_equals($expected, $sig)) return false;

            $data = json_decode($this->b64urldecode($payloadB64), true);
            if (!isset($data['exp']) || time() > (int)$data['exp']) return false;
            if ($expectedType && (!isset($data['type']) || $data['type'] !== $expectedType)) return false;

            return true;
        } catch (\Throwable) {
            return false;
        }
    }


    private function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function b64urldecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) $data .= str_repeat('=', 4 - $remainder);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}