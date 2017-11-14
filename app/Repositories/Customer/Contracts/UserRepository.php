<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-2
 * Time: 下午1:47
 */

namespace App\Repositories\Customer\Contracts;


use Overtrue\Socialite\UserInterface;

interface UserRepository
{
    /**
     * 获取用户信息
     *
     * @param $userId
     *
     * @return mixed
     */
    public function getUserProfile($userId);

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
    public function updateInfoById($id, array $updateArr);

    /**
     * 创建用户
     *
     * @param $weChatUser UserInterface
     * @param $mobile
     * @param $spId
     *
     * @return mixed
     */
    public function createUser($weChatUser, $mobile, $spId);
}
