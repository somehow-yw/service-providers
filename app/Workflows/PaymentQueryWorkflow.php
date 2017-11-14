<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/20
 * Time: 11:56
 */

namespace App\Workflows;

use App\Exceptions\OrderException;
use App\Services\Payments\WechatPaymentQueryService;
use Carbon\Carbon;
use Zdp\ServiceProvider\Data\Models\Order;

class PaymentQueryWorkflow
{
    /**
     * 支付订单主动查询
     *
     * @param $orderNo string 订单编号
     */
    public function paymentQuery($orderNo)
    {
        $carbon = Carbon::now();
        
        $queryEndDate = $carbon->subMinute(15)->format('Y-m-d H:i:s');

        $query = Order::query()
                      ->with('shop.wechatAccount')
                      ->where('status', Order::PAYING)
                      ->select(['id', 'order_no', 'sp_id', 'payment']);

        if (!empty($orderNo)) {
            $query = $query->where('order_no', $orderNo);
        } else {
            $query = $query->where('updated_at', '<', $queryEndDate);
        }
        $orderInfoCollection = $query->get();

        if ($orderInfoCollection->isEmpty()) {
            echo '没有需要处理的订单';

            return;
        }

        foreach ($orderInfoCollection as $item) {
            $service = $this->getServiceObj($item->payment);
            if (is_null($service)) {
                echo "{$item->id}—>未知付款方式\n";
            } else {
                echo "{$item->id}—>处理";
                $service->paymentQuery($item);
                echo "成功 \n";
            }
        }
    }

    /**
     * 前端主动查询支付中的订单状态
     *
     * @param $orderId integer 订单ID
     *
     * @return array
     * @throws \App\Exceptions\OrderException
     */
    public function paymentQueryByOrderId($orderId)
    {
        $orderInfo = Order::query()
                          ->with('shop.wechatAccount')
                          ->where('status', Order::PAYING)
                          ->where('id', $orderId)
                          ->select(['id', 'order_no', 'sp_id', 'payment'])
                          ->first();
        if (is_null($orderInfo)) {
            throw new OrderException(OrderException::NOT_PAY_QUERY_ORDER);
        }
        $service = $this->getServiceObj($orderInfo->payment);
        if (is_null($service)) {
            throw new OrderException(OrderException::PAY_METHOD_ERROR);
        }
        $paymentStatus = $service->paymentQuery($orderInfo);

        return ['order_status' => $paymentStatus];
    }

    /**
     * 获得需要处理的服务
     *
     * @param $payment integer 支付方式
     *
     * @return WechatPaymentQueryService|null
     */
    protected function getServiceObj($payment)
    {
        $queryService = null;
        switch ($payment) {
            case Order::WECHAT_PAY:
                /** @var WechatPaymentQueryService $queryService */
                $queryService = app(WechatPaymentQueryService::class);
                break;
        }

        return $queryService;
    }
}
