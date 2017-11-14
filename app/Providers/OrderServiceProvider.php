<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Customer\Contracts\OrderRepository as OrderInterface;
use App\Repositories\Customer\OrderRepository;

class OrderServiceProvider extends ServiceProvider
{
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
        // 订单仓库
        $this->app->singleton(
            OrderInterface::class,
            OrderRepository::class
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
            OrderInterface::class,
        ];
    }
}
