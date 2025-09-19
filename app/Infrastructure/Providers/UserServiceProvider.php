<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Providers;

use Illuminate\Support\ServiceProvider;
use Infrastructure\Adapters\Database\Eloquent\Model\UserModel;
use Infrastructure\Adapters\Database\Eloquent\Repository\EloquentUserRepositoryAdapter;
use Infrastructure\Adapters\Security\Password\PasswordHasherAdapter;
use Infrastructure\Adapters\Security\Password\PasswordStrengthPolicyAdapter;
use Infrastructure\Entrypoint\Rest\Users\Mapper\UserHttpMapper;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserModel::class, function () {
            return new UserModel();
        });

        $this->app->bind(\Application\Port\Out\UserRepository::class, function ($app) {
            return new EloquentUserRepositoryAdapter($app->make(UserModel::class));
        });

        $this->app->singleton(\Domain\Users\Service\Contracts\PasswordHasher::class, function () {
            return new PasswordHasherAdapter();
        });

        $this->app->singleton(\Domain\Users\Service\Contracts\PasswordStrengthEvaluator::class, function () {
            return new PasswordStrengthPolicyAdapter();
        });

        $this->app->singleton(UserHttpMapper::class, function () {
            return new UserHttpMapper();
        });
    }
}
