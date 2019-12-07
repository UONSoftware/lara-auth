<?php

namespace UonSoftware\LaraAuth;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use UonSoftware\LaraAuth\Services\LoginService;
use UonSoftware\LaraAuth\Contracts\LoginContract;
use UonSoftware\LaraAuth\Services\ChangePasswordService;
use UonSoftware\LaraAuth\Contracts\ChangePasswordContract;
use UonSoftware\LaraAuth\Services\UpdateUserPasswordService;
use UonSoftware\LaraAuth\Contracts\UpdateUserPasswordContract;

class LaravelAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/lara_auth.php', 'lara_auth');

        $this->app->singleton(UpdateUserPasswordContract::class, UpdateUserPasswordService::class);
        $this->app->singleton(LoginContract::class, LoginService::class);
        $this->app->singleton(ChangePasswordContract::class, ChangePasswordService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/lara_auth.php' => config_path('lara_auth.php'),
            ],
            'config'
        );

        Route::prefix('/api/auth')
            ->middleware('api')
            ->name('auth.*')
            ->namespace('UonSoftware\LaraAuth\Http\Controllers')
            ->group(__DIR__ . '/../routes/api.php');
    }
}
