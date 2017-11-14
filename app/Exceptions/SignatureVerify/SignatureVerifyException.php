<?php

/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/2/22
 * Time: 14:27
 */

namespace App\Exceptions\SignatureVerify;

use App\Exceptions\AppException;

class SignatureVerifyException extends AppException
{
    const REQUEST_FAILURE = [
        'code'    => '101',
        'message' => '请求方式不正确',
    ];

    const SIGNATURE_FAILURE = [
        'code'    => '102',
        'message' => '签名错误',
    ];

    const TIMESTAMP_NOT_FAILURE = [
        'code'    => '103',
        'message' => '参与签名的时间戳必须传入',
    ];

    const SIGNA_NOT_FAILURE = [
        'code'    => '104',
        'message' => '生成的签名串必须传入',
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
