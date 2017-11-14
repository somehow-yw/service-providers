<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/4
 * Time: 11:04
 */

namespace App\Repositories\Shop\Contracts;

/**
 * 主站会员数据
 * Interface MainUserRepository
 * @package App\Repositories\Shop\Contracts
 */
interface MainUserRepository
{
    /**
     * @param       $userId    integer 会员ID
     * @param array $selectArr array 获取字段
     *
     *                         [
     *                             'user'=>['select 1', ...],
     *                             'shop'=>['select 1', ...]
     *                         ]
     *
     * @return mixed
     */
    public function getUserAndShopInfoByUserId($userId, array $selectArr);
}
