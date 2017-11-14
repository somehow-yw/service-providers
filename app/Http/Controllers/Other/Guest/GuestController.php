<?php

namespace App\Http\Controllers\Other\Guest;


use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Zdp\ServiceProvider\Data\Models\SpMember;
use Zdp\ServiceProvider\Data\Models\SpMemberInvite;
use Zdp\ServiceProvider\Data\Models\WechatAccount;
use Zdp\ServiceProvider\Data\Utils\UserTag;

/**
 * Class PublicController
 */
class GuestController extends Controller
{
    /**
     * 更新微信配置
     *
     * @param Request $request
     *
     * @throws AppException
     */
    public function updateWeChatConfig(Request $request)
    {
        $this->validate(
            $request,
            [
                'appid'   => 'required|string',
                'secret'  => 'required|string',
                'token'   => 'required|string',
                'aes_key' => 'required|string',
            ]
        );

        $weChatAccounts = getWeChatAccount();

        if (
            !empty($weChatAccounts->appid) &&
            !empty($weChatAccounts->secret) &&
            !empty($weChatAccounts->token) &&
            !empty($weChatAccounts->aes_key)
        ) {
            throw new AppException("您的微信账号相关配置已经完善");
        }

        $weChatAccounts->update(
            [
                'appid'   => $request->input('appid'),
                'secret'  => $request->input('secret'),
                'token'   => $request->input('token'),
                'aes_key' => $request->input('aes_key'),
            ]
        );
    }
}