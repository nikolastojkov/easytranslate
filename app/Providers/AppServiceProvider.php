<?php

namespace App\Providers;

use App\Contracts\CurrencyConversionContract;
use App\Repositories\CurrencyConversionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CurrencyConversionContract::class, CurrencyConversionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
