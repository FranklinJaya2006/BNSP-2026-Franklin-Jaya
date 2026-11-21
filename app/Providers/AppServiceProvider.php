<?php

namespace App\Providers;

use App\Contracts\BillingCalculator;
use App\Contracts\RentalManager;
use App\Services\Auth\ApprovedTenantUserProvider;
use App\Services\BillingService;
use App\Services\RentalService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BillingCalculator::class, BillingService::class);
        $this->app->bind(RentalManager::class, RentalService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('approved-tenants', function (Application $app, array $config): ApprovedTenantUserProvider {
            return new ApprovedTenantUserProvider($app['hash'], $config['model']);
        });
    }
}
