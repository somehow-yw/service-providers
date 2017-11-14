<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-2
 * Time: 下午1:47
 */

namespace App\Repositories\Customer;

use App\Models\User;
use App\Repositories\Customer\Contracts\UserRepository as Contract;

class UserRepository implements Contract
{
    /**
     * @inheritDoc
     */
    public function getUserProfile($userId)
    {
        return User::with('addresses', 'defaultAddress', 'shopType')
            ->where('id', $userId)
            ->first();
    }

    /**
     * @param       $id        integer 记录ID
     * @param array $updateArr array 修改的信息数组
     *
     *                         [
     *                             'user_name' => 'xxx',
     *                             ...
     *                         ]
     *
     * @return integer
     */
    public function updateInfoById($id, array $updateArr)
    {
        return User::where('id', $id)->update($updateArr);
    }

    /**
     * @inheritDoc
     */
    public function createUser($weChatUser, $mobile, $spId)
    {
        return User::create(
            [
                'wechat_openid'   => $weChatUser->getId(),
                'wechat_nickname' => $weChatUser->getNickname(),
                'wechat_avatar'   => $weChatUser->getAvatar(),
                'user_name'       => $weChatUser->getName(),
                'mobile_phone'    => $mobile,
                'status'          => User::ENDING,
                'sp_id'           => $spId,
            ]
        );
    }
}
