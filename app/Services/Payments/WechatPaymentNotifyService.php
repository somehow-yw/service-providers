<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/20
 * Time: 10:48
 */

namespace App\Services\Payments;

use App\Events\OrderEvent;
use PayPack\WeChat\Core\Payment;
use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Models\OrderLog;
use Illuminate\Support\Facades\Log;

/**
 * 微信支付回调处理
 * Class WechatPaymentNotify
 * @package App\Services\Payments
 */
class WechatPaymentNotifyService
{
    /**
     * 回调处理
     * @return bool|string
     */
    public function paymentNotify()
    {
        $optionsArr = config('wechat');
        $paymentApp = new Payment($optionsArr);
        $notifyArr = $paymentApp->weChatNotify();
        $orderStatus = Order::getStatusByPaymentStatus($notifyArr['status']);
        try {
            $orderInfo = Order::query()
                ->where('order_no', $notifyArr['order_no'])
                ->where('status', Order::PAYING)
                ->first();
            $orderInfo->status = $orderStatus;
            $orderInfo->save();
            if ($orderStatus == Order::PAY_SUCCESS) {
                Log::info('wechat payment notice.', ['status' => $orderStatus]);
                event(new OrderEvent($orderInfo->id, $orderStatus, OrderLog::CUSTOMER));
            }
            $notifyArr['response'] = <<<XML
<xml>
<return_code><![CDATA[SUCCESS]]></return_code>
<return_msg><![CDATA[OK]]></return_msg>
</xml>
XML;
        } catch (\Exception $e) {
            $notifyArr['response'] = false;
        }

        return $notifyArr['response'];
    }
}
