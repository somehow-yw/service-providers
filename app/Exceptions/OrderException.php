<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/10
 * Time: 11:01
 */

namespace App\Exceptions;

/**
 * 订单异常处理
 * Class OrderException
 * @package App\Exceptions
 */
class OrderException extends AppException
{
    const NOT_ORDER = [
        'code'    => '101',
        'message' => '订单不存在',
    ];

    const PERMISSION_ERR = [
        'code'    => '102',
        'message' => '您无权操作此订单',
    ];

    const NOT_PAY = [
        'code'    => '103',
        'message' => '此订单不可进行在线支付',
    ];

    const NOT_PAY_QUERY_ORDER = [
        'code'    => '104',
        'message' => '没有需要支付处理的订单',
    ];

    const PAY_METHOD_ERROR = [
        'code'    => '105',
        'message' => '未知支付方式',
    ];

    public function __construct($errorConst, $message = '', $code = null)
    {
        if (!empty($errorConst['code'])) {
            $message = $errorConst['message'];
            $code = $errorConst['code'];
        }
        parent::__construct($message, $code);
    }
}
