<?php
declare(strict_types=1);

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

/* Ports In */
use App\Application\Cellphone\Port\In\ListCellphonesUseCase;
use App\Application\Cellphone\Port\In\GetCellphoneByIdUseCase;
use App\Application\Cellphone\Port\In\RegisterCellphoneUseCase;
use App\Application\Cellphone\Port\In\UpdateCellphoneUseCase;
use App\Application\Cellphone\Port\In\DeleteCellphoneUseCase;

/* Services (Implementaciones de UseCases) */
use App\Application\Cellphone\Service\ListCellphoneService;
use App\Application\Cellphone\Service\GetCellphoneByIdService;
use App\Application\Cellphone\Service\RegisterCellphoneService;
use App\Application\Cellphone\Service\UpdateCellphoneService;
use App\Application\Cellphone\Service\DeleteCellphoneService;

/* Ports Out */
use App\Application\Cellphone\Port\Out\CellphoneRepositoryPort;
use App\Application\Users\Port\Out\UnitOfWorkPort;

/* Adapters */
use App\Infrastructure\Adapters\Database\Eloquent\Repository\EloquentCellphoneRepositoryAdapter;
use App\Infrastructure\Adapters\Database\Eloquent\UnitOfWork\LaravelUnitOfWorkAdapter;

/* Mapper */
use App\Application\Cellphone\Mapper\CellphoneMapper;

final class CellphoneBindingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositorio
        $this->app->bind(CellphoneRepositoryPort::class, EloquentCellphoneRepositoryAdapter::class);

        // UnitOfWork
        

        // Mapper
        $this->app->singleton(CellphoneMapper::class, function () {
            return new CellphoneMapper();
        });

        /*$this->app->bind(
            ListCellphonesUseCase::class,
            ListCellphoneService::class
        );*/
        /*$this->app->bind(ListCellphoneService::class, function() {
            return new ListCellphoneService();
        });*/
        // 3) Registrar el caso de uso (bind interfaz -> implementación)
        //    Construimos la implementación con sus dependencias resueltas por el contenedor.
        $this->app->bind(ListCellphonesUseCase::class, function ($app) {
            return new ListCellphoneService(
                $app->make(CellphoneRepositoryPort::class),
                $app->make(CellphoneMapper::class)
            );
        });


        $this->app->bind(GetCellphoneByIdUseCase::class, function ($app) {
            return new GetCellphoneByIdService(
                $app->make(CellphoneRepositoryPort::class),
                $app->make(CellphoneMapper::class)
            );
        });

        $this->app->bind(RegisterCellphoneUseCase::class, function ($app) {
            return new RegisterCellphoneService(
                $app->make(CellphoneRepositoryPort::class),
                $app->make(UnitOfWorkPort::class)
            );
        });

        $this->app->bind(UpdateCellphoneUseCase::class, function ($app) {
            return new UpdateCellphoneService(
                $app->make(CellphoneRepositoryPort::class),
                $app->make(UnitOfWorkPort::class)
            );
        });

        $this->app->bind(DeleteCellphoneUseCase::class, function ($app) {
            return new DeleteCellphoneService(
                $app->make(CellphoneRepositoryPort::class),
                $app->make(UnitOfWorkPort::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
