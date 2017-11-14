<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/31 0031
 * Time: 下午 5:36
 */
namespace App\Exceptions\Feedback;

use App\Exceptions\AppException;

class FeedbackException extends AppException
{
    // 用户名不存在
    const USER_NOT_EXIST = 104;

    // 微信图片下载失败
    const DOWNLOAD_FAIL = 105;
    
    // 图片上传到OSS失败
    const UPLOAD_FAIL = 106;
    
    // 路径创建失败
    const PATH_CREATE_FAIL = 107;
    /**
     * OrderBuy constructor.
     *
     * @param string $message
     * @param null   $code
     */
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}