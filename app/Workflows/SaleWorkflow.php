<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/13
 * Time: 17:24
 */

namespace App\Workflows;

use Zdp\Main\Data\Services\Goods\ShoppingCartService;
use Zdp\ServiceProvider\Data\Models\OrderGoods;
use Zdp\ServiceProvider\Data\Models\Order;
use App\Exceptions\OrderException;

/**
 * 进货单
 * Class SaleWorkflow
 * @package App\Workflows
 */
class SaleWorkflow
{
    private $zdpShoppingCartService;

    public function __construct(ShoppingCartService $zdpShoppingCartService)
    {
        $this->zdpShoppingCartService = $zdpShoppingCartService;
    }

    /**
     * 商品进货
     *
     * @param $orderId     integer 订单ID
     * @param $goodsId     integer 商品ID
     * @param $purchaseNum integer 采购数量
     * @param $userId      integer 找冻品会员ID
     *
     * @throws \App\Exceptions\OrderException
     * @return void
     */
    public function goodsPurchase($orderId, $goodsId, $purchaseNum, $userId)
    {
        // 取得此订单信息
        $orderInfo = Order::query()->where('id', $orderId)
            ->where('sp_id', $userId)
            ->select(['id', 'order_no', 'user_id', 'goods_num', 'buy_count', 'status'])
            ->first();
        if (is_null($orderInfo)) {
            throw new OrderException(OrderException::NOT_ORDER);
        }
        $this->zdpShoppingCartService->addToCart($goodsId, $purchaseNum, $userId);
        // 更改订单中商品的进货状态
        OrderGoods::where('order_id', $orderId)
            ->where('goods_id', $goodsId)
            ->where('status', OrderGoods::NOT_PURCHASE)
            ->update(['status' => OrderGoods::PURCHASE]);
    }
}
