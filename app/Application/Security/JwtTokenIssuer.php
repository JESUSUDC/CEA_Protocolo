<?php
declare(strict_types=1);

namespace App\Application\Security;

interface JwtTokenIssuer
{
    /**
     * Issue a JWT for given claims, returns string token.
     *
     * @param array $claims
     * @return string
     */
    public function issue(array $claims): string;

    /**
     * Verify token and return claims array or null if invalid.
     *
     * @param string $token
     * @return array|null
     */
    public function verify(string $token): ?array;
}
