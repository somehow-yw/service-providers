<?php

namespace App\Providers;

use Zdp\ServiceProvider\Data\Models\WechatAccount;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    const EASY_WECHAT_CONFIG_KEY = 'wechat';
    const WECHAT_ACCOUNT_KEY     = 'wechat_account';
    const SERVICE_PROVIDER_KEY   = 'service_provider';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->runningInConsole()) {
            return;
        }

        $source = $this->resolveSource(request());

        if ($this->sourceInWhitelist($source)) {
            return;
        }

        $config = WechatAccount::getWeChatConfigBySource($source);
        $this->setGlobalModel($source);
        $this->setWeChatConfig($config);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * 解析出子域名 如 cd.test.zdongpin.com => cd
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function resolveSource($request)
    {
        $host = explode('.', $request->getHttpHost());

        $subDomain = array_first($host);

        return $subDomain;
    }

    protected function sourceInWhitelist($source)
    {
        return in_array($source, ['dongpin', 'test']);
    }

    /**
     * 设置wechat配置
     *
     * @param $config
     */
    protected function setWeChatConfig($config)
    {
        $config = array_merge(config(self::EASY_WECHAT_CONFIG_KEY), $config);
        app('config')->set(self::EASY_WECHAT_CONFIG_KEY, $config);
    }


    /**
     * 设置全局可用的wechat_accounts,service_provider实例
     *
     * @param $source
     */
    protected function setGlobalModel($source)
    {
        $weChatAccounts = WechatAccount::where('source', $source)
                                       ->first();
        $serviceProvider = $weChatAccounts->serviceProvider;
        app('config')->set(self::WECHAT_ACCOUNT_KEY, $weChatAccounts);
        app('config')->set(self::SERVICE_PROVIDER_KEY, $serviceProvider);
    }
}
