<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::group(
    ['middleware' => 'wechat-oauth'],
    function () {
        // 添加成员
        Route::get('/new-shop-member/{hash}', 'WebController@newShopMember')
             ->where('hash', '[\w]{8}')
             ->name('new-shop-member');
        // 首页判断
        Route::get('/home', 'WebController@home');
        // 静态页面输出路由
        Route::get('/{page_name}', 'WebController@pullWebPage')
             ->name('static_web');
    }
);
