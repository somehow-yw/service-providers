<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 15:17
 */

namespace App\Exceptions\Customer;

use App\Exceptions\AppException;

/**
 * Class CustomerException.
 * 会员错误提示
 * @package App\Exceptions\Customer
 */
class CustomerException extends AppException
{
    const ADDRESS_NOT_PART_YOU = [
        'code'    => '101',
        'message' => '只能修改自己的收货地址',
    ];

    const INFO_DEL = [
        'code'    => '102',
        'message' => '信息不存在',
    ];

    const OPTION_TYPE_NOT = [
        'code'    => '103',
        'message' => '操作类型不存在',
    ];

    const ADDRESS_NUM_OVER = [
        'code'    => '104',
        'message' => '收货地址已超过最大可设置数',
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
