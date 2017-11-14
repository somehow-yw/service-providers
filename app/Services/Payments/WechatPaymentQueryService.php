<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/20
 * Time: 12:24
 */

namespace App\Services\Payments;

use App\Events\OrderEvent;
use PayPack\WeChat\Core\Payment;
use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Models\OrderLog;

class WechatPaymentQueryService
{
    /**
     * 订单支付处理(主动查询支付单信息)
     *
     * @param $orderInfo \Zdp\ServiceProvider\Data\Models\Order 订单信息
     *
     * @return integer
     */
    public function paymentQuery($orderInfo)
    {
        $optionsArr = config('wechat');
        $subWechatInfo = $orderInfo->shop->wechatAccount;

        $paymentConfArr = [
            'app_id'          => $optionsArr['main']['app_id'],
            'merchant_id'     => $optionsArr['main']['merchant_id'],
            'sub_app_id'      => $subWechatInfo->appid,//'wx129e040711acb928'
            'sub_merchant_id' => $subWechatInfo->merchant_id,//1454959502
        ];

        $optionsArr['payment'] =
            array_merge($optionsArr['payment'], $paymentConfArr);

        $paymentApp = new Payment($optionsArr);
        $status = $paymentApp->getWeChatPay($orderInfo->order_no);
        $orderStatus = Order::getStatusByPaymentStatus($status);

        $orderInfo->status = $orderStatus;
        $orderInfo->save();

        if ($orderStatus == Order::PAY_SUCCESS) {
            event(new OrderEvent($orderInfo->id, $orderStatus,
                OrderLog::CUSTOMER));
        }

        return $orderStatus;
    }
}
