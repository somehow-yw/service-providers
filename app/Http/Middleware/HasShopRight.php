<?php

namespace App\Http\Middleware;

use App\Exceptions\AppException;
use Closure;
use Zdp\ServiceProvider\Data\Models\ServiceProvider;

class HasShopRight
{
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
        $this->checkHasRight();

        return $next($request);
    }

    /**
     * check当前用户是否有权限查看店铺管理相关页面
     */
    protected function checkHasRight()
    {
        $weChatAccount = getWeChatAccount();
        $wechatOAuthUser = getWeChatOAuthUser();

        if (empty($weChatAccount)) {
            throw new AppException("该页面不存在");
        }

        if (empty($wechatOAuthUser)) {
            throw new AppException("授权已过期，请重新登录");
        }

        $serviceProvider = $weChatAccount->serviceProvider;
        if ($this->testWhiteList($serviceProvider->wechat_openid)) {
            //
        } elseif (!isSpOwner() && !isSpMember()) {
            throw new AppException("你无权查看此页面");
        }
        $status = $serviceProvider->status;
        if ($status != ServiceProvider::PASS) {
            $status = array_get(ServiceProvider::STATUS, $status, "未知");
            throw new AppException("审核暂未完成 目前状态:{$status}");
        }
    }

    /**
     * 只有白名单中的OPENID才有所有权限
     *
     * @param $openId
     *
     * @return bool
     */
    private function testWhiteList($openId)
    {
        $whiteOpenIdArr = [
            'oUfLYjjdVWJ0MoRfLPwFMmGtOuwg',
            'oUfLYjq2apIWe98srStZABHxtr5w',
            'oUfLYjnFDwyYKyHPz7XeIBlkMpMs',
            'oUfLYjniRfs7Qi3EhFYHYHpg-Vs0',
            'oUfLYjmaXbu30h0K21JV3r8lYbb0',
            'oUfLYjgBXhgoJIV8yIgaK9WPq-QI',
            'oUfLYjvmxriSgFfrueg_JdKMg7xM',
            'o1g2D0dJootsdKJNIEvbx3MMSUco',
        ];

        return in_array($openId, $whiteOpenIdArr);
    }

}
