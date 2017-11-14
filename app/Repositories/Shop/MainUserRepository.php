<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/4
 * Time: 11:05
 */

namespace App\Repositories\Shop;

use App\Repositories\Shop\Contracts\MainUserRepository as MainUserInterface;
use Zdp\Main\Data\Models\DpShangHuInfo;

/**
 * 主站会员数据
 * Class MainUserRepository
 * @package App\Repositories\Shop
 */
class MainUserRepository implements MainUserInterface
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
    public function getUserAndShopInfoByUserId($userId, array $selectArr)
    {
        return DpShangHuInfo::with([
            'shop' => function ($query) use ($selectArr) {
                $query->select($selectArr['shop']);
            },
        ])->select($selectArr['user'])
            ->where('shId', $userId)
            ->where('shengheAct', DpShangHuInfo::STATUS_PASS)
            ->first();
    }
}
