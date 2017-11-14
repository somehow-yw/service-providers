<?php

return [
    /*
     * Debug 模式，bool 值：true/false
     *
     * 当值为 false 时，所有的日志都不会记录
     */
    'debug'             => true,

    /*
     * 使用 Laravel 的缓存系统
     */
    'use_laravel_cache' => true,

    /*
     * 账号基本信息，请从微信公众平台/开放平台获取
     */
    'app_id'            => env('WECHAT_APPID', 'wxddca787d1553e4ab'), // AppID
    'secret'            => env('WECHAT_SECRET', '0d68b999818d29f163db87cd619a9aad'), // AppSecret
    'token'             => env('WECHAT_TOKEN', 'zhaoDongPing20150815134828'), // Token
    'aes_key'           => env('WECHAT_AES_KEY', 'o3fGNCs8uSzkPBjc6vEM0gFIzmL4WmBWeZbRbtTeESq'), // EncodingAESKey

    /**
     * 开放平台第三方平台配置信息
     */
    //'open_platform' => [
    /**
     * 事件推送URL
     */
    //'serve_url' => env('WECHAT_OPEN_PLATFORM_SERVE_URL', 'serve'),
    //],

    /*
     * 日志配置
     *
     * level: 日志级别，可选为：
     *                 debug/info/notice/warning/error/critical/alert/emergency
     * file：日志文件位置(绝对路径!!!)，要求可写权限
     */
    'log'               => [
        'level' => env('WECHAT_LOG_LEVEL', 'debug'),
        'file'  => env('WECHAT_LOG_FILE', storage_path('logs/wechat' . date('Y-m-d') . '.log')),
    ],

    /*
     * OAuth 配置
     *
     * only_wechat_browser: 只在微信浏览器跳转
     * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
     * callback：OAuth授权完成后的回调页地址(如果使用中间件，则随便填写。。。)
     */
    'oauth'             => [
        'only_wechat_browser' => env('ONLY_WECHAT_BROWSER', false),
        'scopes'              => array_map('trim', explode(',', env('WECHAT_OAUTH_SCOPES', 'snsapi_userinfo'))),
        'callback'            => env('WECHAT_OAUTH_CALLBACK', '/examples/oauth_callback.php'),
    ],

    /*
     * 微信支付
     */
    'payment'           => [
        'merchant_id'     => env('WECHAT_PAYMENT_MERCHANT_ID', '1449074502'),
        'key'             => env('WECHAT_PAYMENT_KEY', 'Gj73JT0L8Do2Gq3Do8Wvo6EiEFVonii7'),
        // XXX: 绝对路径！！！！
        'cert_path'       => env('WECHAT_PAYMENT_CERT_PATH', '/data/www/secrets/payment/wechat/apiclient_cert.pem'),
        // XXX: 绝对路径！！！！
        'key_path'        => env('WECHAT_PAYMENT_KEY_PATH', '/data/www/secrets/payment/wechat/apiclient_key.pem'),
        'device_info'     => env('WECHAT_PAYMENT_DEVICE_INFO', 'WEB'),
        'sub_app_id'      => env('WECHAT_PAYMENT_SUB_APP_ID', ''),
        'sub_merchant_id' => env('WECHAT_PAYMENT_SUB_MERCHANT_ID', ''),
        'notify_url'      => env('WECHAT_PAYMENT_NOTIFY_URL', ''),
        // ...
    ],

    /*
     * 开发模式下的免授权模拟授权用户资料
     *
     * 当 enable_mock 为 true 则会启用模拟微信授权，用于开发时使用，开发完成请删除或者改为 false 即可
     */
    'enable_mock'       => env('ENABLE_MOCK', false),
    'mock_user'         => [
        "openid"     => env('WECHAT_MOCK_OPENID', "odh7zsgI75iT8FRh0fGlSojc9Pmw"),
        // 以下字段为 scope 为 snsapi_userinfo 时需要
        "nickname"   => "fer",
        "sex"        => "1",
        "province"   => "北京",
        "city"       => "北京",
        "country"    => "中国",
        "headimgurl" => "http://wx.qlogo.cn/mmopen/C2rEUskXxziaSt5BATrlbx1GVzwW2qjUCqtYpDvIJLjKgP1ug/0",
    ],
    'main'              => [
        'app_id'      => 'wxddca787d1553e4ab',
        'merchant_id' => '1449074502',
        'payment_key' => 'Gj73JT0L8Do2Gq3Do8Wvo6EiEFVonii7',
    ],
];
