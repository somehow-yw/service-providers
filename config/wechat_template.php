<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/30
 * Time: 14:19
 */

return [
    // 新订单通知模板配置
    'new_order'       => [
        'short_id'      => 'OPENTM203331384',
        'name'          => '新订单通知',
        'remark'        => '发货提醒：服务商收到订单后，消息模板下发至服务商',
        'template_data' => [
            'touser'      => '接收者OPENID',
            'template_id' => '消息模板ID',
            'url'         => '消息点击后的链接URL',
            'data'        => [
                'first'    => ['标题 如：你有新的订单，请及时发货。', '#173177'],
                'keyword1' => ['订单金额 如：￥145.25（已支付/货到付款）', '#173177'],
                'keyword2' => ['商品详情 如：商品名1+商品名N', '#173177'],
                'keyword3' => ['收货信息 如：店铺名，手机号，地址', '#173177'],
                'remark'   => ['备注信息 如：查看订单详情', '#173177'],
            ],
        ],
    ],

    // 订单取消通知
    'cancel_order'    => [
        'short_id'      => 'TM00850',
        'name'          => '订单取消通知',
        'remark'        => '买家取消订单通知服务商；服务商取消订单通知买家',
        'template_data' => [
            'touser'      => '接收者OPENID',
            'template_id' => '消息模板ID',
            'url'         => '消息点击后的链接URL',
            'data'        => [
                'first'             => ['标题 如：买(卖)家取消了您的订单。', '#173177'],
                'orderProductPrice' => ['订单金额 如：￥145.25', '#173177'],
                'orderProductName'  => ['商品详情 如：商品名1+商品名N', '#173177'],
                'orderAddress'      => ['收货信息 如：店铺名，手机号，地址', '#173177'],
                'orderName'         => ['订单编号', '#173177'],
                'remark'            => ['备注信息 如：查看详情', '#173177'],
            ],
        ],
    ],

    // 订单待确认通知(买家下单成功通知)
    'order_succeed'   => [
        'short_id'      => 'OPENTM202297555',
        'name'          => '订单确认通知',
        'remark'        => '买家提交订单，待卖家确认，发消息通知买家',
        'template_data' => [
            'touser'      => '接收者OPENID',
            'template_id' => '消息模板ID',
            'url'         => '消息点击后的链接URL',
            'data'        => [
                'first'    => ['标题 如：亲，您的订单已创建成功，我们会立即为您备货，并在第一时间内为您安排专人免费送货到家！订单详情如下：', '#173177'],
                'keyword1' => ['订单号', '#173177'],
                'keyword2' => ['商品名称 如：商品名1+商品名N', '#173177'],
                'keyword3' => ['订购数量 如：8', '#173177'],
                'keyword4' => ['订单总额 如：￥480元', '#173177'],
                'keyword5' => ['付款方式 如：货到付款', '#173177'],
                'remark'   => ['备注信息 如：联系我们：18435784564', '#173177'],
            ],
        ],
    ],

    // 发货提醒
    'order_shipments' => [
        'short_id'      => 'OPENTM207705155',
        'name'          => '订单确认通知',
        'remark'        => '发货提醒：订单出库提醒通知买家',
        'template_data' => [
            'touser'      => '接收者OPENID',
            'template_id' => '消息模板ID',
            'url'         => '消息点击后的链接URL',
            'data'        => [
                'first'    => ['标题 如：您的商品已发货，请注意收货。', '#173177'],
                'keyword1' => ['订单号', '#173177'],
                'keyword2' => ['商品信息 如：商品名1+商品名N', '#173177'],
                'keyword3' => ['商品数量 如：3', '#173177'],
                'keyword4' => ['商品金额 如：￥10.50元', '#173177'],
                'remark'   => ['备注信息 如：联系我们：18435784564', '#173177'],
            ],
        ],
    ],

    // 用户反馈提醒
    'feedback_remind' => [
        'short_id'      => 'OPENTM411628723',
        'name'          => '问题反馈提醒',
        'remark'        => '问题反馈提醒：用户反馈通知服务商',
        'template_data' => [
            'touser'      => '接收者OPENID',
            'template_id' => '消息模板ID',
            'url'         => '消息点击后的链接URL',
            'data'        => [
                'first'    => ['标题 如：您好，有用户向您反馈问题。', '#173177'],
                'keyword1' => ['反馈用户：如 买家店铺名+手机号', '#173177'],
                'keyword2' => ['反馈时间：如 2017-12-11 12:11:11', '#173177'],
                'keyword3' => ['反馈问题：如 价格收藏夹', '#173177'],
                'remark'   => ['备注信息 如：商品和订单问题...', '#173177'],
            ],
        ],
    ],

    // 各模板的不同标题配置
    'titles'          => [
        'new_order_title' => '你有新的订单，请及时发货',
        'order_succeed'   => '您的订单已提交，卖家确认后将安排发货',
    ],

    'urls' => [
        'new_order_url'      => "/index?page_route=/seller/ticket",
        'order_succeed_url'  => "/order",
        'cancel_order'       => '/index?page_route=/seller/ticket',
        'sp_cancel_order'    => '/order',
        'order_send_url'     => "/order",
        'shipments_send_url' => '/order',
        'feedback_remind_url'    => '?m=PublicTemplate&c=ApiPublic&a=userFeedBody&feedback_id=',
    ],
];
