<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:35
 */

namespace App\Exceptions\WeChat;

use App\Exceptions\AppException;

class WeChatException extends AppException
{
    const SERVICE_PROVIDERS_NOT = [
        'code'    => '101',
        'message' => '此服务商不存在',
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
