<?php

namespace App\Http\Middleware;

use App\Models\WebAdminUser;
use Closure;

/**
 * Class AdminNotLogin
 *
 * 验证服务商管理后台未登录
 *
 * @package App\Http\Middleware
 */
class AdminNotLogin
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

        if ($user->isLoggedIn()) {
            if (!$request->ajax()) {
                return redirect(config('admin.urls.index'));
            }
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