<?php

/*
|--------------------------------------------------------------------------
| 冻参谋管理后台接口
|--------------------------------------------------------------------------
*/

Route::domain(config('admin.domain'))->namespace('Admin')->group(function () {

    Route::middleware(['web-admin', 'admin-not-login'])->group(function () {

        // 登录页面
        Route::get('/user/login', function () {
            return view('blades.admin.login');
        });

        Route::post('/user/login', 'User@login');

    });

    Route::middleware(['web-admin', 'admin-login'])->group(function () {

        // 后台管理页面
        Route::get('/', function () {

            return view('blades.admin.index', [
                'csqr' => config('admin.csqr'), // 客服二维码
            ]);

        });

        // 退出登录
        Route::get('/user/logout', 'User@logout');

        // 基础统计
        Route::get('/statics', 'Statics@index');

        // 客户分析
        Route::post('/statics/custom', 'Statics@custom');

        // 客户名单
        Route::get('/custom', 'Custom@index');

        // 修改用户信息
        Route::post('/custom/{user}', 'Custom@update');

        // 店铺信息
        Route::get('/user/info', 'User@getInfo');

        // 修改店铺信息
        Route::post('/user/info', 'User@updateInfo');

        // 获取分类列表
        Route::get('/goods/categories', 'Goods@categories');

        // 获取品牌列表
        Route::get('/goods/brands', 'Goods@brands');

        // 获取某个四级分类下的品牌列表(带搜索条件)
        Route::get('/goods/category/brands', 'Goods@categoryBrands');

        // 获取某个四级分类下的品牌列表搜索hint
        Route::get('/goods/category/brands/hint', 'Goods@categoryBrandsHint');

        // 屏蔽分类品牌
        Route::post('/goods/category/blacklist', 'Goods@addBlacklist');

        // 取消屏蔽分类品牌
        Route::post('/goods/category/blacklist/cancel',
            'Goods@removeBlacklist');

        // 置顶商品
        Route::post('/goods/category/stick', 'Goods@resetSticks');

        // 改价
        Route::post('/goods/category/markup', 'Goods@markUp');

        // 商品详情
        Route::get('/goods/{id}', 'Goods@detail');
        // 商品列表
        Route::post('/goods', 'Goods@goods');

        // 首页管理
        Route::get('/home/status', 'Home@isEnable'); // 获取首页是否开启
        Route::post('/home/status', 'Home@enable'); // 开启关闭首页
        Route::get('/home/hot/category', 'Home@hotGoodsCategories'); // 获取热门分类
        Route::post('/home/hot/category',
            'Home@resetHotGoodsCategory'); // 重置热门分类
        Route::get('/home/hot/brand', 'Home@hotGoodsBrand'); // 获取热门品牌
        Route::post('home/hot/brand', 'Home@resetHotGoodsBrand'); // 重置热门品牌
        Route::get('/home/hot/goods', 'Home@hotGoods'); // 获取推荐商品
        Route::post('/home/hot/goods', 'Home@resetHotGoods'); // 重置推荐商品

        Route::post('/cart/upload', 'Cart@upload'); // 上传购物车
        Route::get('/cart/download', 'Cart@download'); // 下载两日进货订单记录
    });

});