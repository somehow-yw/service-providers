<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 15:17
 */

namespace App\Exceptions\Shop;

use App\Exceptions\AppException;

/**
 * Class CustomerException.
 * 服务商错误提示
 * @package App\Exceptions\Customer
 */
class ShopException extends AppException
{
    const MAIN_INFO_DEL = [
        'code'    => '101',
        'message' => '用户信息错误',
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
