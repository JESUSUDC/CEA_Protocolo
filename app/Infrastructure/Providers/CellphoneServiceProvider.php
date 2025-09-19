<?php
declare(strict_types=1);

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Infrastructure\Adapters\Database\Eloquent\Model\CellphoneModel;
use App\Infrastructure\Adapters\Database\Eloquent\Repository\EloquentCellphoneRepositoryAdapter;
use App\Infrastructure\Entrypoint\Rest\Cellphones\Mapper\CellphoneHttpMapper;
use App\Infrastructure\Adapters\Database\Eloquent\UnitOfWork\LaravelUnitOfWorkAdapter;
use App\Infrastructure\Adapters\Security\Password\PasswordHasherAdapter; // if needed
use App\Application\Cellphone\Port\Out\CellphoneRepositoryPort;

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

        $this->app->singleton(\App\Application\Users\Port\Out\UnitOfWorkPort::class, function () {
            return new LaravelUnitOfWorkAdapter();
        });

        $this->app->singleton(CellphoneHttpMapper::class, function () {
            return new CellphoneHttpMapper();
        });
    }
}
