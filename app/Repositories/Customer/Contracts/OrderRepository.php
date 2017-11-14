<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/9
 * Time: 15:18
 */

namespace App\Repositories\Customer\Contracts;

/**
 * 会员订单管理数据处理
 * Interface OrderRepository
 * @package App\Repositories\Customer\Contracts
 */
interface OrderRepository
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
    public function getOrderList($status, $size, $userId);

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
    public function updateOrderStatus($orderId, $status, $source, $sourceId);

    /**
     * 取得订单信息
     *
     * @param $orderId integer 订单ID
     * @param $buyerId integer 买家ID
     *
     * @throws \App\Exceptions\OrderException
     * @return \Zdp\ServiceProvider\Data\Models\Order
     */
    public function getOrderInfo($orderId, $buyerId);
}
