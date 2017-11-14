<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-8
 * Time: 下午3:10
 */

namespace App\Repositories\Customer;

use App\Repositories\Customer\Contracts\ShoppingCartRepository as Contract;
use Illuminate\Support\Collection;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\ServiceProvider\Data\Models\ShoppingCart;

class ShoppingCartRepository implements Contract
{
    /**
     * @inheritDoc
     */
    public function getShoppingCarts($userId)
    {
        /** @var Collection $shoppingItems */
        $shoppingItems = ShoppingCart::where('user_id', $userId)
                                     ->select(
                                         'goods_id',
                                         'buy_num'
                                     )
                                     ->get();

        if (empty($shoppingItems)) {
            return $shoppingItems;
        }
        $goodsIds = $shoppingItems->pluck('goods_id')->toArray();

        $sItemsKeyByGoodsId = $shoppingItems->keyBy('goods_id');

        $select = [
            'g.id',
            'g.id as goods_id',
            'g.goods_type_id as type_id',
            'g.brand_id as brand_id',
            'g.goods_title',
            'g.shenghe_act',
            'g.on_sale',
            'attr.goods_price',
            'attr.auto_soldout_time',
            'attr.meter_unit',
        ];

        $goods = $this->getGoodsInfo($goodsIds, $select);

        $gItemsKeyByGoodsId = $goods->keyBy('goods_id');

        $goods = array_key_by_merge(
            $sItemsKeyByGoodsId->toArray(),
            $gItemsKeyByGoodsId->toArray()
        );

        return collect($goods);
    }

    /**
     * @inheritDoc
     */
    public function getGoodsInfo($goodsIds, $select)
    {
        return DpGoodsInfo::from('dp_goods_info as g')
                          ->with([
                              'goodsPicture' => function ($query) {
                                  $query
                                      ->select(
                                          'goodsid',
                                          'ypic_path as goods_image'
                                      )
                                      ->whereIn('ordernum', [0, 1]);
                              },
                          ])
                          ->join(
                              'dp_goods_basic_attributes as attr',
                              'attr.goodsid',
                              '=',
                              'g.id'
                          )
                          ->whereIn('g.id', $goodsIds)
                          ->select($select)
                          ->get();
    }


}