<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/9
 * Time: 15:18
 */

namespace App\Repositories\Customer;

use App\Exceptions\OrderException;
use App\Repositories\Customer\Contracts\OrderRepository as OrderContract;
use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Models\OrderLog;
use DB;

/**
 * 会员订单管理数据处理实现
 * Class OrderRepository
 * @package App\Repositories\Customer
 */
class OrderRepository implements OrderContract
{
    /**
     * 获取订单列表信息
     *
     * @param $status integer 获取状态 前端请求状态
     * @param $size   integer 获取数据量
     * @param $userId integer
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getOrderList($status, $size, $userId)
    {
        $orderSelectArr = [
            'id',
            'order_no',
            'goods_num',
            'buy_count',
            'order_amount',
            'payment',
            'delivery',
            'consignee_info',
            'status',
            'created_at',
        ];

        $query = Order::where('user_id', $userId)
            ->select($orderSelectArr);

        switch ($status) {
            case Order::NEW_ORDER:
                // 新订单 待确认
                $query = $query->where('status', Order::NEW_ORDER);
                break;
            case Order::UNDELIVERED:
                // 已确认 待发货
                $query = $query->where('status', Order::UNDELIVERED);
                break;
            case Order::DELIVERING:
                $query = $query->where('status', Order::DELIVERING);
                // 已发货 待收货
                break;
            default:
                ;
        }
        $query = $query->orderBy('id', 'desc');

        return $query->paginate($size);
    }

    /**
     * 订单状态变化操作
     *
     * @param $orderId  integer 订单ID
     * @param $status   integer 操作后订单状态值
     * @param $source   integer 来源值 对应订单日志表order_logs->source
     * @param $sourceId integer 操作者ID 对应订单日志表order_logs->user_id
     *
     * @return integer
     */
    public function updateOrderStatus($orderId, $status, $source, $sourceId)
    {
        $updateNum = 0;
        DB::transaction(function () use ($orderId, $status, $source, $sourceId, &$updateNum) {
            // 更改订单状态
            $updateNum = 0;
            $orderUpdateArr = ['status' => $status];
            switch ($status) {
                case Order::UNDELIVERED:
                    // 已确认 待发货
                    $updateNum = Order::query()
                        ->where('id', $orderId)
                        ->where('sp_id', $sourceId)
                        ->where(function ($query) {
                            $query->where(function ($query) {
                                // 非在线付款
                                $paymentArr = [
                                    Order::CASH_ON_DELIVERY,
                                ];
                                $query->where('status', Order::NEW_ORDER)
                                    ->whereIn('payment', $paymentArr);
                            })->orWhere(function ($query) {
                                // 在线付款
                                $paymentArr = [
                                    Order::WECHAT_PAY,
                                ];
                                $query->where('status', Order::PAY_SUCCESS)
                                    ->whereIn('payment', $paymentArr);
                            });
                        })
                        ->update($orderUpdateArr);
                    break;
                case Order::DELIVERING:
                    // 已发货 待收货
                    $updateNum = Order::query()
                        ->where('id', $orderId)
                        ->where('status', Order::UNDELIVERED)
                        ->where('sp_id', $sourceId)
                        ->update($orderUpdateArr);
                    break;
                case Order::RECEIVED:
                    // 已收货
                    $updateNum = Order::query()
                        ->where('id', $orderId)
                        ->where('status', Order::DELIVERING)
                        ->update($orderUpdateArr);
                    break;
                case Order::CANCELED:
                    // 已取消
                    $updateNum = Order::query()
                        ->where('id', $orderId)
                        ->whereIn('status', [Order::NEW_ORDER, Order::UNDELIVERED, Order::PAY_SUCCESS])
                        ->update($orderUpdateArr);
                    break;
                default:
                    ;
            }
            // 写日志
            if ($updateNum) {
                $orderLogCreateArr = [
                    'order_id'  => $orderId,
                    'operation' => $status,
                    'source'    => $source,
                    'user_id'   => $sourceId,
                ];
                OrderLog::query()->create($orderLogCreateArr);
            }
        });

        return $updateNum;
    }

    /**
     * 取得订单信息
     *
     * @param $orderId integer 订单ID
     * @param $buyerId integer 买家ID
     *
     * @throws \App\Exceptions\OrderException
     * @return Order
     */
    public function getOrderInfo($orderId, $buyerId)
    {
        $selectArr = [
            'order' => ['id', 'order_no', 'user_id', 'order_amount', 'payment', 'status'],
            'buyer' => ['id', 'wechat_openid', 'mobile_phone', 'user_name', 'shop_name'],
        ];
        $orderInfoModel = Order::query()->with([
            'buyer' => function ($query) use ($selectArr) {
                $query->select($selectArr['buyer']);
            },
            'orderGoods',
        ])
            ->select($selectArr['order'])
            ->where('id', $orderId)
            ->where('user_id', $buyerId)
            ->first();
        if (is_null($orderInfoModel)) {
            throw new OrderException(OrderException::NOT_ORDER);
        }

        return $orderInfoModel;
    }
}
