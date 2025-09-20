<?php
declare(strict_types=1);

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

/* Ports In (use-cases) */
use App\Application\Users\Port\In\CreateUserUseCase;
use App\Application\Users\Port\In\LoginUseCase;
use App\Application\Users\Port\In\RefreshTokenUseCase; // ✅ Nuevo use case
use App\Application\Users\Port\In\GetUserByIdUseCase;
use App\Application\Users\Port\In\ListUsersUseCase;
use App\Application\Users\Port\In\UpdateUserUseCase;
use App\Application\Users\Port\In\DeleteUserUseCase;
use App\Application\Users\Port\In\ChangePasswordUseCase;
use App\Application\Users\Port\In\LogoutUseCase;

/* Services (implementations) */
use App\Application\Users\Service\CreateUserService;
use App\Application\Users\Service\LoginService;
use App\Application\Users\Service\RefreshTokenService; // ✅ Nuevo servicio
use App\Application\Users\Service\GetUserByIdService;
use App\Application\Users\Service\ListUsersService;
use App\Application\Users\Service\UpdateUserService;
use App\Application\Users\Service\DeleteUserService;
use App\Application\Users\Service\ChangePasswordService;
use App\Application\Users\Service\LogoutService;

/* Ports Out (interfaces required by services) */
use App\Application\Users\Port\Out\UserRepositoryPort;
use App\Application\Users\Port\Out\PasswordHasherPort;
use App\Application\Users\Port\Out\PasswordStrengthPolicyPort;
use App\Application\Users\Port\Out\UnitOfWorkPort;
use App\Application\Users\Mapper\UserMapper;
use App\Application\Users\Port\Out\TokenIssuerPort;
use App\Infrastructure\Adapters\Database\Eloquent\Repository\EloquentUserRepositoryAdapter;
use App\Infrastructure\Adapters\Database\Eloquent\UnitOfWork\LaravelUnitOfWorkAdapter;
use App\Infrastructure\Adapters\Security\Jwt\JwtTokenIssuerAdapter;
use App\Infrastructure\Adapters\Security\Password\PasswordHasherAdapter;
use App\Infrastructure\Adapters\Security\Password\PasswordStrengthPolicyAdapter;

final class UserBindingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Mapper
        $this->app->singleton(UserMapper::class, fn() => new UserMapper());

        // Ports Out
        $this->app->bind(UserRepositoryPort::class, EloquentUserRepositoryAdapter::class);
        $this->app->singleton(PasswordHasherPort::class, fn() => new PasswordHasherAdapter());
        $this->app->singleton(PasswordStrengthPolicyPort::class, fn() => new PasswordStrengthPolicyAdapter());
        
        // UnitOfWork
        $this->app->bind(UnitOfWorkPort::class, LaravelUnitOfWorkAdapter::class);
        
        // Token Issuer - Actualizado para refresh token
        $this->app->singleton(TokenIssuerPort::class, function () {
            return new JwtTokenIssuerAdapter(
                config('app.jwt_secret') ?? env('JWT_SECRET', 'changeme'),
                (int)(env('JWT_ACCESS_TTL', 3600)),      // Access token TTL
                (int)(env('JWT_REFRESH_TTL', 2592000))   // Refresh token TTL (30 días)
            );
        });

        // Use Cases
        $this->app->bind(CreateUserUseCase::class, function ($app) {
            return new CreateUserService(
                $app->make(UserRepositoryPort::class),
                $app->make(PasswordHasherPort::class),
                $app->make(PasswordStrengthPolicyPort::class),
                $app->make(UnitOfWorkPort::class)
            );
        });

        $this->app->bind(LoginUseCase::class, function ($app) {
            return new LoginService(
                $app->make(UserRepositoryPort::class),
                $app->make(PasswordHasherPort::class),
                $app->make(TokenIssuerPort::class)
            );
        });

        // ✅ NUEVO: Refresh Token Use Case
        $this->app->bind(RefreshTokenUseCase::class, function ($app) {
            return new RefreshTokenService(
                $app->make(UserRepositoryPort::class),
                $app->make(TokenIssuerPort::class)
            );
        });

        $this->app->bind(GetUserByIdUseCase::class, function ($app) {
            return new GetUserByIdService(
                $app->make(UserRepositoryPort::class),
                $app->make(UserMapper::class)
            );
        });

        $this->app->bind(ListUsersUseCase::class, function ($app) {
            return new ListUsersService(
                $app->make(UserRepositoryPort::class),
                $app->make(UserMapper::class)
            );
        });

        $this->app->bind(UpdateUserUseCase::class, function ($app) {
            return new UpdateUserService(
                $app->make(UserRepositoryPort::class),
                $app->make(UnitOfWorkPort::class)
            );
        });

        $this->app->bind(DeleteUserUseCase::class, function ($app) {
            return new DeleteUserService(
                $app->make(UserRepositoryPort::class),
                $app->make(UnitOfWorkPort::class)
            );
        });

        $this->app->bind(ChangePasswordUseCase::class, function ($app) {
            return new ChangePasswordService(
                $app->make(UserRepositoryPort::class),
                $app->make(PasswordHasherPort::class),
                $app->make(PasswordStrengthPolicyPort::class),
                $app->make(UnitOfWorkPort::class)
            );
        });

        $this->app->bind(LogoutUseCase::class, function ($app) {
            return new LogoutService();
        });
    }

    public function boot(): void
    {
        //
    }
}