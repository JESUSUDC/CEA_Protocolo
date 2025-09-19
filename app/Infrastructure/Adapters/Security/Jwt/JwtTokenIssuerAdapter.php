<?php
declare(strict_types=1);

namespace Infrastructure\Adapters\Security\Jwt;

use Application\Security\JwtTokenIssuer; // interface assumed

final class JwtTokenIssuerAdapter implements JwtTokenIssuer
{
    public function __construct(private string $secret, private int $ttlSeconds = 3600)
    {
    }

    public function issue(array $claims): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $now = time();
        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $this->ttlSeconds,
        ]);
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        $sig = $this->sign($header . '.' . $payloadEncoded);
        return sprintf('%s.%s.%s', $header, $payloadEncoded, $sig);
    }

    public function verify(string $token): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        [$header, $payload, $sig] = $parts;
        $expected = $this->sign($header . '.' . $payload);
        if (!hash_equals($expected, $sig)) return false;
        $payloadJson = json_decode($this->base64UrlDecode($payload), true);
        if (!isset($payloadJson['exp'])) return false;
        return time() <= (int)$payloadJson['exp'];
    }

    private function sign(string $data): string
    {
        $raw = hash_hmac('sha256', $data, $this->secret, true);
        return $this->base64UrlEncode($raw);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) $data .= str_repeat('=', 4 - $remainder);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
