<?php

namespace App\Http\Middleware;

use Zdp\ServiceProvider\Data\Models\WechatAccount;
use Closure;

class WeChat
{
    const EASY_WECHAT_CONFIG_KEY     = 'wechat';
    const WECHAT_ACCOUNT_KEY         = 'wechat_account';
    const SERVICE_PROVIDER_KEY       = 'service_provider';
    const SERVICE_PROVIDER_OWNER_KEY = 'service_provider_owner';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $source = $this->resolveSource($request);
        $config = WechatAccount::getWeChatConfigBySource($source);
        $this->setGlobalModel($source);
        $this->setWeChatConfig($config);

        return $next($request);
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

        $owner = $weChatAccounts->serviceProvider;
        
        app('config')->set(self::WECHAT_ACCOUNT_KEY, $weChatAccounts);
        app('config')->set(self::SERVICE_PROVIDER_KEY, $serviceProvider);
        app('config')->set(self::SERVICE_PROVIDER_OWNER_KEY, $owner);
    }
}
