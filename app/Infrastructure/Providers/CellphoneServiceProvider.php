<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Providers;

use Illuminate\Support\ServiceProvider;
use Infrastructure\Adapters\Database\Eloquent\Model\CellphoneModel;
use Infrastructure\Adapters\Database\Eloquent\Repository\EloquentCellphoneRepositoryAdapter;
use Infrastructure\Entrypoint\Rest\Cellphones\Mapper\CellphoneHttpMapper;
use Infrastructure\Adapters\Database\Eloquent\UnitOfWork\LaravelUnitOfWorkAdapter;
use Infrastructure\Adapters\Security\Password\PasswordHasherAdapter; // if needed
use Application\Cellphone\Port\Out\CellphoneRepositoryPort;

class CellphoneServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CellphoneModel::class, function ($app) {
            return new CellphoneModel();
        });

        $this->app->bind(CellphoneRepositoryPort::class, function ($app) {
            return new EloquentCellphoneRepositoryAdapter($app->make(CellphoneModel::class));
        });

        $this->app->singleton(\Application\Users\Port\Out\UnitOfWorkPort::class, function () {
            return new LaravelUnitOfWorkAdapter();
        });

        $this->app->singleton(CellphoneHttpMapper::class, function () {
            return new CellphoneHttpMapper();
        });
    }
}
