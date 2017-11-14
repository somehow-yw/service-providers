<?php

namespace App\Http\Middleware;

use App\Models\WebAdminUser;
use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class AdminLogin
 *
 * 验证服务商管理后台登陆
 *
 * @package App\Http\Middleware
 */
class AdminLogin
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function handle($request, Closure $next)
    {
        $user = new WebAdminUser();

        if (!$user->isLoggedIn()) {
            if ($request->ajax()) {
                throw new AccessDeniedHttpException('登陆失效');
            }

            return redirect(config('admin.urls.login'));
        }

        app()->singleton(
            WebAdminUser::class,
            function () use ($user) {
                return $user;
            }
        );

        return $next($request);
    }
}