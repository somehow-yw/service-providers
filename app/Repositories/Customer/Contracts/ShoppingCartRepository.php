<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-8
 * Time: 下午3:10
 */

namespace App\Repositories\Customer\Contracts;


use Illuminate\Support\Collection;

interface ShoppingCartRepository
{
    /**
     * 获取用户的购物车信息
     *
     * @param $userId
     *
     * @return Collection
     */
    public function getShoppingCarts($userId);

    /**
     * dp_goods_info as g, dp_goods_basic_attributes as attr
     *
     * @param $goodsIds
     * @param $select
     *
     * @return Collection
     */
    public function getGoodsInfo($goodsIds, $select);
}