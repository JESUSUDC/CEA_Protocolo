<?php
declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\Security\JwtTokenIssuer as SecurityJwtTokenIssuer;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Adapters\Security\Jwt\JwtTokenIssuerAdapter;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SecurityJwtTokenIssuer::class, function () {
            $secret = config('app.jwt_secret'); // .env JWT_SECRET
            $ttl = (int) config('app.jwt_ttl', 3600); // segundos
            return new JwtTokenIssuerAdapter($secret, $ttl);
        });
    }
}
