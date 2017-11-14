<?php

return [

    'session' => [
        'open_id' => env('ADMIN_OPENID', 'admin_open_id'),
    ],

    'domain' => env('APP_DEBUG', false) ? 'test.dongpin.me' : 'dongpin.me',

    'urls' => [
        'login' => '/user/login',
        'index' => '/',
    ],

    'csqr' => '/public/Public/pc/dcanmou-client/img/contactus.png',

    'token' => [
        'duration' => 1, // 登录凭证过期时间 1 分钟
    ],

];