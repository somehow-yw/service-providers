<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Shop\Contracts\MainUserRepository as MainUserInterface;
use App\Repositories\Shop\MainUserRepository;

class MainShopServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // 主站店铺数据仓库绑定
        $this->app->singleton(
            MainUserInterface::class,
            MainUserRepository::class
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            MainUserInterface::class,
        ];
    }
}
