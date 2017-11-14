<?php

namespace App\Models;

use App\Exceptions\AppException;
use App\Http\Middleware\WeChat;
use Zdp\ServiceProvider\Data\Models\ServiceProvider;
use Zdp\ServiceProvider\Data\Models\SpMember;
use Zdp\ServiceProvider\Data\Models\WebAdminLoginToken;

/**
 * 服务商管理WEB端登陆用户
 *
 * @package App\Models
 */
class WebAdminUser
{
    const EASY_WECHAT_CONFIG_KEY = 'wechat';

    protected $sp;

    protected $spMember;

    protected $loggedIn = false;

    /**
     * WebAdminUser constructor.
     *
     * @param ServiceProvider $sp
     * @param SpMember        $isOwner
     */
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $openId = session()->get(config('admin.session.open_id'));

        if (empty($openId)) {
            return;
        }

        $sp = ServiceProvider::query()
                             ->where('wechat_openid', $openId)
                             ->first();

        if (empty($sp)) {
            $spMember = SpMember::query()
                                ->with(['serviceProvider'])
                                ->where('wechat_openid', $openId)
                                ->first();

            if (empty($spMember) || empty($spMember->serviceProvider)) {
                self::logout();

                return;
            }

            $sp = $spMember->serviceProvider;
            $this->spMember = $spMember;
        }

        if ($sp->status != ServiceProvider::PASS) {
            self::logout();

            return;
        }

        $this->sp = $sp;

        $this->setWechatConfig();

        $this->loggedIn = true;
    }

    /**
     * 登陆
     *
     * @param string $token
     *
     * @return WebAdminUser
     * @throws AppException
     */
    public static function login($token)
    {
        $openId = WebAdminLoginToken::check($token);

        if (empty($openId)) {
            throw new AppException('登陆凭证错误或失效');
        }

        session()->put(config('admin.session.open_id'), $openId);

        return new self;
    }

    /**
     * 登出
     */
    public static function logout()
    {
        session()->remove(config('admin.session.open_id'));
    }

    /**
     * 是否已登录
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->loggedIn;
    }

    /**
     * 获取 SpMember
     *
     * @return SpMember
     */
    public function getMember()
    {
        return $this->spMember;
    }

    /**
     * 获取当前服务商信息
     *
     * @return ServiceProvider
     */
    public function getSp()
    {
        return $this->sp;
    }

    /**
     * 获取是否为 Owner
     *
     * @return bool
     */
    public function isOwner()
    {
        return empty($this->spMember);
    }

    /**
     * 设置微信账户信息
     */
    public function setWechatConfig()
    {
        app('config')
            ->set(WeChat::SERVICE_PROVIDER_KEY, $this->sp);

        $config = $this->sp->wechatAccount->config;
        $config = array_merge(config(self::EASY_WECHAT_CONFIG_KEY), $config);

        app('config')->set(self::EASY_WECHAT_CONFIG_KEY, $config);
    }
}