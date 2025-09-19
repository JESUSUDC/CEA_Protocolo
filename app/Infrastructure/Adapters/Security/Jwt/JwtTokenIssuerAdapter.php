<?php
declare(strict_types=1);

namespace Infrastructure\Adapters\Security\Jwt;

use Application\Security\TokenIssuerPort;
use Domain\Users\Entity\User;

final class JwtTokenIssuerAdapter implements TokenIssuerPort
{
    public function __construct(private string $secret, private int $ttlSeconds = 3600)
    {
    }

    public function issue(User $user): string
    {
        $header = $this->b64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $now = time();
        $payload = $this->b64url(json_encode([
            'sub' => $user->id()->toString(),
            'username' => $user->username()->toString(),
            'role' => $user->role()->toString(),
            'iat' => $now,
            'exp' => $now + $this->ttlSeconds,
        ]));
        $signature = $this->b64url(hash_hmac('sha256', "$header.$payload", $this->secret, true));
        return "$header.$payload.$signature";
    }

    public function validate(string $token): bool
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) return false;
            [$header, $payload, $sig] = $parts;
            $expected = $this->b64url(hash_hmac('sha256', "$header.$payload", $this->secret, true));
            if (!hash_equals($expected, $sig)) return false;
            $data = json_decode($this->b64urldecode($payload), true);
            if (!isset($data['exp'])) return false;
            return time() <= (int)$data['exp'];
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
