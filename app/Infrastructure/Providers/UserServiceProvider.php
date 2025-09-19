<?php
declare(strict_types=1);

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Infrastructure\Adapters\Database\Eloquent\Model\UserModel;
use App\Infrastructure\Adapters\Database\Eloquent\Repository\EloquentUserRepositoryAdapter;
use App\Infrastructure\Adapters\Security\Password\PasswordHasherAdapter;
use App\Infrastructure\Adapters\Security\Password\PasswordStrengthPolicyAdapter;
use App\Infrastructure\Adapters\Security\Jwt\JwtTokenIssuerAdapter;
use App\Application\Users\Port\Out\UserRepositoryPort;
use App\Application\Users\Port\Out\PasswordHasherPort;
use App\Application\Users\Port\Out\PasswordStrengthPolicyPort;
use App\Application\Security\Port\Out\TokenIssuerPort;
use Infrastructure\Adapters\Security\Jwt\JwtTokenIssuerAdapter as JwtJwtTokenIssuerAdapter;

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
            return new JwtJwtTokenIssuerAdapter($secret, $ttl);
        });
    }
}
