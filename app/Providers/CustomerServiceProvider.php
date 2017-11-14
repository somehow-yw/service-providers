<?php

namespace App\Providers;

use App\Repositories\Customer\Contracts\ShippingAddressRepository as ShippingAddressInterface;
use App\Repositories\Customer\Contracts\UserRepository as UserInterface;
use App\Repositories\Customer\ShippingAddressRepository;
use App\Repositories\Customer\UserRepository;
use App\Repositories\Shop\Contracts\MainGoodsRepository as GoodsInterface;
use App\Repositories\Shop\MainGoodsRepository as GoodsRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Customer\Contracts\ShoppingCartRepository as ShoppingCartInterface;
use App\Repositories\Customer\ShoppingCartRepository;
use App\Repositories\Feedback\Contracts\FeedbackRepository as FeedbackInterface;
use App\Repositories\Feedback\FeedbackRepository;

class CustomerServiceProvider extends ServiceProvider
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
        //收货地址仓库绑定
        $this->app->singleton(
            ShippingAddressInterface::class,
            ShippingAddressRepository::class
        );

        //用户仓库绑定
        $this->app->singleton(
            UserInterface::class,
            UserRepository::class
        );

        //主商品仓库绑定
        $this->app->singleton(
            GoodsInterface::class,
            GoodsRepository::class
        );

        //购物车仓库绑定
        $this->app->singleton(
            ShoppingCartInterface::class,
            ShoppingCartRepository::class
        );

        // 用户反馈仓库绑定
        $this->app->singleton(
            FeedbackInterface::class,
            FeedbackRepository::class
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
            ShippingAddressInterface::class,
            UserInterface::class,
            GoodsInterface::class,
            ShoppingCartInterface::class,
            FeedbackInterface::class,
        ];
    }
}
