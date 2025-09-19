<?php

namespace App\Providers;

use App\Application\Cellphone\Port\In\ListCellphonesUseCase;
use App\Application\Cellphone\Service\ListCellphonesService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
