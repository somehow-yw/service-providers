<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2017/6/15
 * Time: 12:14
 */

namespace App\Services\Wechat;

use Zdp\ServiceProvider\Data\Models\ServiceProvider;
use Zdp\ServiceProvider\Data\Models\SpMember;
use Zdp\ServiceProvider\Data\Models\User;
use Zdp\ServiceProvider\Data\Models\WechatAccount;
use Zdp\ServiceProvider\Data\Utils\UserMenu;
use Zdp\ServiceProvider\Data\Utils\UserTag;

/**
 * 微信消息事件提醒等的处理
 * Class WechatService
 * @package App\Services\Wechat
 */
class WechatService
{
    /**
     * 微信会员标签的处理
     *
     * @param $openId string 会员OPENID
     */
    public function setUserWechatTag($openId)
    {
        // 是否在会员表中存在
        $userStatusArr = [
            User::ENDING,
            User::PASS,
        ];
        $userInfo = User::query()
            ->whereIn('status', $userStatusArr)
            ->where('wechat_openid', $openId)
            ->select(['sp_id'])
            ->first();
        if (!is_null($userInfo)) {
            $this->setTag($openId, $userInfo->sp_id, $tagName = UserTag::SIGNED_TAG_NAME);
        }
        // 是否在服务商表中存在
        $mainServiceInfo = ServiceProvider::query()
            ->where('wechat_openid', $openId)
            ->select(['zdp_user_id'])
            ->first();
        if (!is_null($mainServiceInfo)) {
            $this->setTag($openId, $mainServiceInfo->zdp_user_id, $tagName = UserTag::SHOP_TAG_NAME);
        }
        // 是否在服务商成员表中存在
        $serviceInfo = SpMember::query()
            ->where('wechat_openid', $openId)
            ->select(['sp_id'])
            ->first();
        if (!is_null($serviceInfo)) {
            $this->setTag($openId, $serviceInfo->sp_id, $tagName = UserTag::SHOP_TAG_NAME);
        }
    }

    /**
     * 给指定会员打标签
     *
     * @param string $openId  会员OPENID
     * @param string $spId    服务商ID
     * @param string $tagName 标签名称
     * @param string $spCode  服务商标识
     */
    private function setTag($openId, $spId, $tagName = UserTag::SIGNED_TAG_NAME, $spCode = '')
    {
        // 取得服务商标识
        if (empty($spCode)) {
            $spInfo = WechatAccount::query()->where('sp_id', $spId)->first();
            if (is_null($spInfo)) {
                return;
            }
            $spCode = $spInfo->source;
        }
        // 获得已设置的所有标签
        /** @var UserMenu $weMenuApp */
        $weMenuApp = new UserMenu($spCode);
        $userTags = $weMenuApp->getUserTag();
        $tags = collect($userTags['tags'])->keyBy('name')->toArray();
        $tag = array_get($tags, $tagName, 0);
        if (empty($tag)) {
            return;
        }
        $openIds = [$openId];
        $weMenuApp->setUserTag($openIds, $tag['id']);
    }
}
