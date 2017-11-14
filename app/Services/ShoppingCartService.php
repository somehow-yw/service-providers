<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-8
 * Time: 下午2:55
 */

namespace App\Services;


use App\Exceptions\AppException;
use App\Repositories\Customer\Contracts\ShoppingCartRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\ServiceProvider\Data\Models\GoodsCategoryBrand;
use Zdp\ServiceProvider\Data\Models\Markup;
use Zdp\ServiceProvider\Data\Models\ShoppingCart;

class ShoppingCartService
{
    protected $shoppingCartRepo;

    /**
     * ShoppingCartService constructor.
     *
     * @param ShoppingCartRepository $shoppingCartRepo
     */
    public function __construct(ShoppingCartRepository $shoppingCartRepo)
    {
        $this->shoppingCartRepo = $shoppingCartRepo;
    }

    /**
     * 获取用户购物车信息
     *
     * @param $userId integer 用户id
     *
     * @return array
     */
    public function getShoppingCart($userId)
    {
        $goods = $this->shoppingCartRepo->getShoppingCarts($userId);
        $shoppingInfo = $this->handleShoppingGoods($goods);

        return $shoppingInfo;
    }

    /**
     * 计算购物车
     *
     * @param $goodsArr                     array [
     *                                      [
     *                                      "goods_id":155,
     *                                      "buy_num":3
     *                                      ],
     *                                      [
     *                                      ....
     *                                      ]
     *                                      ]
     *
     * @return array
     */
    public function calcShoppingCart($goodsArr)
    {
        $goodsCount = array_sum(array_pluck($goodsArr, 'buy_num'));
        $buyNumMap = collect($goodsArr)->mapWithKeys(function ($item) {
            return [$item['goods_id'] => $item['buy_num']];
        });

        $goodsIds = array_pluck($goodsArr, 'goods_id');
        $select = [
            'g.id',
            'g.goods_type_id as sort_id',
            'g.brand_id as brand_id',
            'attr.goods_price',
            'attr.meter_unit',
        ];

        $goods = $this->shoppingCartRepo->getGoodsInfo($goodsIds, $select);

        $spId = getServiceProvider()->zdp_user_id;

        $markUpMapArr = GoodsCategoryBrand::getMarkups($spId);

        $totalAmount = 0.0;

        $goods = $goods->toArray();

        foreach ($goods as $item) {
            $goodsId = $item['id'];
            $sortId = $item['sort_id'];
            $brandId = $item['brand_id'];
            $price = $item['goods_price'];
            $unit = $item['meter_unit'];

            $price = GoodsCategoryBrand::markUpPrice(
                $sortId,
                $brandId,
                $price,
                $markUpMapArr,
                $unit
            );

            $totalAmount += (float)$price * $buyNumMap[$goodsId];
        }

        return [
            'goods_count'  => $goodsCount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * 更新购物车(商品数量)
     *
     * @param $goodsArr
     *
     * @return array
     */
    public function updateShoppingCart($goodsArr)
    {
        $userId = getUser()->id;
        /** @var Builder $query */
        $baseQuery = ShoppingCart::where('user_id', $userId);
        foreach ($goodsArr as $item) {
            $query = clone $baseQuery;
            $query->where('goods_id', $item['goods_id'])
                  ->update(['buy_num' => $item['buy_num']]);
        }
    }

    /**
     * 添加商品到购物车
     *
     * @param $goodsId
     * @param $buyNum
     */
    public function addGoodsToShoppingCart($goodsId, $buyNum)
    {
        $userId = getUser()->id;
        /** @var Builder $query */
        $goods = ShoppingCart::where('user_id', $userId)
                             ->where('goods_id', $goodsId)
                             ->first();
        if (empty($goods)) {
            $shoppingCart = new ShoppingCart([
                'user_id'  => $userId,
                'goods_id' => $goodsId,
                'buy_num'  => $buyNum,
            ]);
            $shoppingCart->save();
        } else {
            $goods->buy_num += $buyNum;
            $goods->save();
        }
    }

    /**
     * 处理
     *
     * @param $goods Collection
     *
     * @return array
     */
    protected function handleShoppingGoods($goods)
    {
        $spId = getServiceProvider()->zdp_user_id;

        $markUpMapArray = GoodsCategoryBrand::getMarkups($spId);

        $goods = $goods->toArray();

        foreach ($goods as $key => $item) {
            $typeId = $item['type_id'];
            $brandId = $item['brand_id'];
            $price = $item['goods_price'];
            $unit = $item['meter_unit'];

            $item['goods_price'] = GoodsCategoryBrand::markUpPrice(
                $typeId,
                $brandId,
                $price,
                $markUpMapArray,
                $unit
            );

            $item['goods_price'] = (float)$item['goods_price'];

            if (
                $item["shenghe_act"] != DpGoodsInfo::STATUS_NORMAL ||
                $item['on_sale'] != DpGoodsInfo::GOODS_SALE ||
                $item['auto_soldout_time'] <= date('Y-m-d H:i:s')
            ) {
                $item['available'] = false;
            } else {
                $item['available'] = true;
            }

            if (empty($item['goods_picture'][0])) {
                $item['image'] = [];
            } else {
                $item['image'] = $item['goods_picture'][0]['goods_image'];
            }

            // remove useless keys
            unset($item['id']);
            unset($item['type_id']);
            unset($item['on_sale']);
            unset($item['shenghe_act']);
            unset($item['goods_picture']);
            unset($item['auto_soldout_time']);
            unset($item['meter_unit']);

            $goods[$key] = $item;
        }

        return [
            'goods' => $goods,
        ];
    }

    /**
     * 用户删除购物车商品
     *
     * @param       $userId
     * @param array $orderIds
     *
     * @throws AppException
     */
    public function del($userId, array $orderIds)
    {
        // 删除订单
        ShoppingCart::where('user_id', $userId)
                    ->whereIn('goods_id', $orderIds)
                    ->delete();
    }
}