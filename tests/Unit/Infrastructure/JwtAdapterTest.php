<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Adapters\Security\Jwt\JwtTokenIssuerAdapter;

class JwtAdapterTest extends TestCase
{
    public function test_issue_and_validate_access_token(): void
    {
        $secret = 'my-test-secret';
        $adapter = new JwtTokenIssuerAdapter($secret, 3600, 86400);

        $claims = [
            'sub' => 'user-1',
            'username' => 'juanp',
            'role' => 'user',
            'type' => 'access'
        ];

        $token = $adapter->issueAccessToken($claims);
        $this->assertIsString($token);

        $this->assertTrue($adapter->validateAccessToken($token));

        $decoded = $adapter->getClaimsFromToken($token);
        $this->assertEquals('user-1', $decoded['sub']);
        $this->assertEquals('access', $decoded['type']);
    }

    public function test_refresh_token_validation_and_issue(): void
    {
        $secret = 'another-secret';
        $adapter = new JwtTokenIssuerAdapter($secret, 3600, 86400);

        $refresh = $adapter->issueRefreshToken(['sub' => 'user-1']);
        $this->assertIsString($refresh);
        $this->assertTrue($adapter->validateRefreshToken($refresh));

        $claims = $adapter->getClaimsFromToken($refresh);
        $this->assertEquals('refresh', $claims['type']);
        $this->assertEquals('user-1', $claims['sub']);
    }
}
