<?php

namespace UonSoftware\LaraAuth;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;
use UonSoftware\LaraAuth\Services\LoginService;
use UonSoftware\LaraAuth\Contracts\LoginContract;
use UonSoftware\LaraAuth\Events\PasswordChangedEvent;
use UonSoftware\LaraAuth\Services\ChangePasswordService;
use UonSoftware\LaraAuth\Events\RequestNewPasswordEvent;
use UonSoftware\LaraAuth\Contracts\ChangePasswordContract;
use UonSoftware\LaraAuth\Listeners\PasswordChangedListener;
use UonSoftware\LaraAuth\Services\UpdateUserPasswordService;
use UonSoftware\LaraAuth\Contracts\UpdateUserPasswordContract;
use UonSoftware\LaraAuth\Listeners\RequestNewPasswordListener;

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
     * @param \Illuminate\Contracts\Events\Dispatcher $dispatcher
     *
     * @return void
     */
    public function boot(Dispatcher $dispatcher): void
    {
        if($this->app->runningInConsole())  {
            $this->publishes(
                [
                    __DIR__ . '/../config/lara_auth.php' => config_path('lara_auth.php'),
                ],
                'config'
            );
        }

        Route::prefix('/api/auth')
            ->middleware('api')
            ->name('auth.*')
            ->namespace('UonSoftware\LaraAuth\Http\Controllers')
            ->group(__DIR__ . '/../routes/api.php');

        $dispatcher->listen(PasswordChangedEvent::class, PasswordChangedListener::class);
        $dispatcher->listen(RequestNewPasswordEvent::class, RequestNewPasswordListener::class);
    }
}
