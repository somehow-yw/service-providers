<?php

namespace App\Http\Middleware;

use Cache;
use App\Models\AuthenticatedUser;
use App\Models\User;
use Closure;
use EasyWeChat\Foundation\Application;
use Overtrue\Socialite\UserInterface;
use App\Exceptions\AppException;
use Zdp\ServiceProvider\Data\Models\ServiceProvider;
use Zdp\ServiceProvider\Data\Models\SpMember;

class WeChatOAuth
{
    const IS_OWNER_KEY  = 'SP_IS_OWNER';
    const IS_MEMBER_KEY = 'SP_IS_MEMBER';

    /**
     * Use Service Container would be much artisan.
     */
    private $wechat;
    private $wechatCacheKey;

    /**
     * Inject the wechat service.
     *
     * @param Application $wechat
     */
    public function __construct(Application $wechat)
    {
        $host = explode('.', request()->getHttpHost());
        $subDomain = array_first($host);
        $this->wechatCacheKey =
            'wechat-oauth-user:' . $subDomain . '-' . session_id();
        $this->wechat = $wechat;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $scopes
     *
     * @return mixed
     * @throws AppException
     */
    public function handle($request, Closure $next, $scopes = null)
    {
        $onlyRedirectInWeChatBrowser =
            config('wechat.oauth.only_wechat_browser', false);

        if ($onlyRedirectInWeChatBrowser && !$this->isWeChatBrowser($request)) {
            if (config('debug')) {
                Log::debug('[not wechat browser] skip wechat oauth redirect.');
            }

            return $next($request);
        }

        $scopes = $scopes ? : config('wechat.oauth.scopes', ['snsapi_base']);

        if (is_string($scopes)) {
            $scopes = array_map('trim', explode(',', $scopes));
        }

        $route = $request->route()->getAction();
        //!session()->has('wechat.oauth_user') || $this->needReAuth($scopes)
        if (!Cache::has($this->wechatCacheKey) &&
            !config('wechat.enable_mock')
        ) {
            if (in_array('api', $route['middleware'])) {
                throw new AppException('请刷新页面重新授权');
            }
            if ($request->has('code')) {
                /** @var UserInterface $wechatUser */
                $wechatUser = $this->wechat->oauth->user();
                //session(['wechat.oauth_user' => $wechatUser]);
                Cache::put($this->wechatCacheKey, $wechatUser, 600);
                $this->injectGlobalModel($wechatUser, $route['middleware']);

                $route = $request->route()->getName();

                if (!empty($route)) {
                    return redirect()
                        ->route(
                            $route,
                            array_merge(
                                $request->route()->parameters(),
                                $request->input()
                            )
                        );
                } else {
                    return redirect($request->route()->uri());
                }

                //return redirect()->to($this->getTargetUrl($request));
            }

            //session()->forget('wechat.oauth_user');
            Cache::forget($this->wechatCacheKey);

            return $this->wechat->oauth->scopes($scopes)
                                       ->redirect($request->fullUrl());
        }

        if (config('wechat.enable_mock')) {
            $this->injectGlobalModel(session('wechat.oauth_user'),
                $route['middleware']);
        } else {
            $this->injectGlobalModel(Cache::get($this->wechatCacheKey),
                $route['middleware']);
        }

        return $next($request);
    }

    /**
     * 注入验证过的用户
     *
     * @param $wechatUser UserInterface
     *
     * @param $middleware
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws AppException
     */
    protected function injectGlobalModel($wechatUser, $middleware)
    {
        $openId = $wechatUser->getId();

        $serviceProvider = getServiceProvider();
        if (is_null($serviceProvider) || $serviceProvider->status == ServiceProvider::DENY) {
            throw new AppException('服务商不存在');
        }

        $isOwner = $serviceProvider->wechat_openid == $openId;

        app('config')->set(self::IS_OWNER_KEY, $isOwner);

        if ($isOwner) {
            $isMember = false;
        } else {
            $isMember = SpMember::where('wechat_openid', $openId)
                                ->where('sp_id', $serviceProvider->zdp_user_id)
                                ->exists();
        }

        app('config')->set(self::IS_MEMBER_KEY, $isMember);

        $authenticatedUser = User::where('wechat_openid', $openId)->first();
        $request = request();
        $requestPath = $request->path();
        $hasPageName = !empty($request->input('page_name'));
        $noRegisterArr = config('middleware.no_register');

        if ($isOwner) {
            // 如果是服务商暂时不做操作
        } elseif ($isMember) {
            // 如果是服务商成员暂时不做操作
        } elseif (empty($authenticatedUser)
                  && in_array('web', $middleware) && $hasPageName
                  && !in_array($requestPath, $noRegisterArr)
        ) {
            return abort(302, '注册', ['Location' => '/register']);
        }

        app()->singleton(
            AuthenticatedUser::class,
            function () use ($authenticatedUser) {
                return new AuthenticatedUser($authenticatedUser);
            }
        );
    }

    /**
     * Build the target business url.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function getTargetUrl($request)
    {
        // 需做模板输出的$pageName
        $bladePageNameArr = config('view.blade_view_page', []);
        $pageName = $request->route('page_name');
        $pageRoute = $request->input('page_route', '');
        if (!empty($pageRoute)) {
            if (!empty($pageName) && in_array($pageName, $bladePageNameArr)) {
                $pageRoute = "?page_route={$pageRoute}";
            } else {
                $pageRoute = "#{$pageRoute}";
            }
        }

        return $request->url() . $pageRoute;
        //$queries = array_except($request->query(), ['code', 'state']);
        //return $request->url() . (empty($queries) ? '' : '?' . http_build_query($queries));
    }

    /**
     * Is different scopes.
     *
     * @param  array $scopes
     *
     * @return bool
     */
    protected function needReAuth($scopes)
    {
        //return session('wechat.oauth_user.original.scope') == 'snsapi_base' && in_array("snsapi_userinfo", $scopes);
        $wechatOauthUser = Cache::get($this->wechatCacheKey);

        return $wechatOauthUser->original->scope == 'snsapi_base' &&
               in_array("snsapi_userinfo", $scopes);
    }

    /**
     * Detect current user agent type.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function isWeChatBrowser($request)
    {
        return strpos($request->header('user_agent'), 'MicroMessenger') !==
               false;
    }
}
