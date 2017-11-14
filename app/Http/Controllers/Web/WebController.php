<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/16
 * Time: 18:11
 */

namespace App\Http\Controllers\Web;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Zdp\ServiceProvider\Data\Models\SpMember;
use Zdp\ServiceProvider\Data\Models\SpMemberInvite;
use Zdp\ServiceProvider\Data\Models\WechatAccount;
use Zdp\ServiceProvider\Data\Utils\UserTag;

/**
 * 前端请求静态单页的处理
 * Class WebController
 *
 * @package App\Http\Controllers\Web
 */
class WebController extends Controller
{
    public function home()
    {
        $sp = getServiceProvider();

        if (empty($sp)) {
            throw new AppException('找不到服务商');
        }

        if ($sp->enable_custom_homepage == 0) {
            // 未开启首页，直接跳到买货
            return redirect('/search');
        }

        return view('blades.home');
    }

    /**
     * 跳转输出到指定的静态页面
     *
     * @param $pageName string 页面名称
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \App\Exceptions\AppException
     */
    public function pullWebPage($pageName)
    {
        if (empty($pageName)) {
            throw new AppException('参数不正确');
        }

        $ext = pathinfo($pageName, PATHINFO_EXTENSION);
        if ($ext == 'html') {
            throw new NotFoundResourceException();
        }

        // 需做模板输出的$pageName
        $bladePageNameArr = config('view.blade_view_page', []);
        // 之前进行了跳转到指定静态页面
        // 由于微信授权问题，现调整部分需要的页面进行模板输出
        // 当全部调整完并确认不影响其它功能时可去掉此判断
        $pageRoute = request()->input('page_route', '');
        if (in_array($pageName, $bladePageNameArr)) {
            $outputArr = [
                'page_route' => $pageRoute,
            ];

            return view("blades.{$pageName}", $outputArr);
        }

        $webHost = request()->getScheme() . '://' . request()->getHost() .
                   ':' . request()->getPort() . request()->getBaseUrl();

        if (!empty($pageRoute)) {
            $pageRoute = "#{$pageRoute}";
        }

        $pagePath = "{$webHost}/{$pageName}.html{$pageRoute}";

        return redirect($pagePath);
    }

    /**
     * 邀请新成员
     *
     * @param $hash string
     *
     * @return mixed
     */
    public function newShopMember($hash)
    {
        return \DB::transaction(function () use ($hash) {
            $wuser = getWeChatOAuthUser();

            if (empty($wuser)) {
                throw new AppException('微信授权失败.');
            }

            $isOwner = isSpOwner();

            if ($isOwner) {
                return redirect()->route('static_web', [
                    'page_name'  => 'index',
                    'page_route' => '/seller',
                ]);
            }

            $openid = $wuser->getId();

            if (SpMember::where('wechat_openid', $openid)->exists()) {
                return redirect()->route('static_web', [
                    'page_name'  => 'index',
                    'page_route' => '/seller',
                ]);
            }

            $sp_id = SpMemberInvite
                ::where('hash', $hash)
                ->where('updated_at', '>=', SpMemberInvite::getExpiredTime())
                ->value('sp_id');

            if (empty($sp_id)) {
                throw new AppException('链接已失效');
            }

            SpMember::create([
                'wechat_openid' => $openid,
                'wechat_name'   => $wuser->getNickname(),
                'sp_id'         => $sp_id,
            ]);

            SpMemberInvite::where('hash', $hash)->delete();

            $weChatAccount = getWeChatAccount();
            $config =
                WechatAccount::getWeChatConfigBySource($weChatAccount->source);
            $config = array_merge(config('wechat'), $config);

            /** @var UserTag $userTag */
            $userTag = new UserTag($config);
            $userTag->tagShop($openid);

            return redirect()->route('static_web', [
                'page_name'  => 'index',
                'page_route' => '/seller',
            ]);
        });
    }
}
