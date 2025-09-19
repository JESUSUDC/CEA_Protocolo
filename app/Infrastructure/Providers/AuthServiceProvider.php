<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Providers;

use Illuminate\Support\ServiceProvider;
use Infrastructure\Adapters\Security\Jwt\JwtTokenIssuerAdapter;
use Application\Security\TokenIssuerPort;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TokenIssuerPort::class, function () {
            $secret = config('app.jwt_secret'); // .env JWT_SECRET
            $ttl = (int) config('app.jwt_ttl', 3600); // segundos
            return new JwtTokenIssuerAdapter($secret, $ttl);
        });
    }
}
