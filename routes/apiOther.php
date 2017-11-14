<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 10:48
 *
 * 其它公共数据通用接口路由
 * 路由请求前缀 api/other
 */

// =====================
//  需做微信登录验证的路由
// =====================
Route::group(
    ['namespace' => 'Common', 'middleware' => 'wechat-oauth'],
    function () {
        // 初始化服务商标签记录其openid
        Route::post('activate', 'CommonController@activate');
        // 获取该注册用户的地址(四川成都的服务商客户只能选择四川成都)
        Route::get('shop/location', 'CommonController@getShopLocation');
        // 获取店铺所有分类
        Route::get('shop/categories', 'CommonController@getShopCategories');
        // 获取所有可支付方式
        Route::get('payments', 'CommonController@getPayments');
        // 获取所有可配送方式
        Route::get('delivery/list', 'CommonController@getDeliveryList');
        // 获取服务商基本信息
        Route::get('sp-info', 'CommonController@getSpInfo');
        // 用户反馈写入
        Route::post('feedback', 'FeedbackController@insertUserFeedback');
        // 获取用户反馈详情
        Route::get('get-feedback', 'FeedbackController@getUserFeedback');
    }
);

Route::group(
    ['namespace' => 'Common'],
    function () {
        // 获取中国所有省
        Route::get('provinces', 'CommonController@getProvinces');
        // 获取某区域下的所有子区域
        Route::get('children/{id}', 'CommonController@getChildren')
             ->where('id', '[0-9]+');
    }
);

// ==========================
//  游客路由，所有无需授权的接口
// ==========================
Route::group(
    ['namespace' => 'Guest'],
    function () {
        Route::post('wechat-config', 'GuestController@updateWeChatConfig');
    }
);
