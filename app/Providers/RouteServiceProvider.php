<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        session_start();

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebAdmin();

        $this->mapApiRoutes();

        $this->mapWebRoutes();

        // 服务商自己的管理接口
        $this->mapApiShopRoutes();

        // 服务商客户服务接口
        $this->mapApiCustomerRoutes();

        // 微信处理接口
        $this->mapApiWeChatRoutes();

        // 其它公共数据通用接口
        $this->mapApiOtherRoutes();
    }

    protected function mapWebAdmin()
    {
        Route::group(
            [
                'namespace' => $this->namespace,
            ],
            base_path('routes/admin.php')
        );
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace . '\Web')
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * 服务商自己的管理接口
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiShopRoutes()
    {
        Route::prefix('api/shop')
             ->middleware(['api', 'wechat-oauth', 'has-shop-right'])
             ->namespace($this->namespace . '\Shop')
             ->group(base_path('routes/apiShop.php'));
    }

    /**
     * 服务商客户的服务接口
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiCustomerRoutes()
    {
        Route::prefix('api/customer')
             ->middleware(['api'])
             ->namespace($this->namespace . '\Customer')
             ->group(base_path('routes/apiCustomer.php'));
    }

    /**
     * 微信处理接口
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiWeChatRoutes()
    {
        Route::prefix('api/wechat')
             ->middleware(['api'])
             ->namespace($this->namespace . '\Wechat')
             ->group(base_path('routes/apiWechat.php'));
    }

    /**
     * 其它公共数据通用接口
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiOtherRoutes()
    {
        Route::prefix('api/other')
             ->middleware(['api'])
             ->namespace($this->namespace . '\Other')
             ->group(base_path('routes/apiOther.php'));
    }
}
