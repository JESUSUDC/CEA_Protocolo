<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Providers;

use Illuminate\Support\ServiceProvider;
use Infrastructure\Adapters\Database\Eloquent\Model\UserModel;
use Infrastructure\Adapters\Database\Eloquent\Repository\EloquentUserRepositoryAdapter;
use Infrastructure\Adapters\Security\Password\PasswordHasherAdapter;
use Infrastructure\Adapters\Security\Password\PasswordStrengthPolicyAdapter;
use Infrastructure\Adapters\Security\Jwt\JwtTokenIssuerAdapter;
use Application\Users\Port\Out\UserRepositoryPort;
use Application\Users\Port\Out\PasswordHasherPort;
use Application\Users\Port\Out\PasswordStrengthPolicyPort;
use Application\Security\Port\Out\TokenIssuerPort;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserModel::class, fn() => new UserModel());

        $this->app->bind(UserRepositoryPort::class, function($app) {
            return new EloquentUserRepositoryAdapter($app->make(UserModel::class));
        });

        $this->app->singleton(PasswordHasherPort::class, fn() => new PasswordHasherAdapter());
        $this->app->singleton(PasswordStrengthPolicyPort::class, fn() => new PasswordStrengthPolicyAdapter());

        // JWT secret from env
        $this->app->singleton(TokenIssuerPort::class, function($app) {
            $secret = config('app.jwt_secret') ?? env('JWT_SECRET', 'changeme');
            $ttl = (int)(env('JWT_TTL', 3600));
            return new JwtTokenIssuerAdapter($secret, $ttl);
        });
    }
}
