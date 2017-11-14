<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 10:46
 *
 * 微信处理接口路由
 * 路由请求前缀 api/wechat
 */

// ==================
//  通知处理
// ==================
// 微信请求（如：消息通知等）管理
Route::any('/', 'WechatController@noticeManage');

// ===============
//  微信支付回调
// ===============
Route::post('payment/notify', 'PaymentController@weChatNotify');

// ===============
//  分组处理
// ===============
Route::group(
    ['prefix' => 'group'],
    function () {
        Route::get('hello', function () {
            return "hello";
        });
    }
);

// ===============
//  菜单处理
// ===============
Route::group(
    ['prefix' => 'menu'],
    function () {
        //
    }
);
