<?php
declare(strict_types=1);

namespace Infrastructure\Adapters\Security\Jwt;

use App\Application\Security\JwtTokenIssuer;

final class JwtTokenIssuerAdapter implements JwtTokenIssuer
{
    public function __construct(private string $secret, private int $ttlSeconds = 3600) {}

    public function issue(array $claims): string
    {
        $header = $this->b64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $now = time();
        $claims['iat'] = $now;
        $claims['exp'] = $now + $this->ttlSeconds;

        $payload = $this->b64url(json_encode($claims));
        $signature = $this->b64url(hash_hmac('sha256', "$header.$payload", $this->secret, true));

        return "$header.$payload.$signature";
    }

    public function verify(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) return null;

            [$header, $payload, $sig] = $parts;
            $expected = $this->b64url(hash_hmac('sha256', "$header.$payload", $this->secret, true));

            if (!hash_equals($expected, $sig)) return null;

            $data = json_decode($this->b64urldecode($payload), true);
            if (!isset($data['exp']) || time() > (int)$data['exp']) return null;

            return $data;
        } catch (\Throwable) {
            return null;
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
