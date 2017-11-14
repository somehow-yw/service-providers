<?php

// =====================
//  需做微信登录验证的路由
// =====================

// 获取店铺信息
Route::get('info', 'ShopController@getInfo');

// 更改市场
Route::post('markets', 'ShopController@updateMarkets');

// 获取可服务市场
Route::get('markets', 'ShopController@getMarketList');

// 更新支付方式
Route::post('payments', 'ShopController@updatePayMethods');

// 获取分类列表
Route::get('sorts', 'ShopController@getSorts');

// 获取分类加价列表
Route::get('sorts/prices', 'ShopController@getSortPrices');

// 服务商加价
Route::post('sorts/prices/rise', 'PriceController@rise');

// 客户列表
Route::get('customers', 'CustomerController@customers');

// 获取买家信息
Route::get('customers/{id}', 'CustomerController@getCustomer');

// 查看销售单
Route::get('sale', 'SaleController@index');

// 订单 取消(删除)、确定、发货、确认收货
Route::post('sale', 'SaleController@update');

// 服务商进货（将商品加入找冻品网的购物车）
Route::post('purchase', 'SaleController@goodsPurchase');

// 成员管理
Route::get('member/can', 'MemberController@hasRight');

// 获取添加成员的URL
Route::get('member/url', 'MemberController@share');

// 获取成员列表
Route::get('member/all', 'MemberController@all');

// 删除成员
Route::post('member/remove', 'MemberController@delete');

// -- 屏蔽商品 --

// 获取屏蔽列表
Route::get('goods/blacklist', 'GoodsController@getBlacklist');

// 添加屏蔽项
Route::post('goods/blacklist/add', 'GoodsController@addBlacklist');

// 删除屏蔽箱
Route::post('goods/blacklist/remove', 'GoodsController@removeBlacklist');

// ** 屏蔽商品 **

// -- 置顶商品 --

// 获取置顶列表
Route::get('goods/stick', 'GoodsController@getSticks');

// 重设置顶列表
Route::post('goods/stick', 'GoodsController@resetSticks');

// ** 置顶商品 **

// -- 冻品商城 --

// 获取商品详情
Route::get('goods/{id}', 'GoodsController@getGoodsInfo');

// 获取商品列表
Route::post('goods', 'GoodsController@goods');

// 获取商品的筛选项
Route::post('goods/filters', 'GoodsController@filters');

// ** 冻品商城 **

// -- 权限相关 --

// 获取当前用户权限列表
Route::get('permission', 'PermissionController@current');

// 获取某个用户的权限
Route::post('permission/member', 'PermissionController@member');

// 设置某个用户的权限
Route::post('permission/member/edit', 'PermissionController@edit');