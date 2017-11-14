<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/20
 * Time: 10:41
 */

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Payments\WechatPaymentNotifyService;

/**
 * 支付微信回调相关
 * Class PaymentController
 * @package App\Http\Controllers\Wechat
 */
class PaymentController extends Controller
{
    public function weChatNotify(Request $request, WechatPaymentNotifyService $notifyService)
    {
        $notifyService->paymentNotify();
    }
}
