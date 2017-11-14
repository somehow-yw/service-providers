<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 10:44
 *
 * 服务商客户的服务接口路由
 * 路由请求前缀 api/customer
 */

// =====================
//  需做微信登录验证的路由
// =====================

// 获取联系方式
Route::get('contact', 'CustomerController@getContactInfo');

Route::group(
    ['middleware' => 'wechat-oauth'],
    function () {
        // 服务商客户注册获取验证码
        Route::get('register/verify', 'CustomerController@getVerify');
        // 服务商客户注册状态
        Route::get('register/status', 'CustomerController@getRegisterStatus');
        // 服务商客户注册第一步
        Route::post('register/step1', 'CustomerController@register1');
        // 服务商客户注册第二步
        Route::post('register/step2', 'CustomerController@register2');
        // 会员添加收货地址
        Route::post('address', 'CustomerController@addAddress');
        // 会员修改收货地址
        Route::post('address/update', 'CustomerController@updateAddress');
        // 会员删除收货地址
        Route::post('address/del', 'CustomerController@delAddress');
        // 会员更改默认收货地址
        Route::post('main-address/update',
            'CustomerController@updateMainAddress');
        // 获取当前会员已有收货地址
        Route::get('user-addresses', 'CustomerController@getShippingAddresses');
        // 获取服务商客户信息
        Route::get('profile', 'CustomerController@getProfile');
        // 更新会员店铺信息
        Route::post('shop/update', 'CustomerController@updateShopInfo');
        // 获取商品详情
        Route::get('goods/{id}', 'GoodsController@getGoodsInfo');
        // 获取商品列表
        Route::post('goods', 'GoodsController@goods');
        // 获取商品的筛选项
        Route::post('goods/filters', 'GoodsController@filters');
        // 商品分类
        Route::get('sorts', 'GoodsController@getSorts');
        // 获取购物车列表
        Route::get('shopping-cart', 'ShoppingCartController@getShoppingCart');
        // 添加商品到购物车
        Route::post('shopping-cart/add',
            'ShoppingCartController@addGoodsToShoppingCart');
        // 购物车动态计算
        Route::post('shopping-cart/calc',
            'ShoppingCartController@calcShoppingCart');
        // 购物车修改
        Route::post('shopping-cart/update',
            'ShoppingCartController@updateShoppingCart');
        // 删除购物车中商品
        Route::post('shopping-cart/del', 'ShoppingCartController@del');
        // 订单信息列表
        Route::get('orders', 'OrderController@getOrderList');
        // 生成订单
        Route::post('orders', 'OrderController@generateOrder');
        // 订单操作（确认、发货、收货、取消等操作）
        Route::post('order/status/update', 'OrderController@updateOrderStatus');
        // 订单支付
        Route::post('order/payment', 'OrderController@payment');
        // 订单支付查询处理(主要是前端发起支付失败的订单进行主动查询处理)
        Route::post('order/payment/query', 'OrderController@paymentQuery');

        // 首页
        Route::get('home', 'Home@index'); // 获取首页状态及首页信息

        // 常购
        Route::get('collections', 'GoodsController@getCollections');
        Route::post('collections/add', 'GoodsController@addCollections');
        Route::post('collections/del', 'GoodsController@delCollections');
    }
);
