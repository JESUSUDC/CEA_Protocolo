<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Providers;

use Illuminate\Support\ServiceProvider;

/* Ports In (use-cases) */
use App\Application\Users\Port\In\CreateUserUseCase;
use App\Application\Users\Port\In\LoginUseCase;
use App\Application\Users\Port\In\GetUserByIdUseCase;
use App\Application\Users\Port\In\ListUsersUseCase;
use App\Application\Users\Port\In\UpdateUserUseCase;
use App\Application\Users\Port\In\DeleteUserUseCase;
use App\Application\Users\Port\In\ChangePasswordUseCase;
use App\Application\Users\Port\In\LogoutUseCase;

/* Services (implementations) */
use App\Application\Users\Service\CreateUserService;
use App\Application\Users\Service\LoginService;
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
use App\Application\Users\Port\Out\UnitOfWorkPort; // if you used UnitOfWorkPort
use App\Application\Security\Port\Out\TokenIssuerPort;

/* Additional helpers */
use Application\Users\Mapper\UserMapper;

final class ApplicationBindingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Mapper
        $this->app->singleton(UserMapper::class, fn() => new UserMapper());

        /*
         * Bind UseCase interfaces to concrete services.
         * The container will resolve constructor arguments (ports out) from other providers.
         * Make sure earlier you have bound:
         *  - UserRepositoryPort (EloquentUserRepositoryAdapter)
         *  - PasswordHasherPort (PasswordHasherAdapter)
         *  - PasswordStrengthPolicyPort (PasswordStrengthPolicyAdapter)
         *  - UnitOfWorkPort (LaravelUnitOfWorkAdapter) if used
         *  - TokenIssuerPort (JwtTokenIssuerAdapter)
         */
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
            // If you have a token blacklist repository, inject it here.
        });
    }
}
