<?php

namespace App\Services;

use App\Events\NewWeChatMessage;
use App\Events\OrderEvent;
use App\Exceptions\AppException;
use App\Models\ShippingAddress;
use App\Repositories\Customer\Contracts\OrderRepository;
use App\Repositories\Customer\Contracts\ShoppingCartRepository;
use App\Repositories\Shop\Contracts\MainGoodsRepository;
use App\Utils\GenerateRandomNumber;
use App\Utils\MoneyUnitConvertUtil;
use App\Utils\WechatTemplate;
use DB;
use Illuminate\Database\Eloquent\Collection;
use PayPack\WeChat\Core\Payment;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\ServiceProvider\Data\Models\GoodsCategoryBrand;
use Zdp\ServiceProvider\Data\Models\Markup;
use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Models\OrderGoods;
use Zdp\ServiceProvider\Data\Models\OrderLog;
use Zdp\ServiceProvider\Data\Models\ShoppingCart;
use App\Exceptions\OrderException;
use Event;
use Carbon\Carbon;

/**
 * Class OrderService
 *
 * @package App\Services
 */
class OrderService
{
    protected $shoppingCartRepo;
    protected $mainGoodsRepo;
    protected $goodsTitles;
    private   $orderRepo;

    public function __construct(
        ShoppingCartRepository $shoppingCartRepo,
        MainGoodsRepository $mainGoodsRepo,
        OrderRepository $orderRepo
    ) {
        $this->shoppingCartRepo = $shoppingCartRepo;
        $this->mainGoodsRepo = $mainGoodsRepo;
        $this->orderRepo = $orderRepo;
    }

    /**
     * 生成订单的所有逻辑
     *
     * @param $addressId  integer
     * @param $goods      array     [
     *                    {
     *                    "goods_id":111,
     *                    " buy_num":3
     *                    },
     *                    {
     *                    "goods_id":171,
     *                    "buy_num":1
     *                    },{...}
     *                    ]
     * @param $payment    integer 支付方式
     * @param $delivery   integer 配送方式
     */
    public function generateOrder($addressId, $goods, $payment, $delivery)
    {
        $user = getUser();
        $serviceProvider = getServiceProvider();

        // 收货人信息
        /** @var ShippingAddress $address */
        $address = ShippingAddress::find($addressId);
        $consigneeInfo = [
            'address'  => $address->full_address,
            'receiver' => $address->receiver,
            'mobile'   => $address->mobile,
        ];

        $self = $this;
        $newOrderId = 0;
        DB::transaction(function () use (
            $self,
            $serviceProvider,
            $consigneeInfo,
            $user,
            $goods,
            $payment,
            $delivery,
            &$newOrderId
        ) {
            // 创建订单
            $order = $self->createOrder(
                $user->id,
                $serviceProvider->zdp_user_id,
                $goods,
                $payment,
                $delivery,
                $consigneeInfo
            );
            // 移除购物车
            $this->removeGoodsFromCart($user->id,
                array_column($goods, 'goods_id'));

            $newOrderId = $orderId = $order->id;
            // 生成订单商品
            $self->generateOrderLog($orderId, $user->id);

            $orderAmount = $order->order_amount;

            // 发送微新通知消息
            $user = getUser();
            // 如果货到付款直接发送模板消息给服务商
            /** @var WechatTemplate $wechatTemplate */
            $paymentName = Order::$paymentNameArr[$payment];
            if ($payment == Order::CASH_ON_DELIVERY) {
                $newOrderTemplate = generate_wechat_template('new_order', [
                    'url'  => config('wechat_template.urls.new_order_url'),
                    'data' => [
                        'first'    => [config('wechat_template.titles.new_order_title')],
                        'keyword1' => ["￥{$orderAmount}元 ({$paymentName})"],
                        'keyword2' => [$this->goodsTitles],
                        'keyword3' => [
                            "联系人:{$consigneeInfo['receiver']}" .
                            "联系电话:{$consigneeInfo['mobile']}" .
                            "收货地址:{$consigneeInfo['address']}",
                        ],
                        'remark'   => ['查看订单详情'],
                    ],
                ]);

                Event::fire(
                    'new_wechat_msg',
                    new NewWeChatMessage($serviceProvider->subscribers->toArray(),
                        $newOrderTemplate)
                );
            }

            // 发送模板消息给用户
            $orderSucceedTemplate = generate_wechat_template('order_succeed', [
                'url'  => config('wechat_template.urls.order_succeed_url'),
                'data' => [
                    'first'    => [config('wechat_template.titles.order_succeed')],
                    'keyword1' => ["{$order->order_no}"],
                    'keyword2' => ["{$this->goodsTitles}"],
                    'keyword3' => ["{$order->goods_num}种商品"],
                    'keyword4' => ["{$order->order_amount}元"],
                    'keyword5' => ["{$paymentName}"],
                    'remark'   => ["联系我们：{$serviceProvider->mobile}"],
                ],
            ]);

            Event::fire(
                'new_wechat_msg',
                new NewWeChatMessage([$user->wechat_openid],
                    $orderSucceedTemplate)
            );
        });

        return $newOrderId;
    }

    /**
     * 生成订单
     *
     * @param $userId
     * @param $spId
     * @param $goods
     * @param $payment
     * @param $delivery
     * @param $consigneeInfo
     *
     * @return Order
     * @throws AppException
     */
    protected function createOrder(
        $userId,
        $spId,
        $goods,
        $payment,
        $delivery,
        $consigneeInfo
    ) {
        /** @var array $goodsIds 订单中的商品id数组 */
        $goodsIds = array_pluck($goods, 'goods_id');

        /** @var Collection $buyNumMapArr [商品id=>购买数量] 映射数组 */
        $buyNumMapArr = collect($goods)->mapWithKeys(function ($item) {
            return [$item['goods_id'] => $item['buy_num']];
        });

        $select = [
            'g.id as goods_id',
            'g.goods_title',
            'g.gname as goods_name',
            'g.goods_type_id as sort_id',
            'g.on_sale',
            'g.shenghe_act',
            'g.brand_id as brand_id',
            'attr.goods_price',
            'attr.auto_soldout_time',
            'attr.meter_unit',
        ];

        $goodsCollection =
            $this->shoppingCartRepo->getGoodsInfo($goodsIds, $select);

        // 为了发送微信模板消息
        $this->goodsTitles =
            implode($goodsCollection->pluck('goods_name')->toArray(), ",");

        $goods = $goodsCollection->toArray();

        $markUpMapArr = GoodsCategoryBrand::getMarkups(
            getServiceProvider()->zdp_user_id
        );

        $orderAmount = 0.0;
        $buyCount = 0;
        $goodsNum = 0;

        foreach ($goods as $key => $item) {
            $goodsTitle = $item['goods_title'];

            $goodsTitle =
                empty($goodsTitle) ? $item['goods_name'] : $goodsTitle;

            if ($item['on_sale'] != DpGoodsInfo::GOODS_SALE) {
                throw new AppException("商品:{$goodsTitle}已经下架,无法购买");
            }
            if ($item["shenghe_act"] != DpGoodsInfo::STATUS_NORMAL) {
                throw new AppException("商品:{$goodsTitle}暂时无法购买");
            }
            if ($item["auto_soldout_time"] < date('Y-m-d H:i:s')) {
                throw new AppException("商品:{$goodsTitle}价格已经过期");
            }

            $goodsId = $item['goods_id'];
            $sortId = $item['sort_id'];
            $brandId = $item['brand_id'];
            $price = $item['goods_price'];
            $unit = $item['meter_unit'];

            $price = GoodsCategoryBrand::markUpPrice(
                $sortId,
                $brandId,
                $price,
                $markUpMapArr,
                $unit
            );

            $buyNum = $buyNumMapArr[$goodsId];
            $orderAmount += (float)($buyNum * $price);
            $buyCount += $buyNum;
            $goodsNum++;

            // 修改goods的price buy_num 以便计入日志
            $goods[$key]['goods_price'] = $price;
            $goods[$key]['buy_num'] = $buyNum;
        }

        $orderNo =
            date('YmdHis') . $userId . GenerateRandomNumber::generateString(5);

        $order = Order::create(
            [
                'order_no'       => $orderNo,
                'sp_id'          => $spId,
                'user_id'        => $userId,
                'goods_num'      => $goodsNum,
                'buy_count'      => $buyCount,
                'order_amount'   => $orderAmount,
                'payment'        => $payment,
                'delivery'       => $delivery,
                'consignee_info' => json_encode($consigneeInfo),
                'status'         => Order::NEW_ORDER,
            ]
        );

        $this->generateOrderGoods($order->id, $goods);

        return $order;
    }

    /**
     * 移除用户购物车中的商品
     *
     * @param $userId
     * @param $goodsIds array
     */
    protected function removeGoodsFromCart($userId, $goodsIds)
    {
        ShoppingCart::where('user_id', $userId)
                    ->whereIn('goods_id', $goodsIds)
                    ->delete();
    }

    /**
     * 生成订单中的商品
     *
     * @param $orderId
     * @param $goods
     */
    protected function generateOrderGoods($orderId, $goods)
    {
        $goodsIds = array_pluck($goods, 'goods_id');

        $snapShots = $this->mainGoodsRepo->getGoodsSnapShots($goodsIds);

        $snapShotsKeyByGoodsId = $snapShots->keyBy('id')->toArray();

        foreach ($goods as $key => $item) {
            $goodsId = $item['goods_id'];
            $snapShot = $snapShotsKeyByGoodsId[$goodsId];

            //Modify snapshot price
            $snapShot['goods_price'] = $item['goods_price'];
            //Add buy num
            $snapShot['buy_num'] = $item['buy_num'];

            OrderGoods::create(
                [
                    'order_id'   => $orderId,
                    'goods_id'   => $goodsId,
                    'goods_info' => json_encode($snapShot),
                ]
            );
        }
    }

    /**
     * 生成订单日志
     *
     * @param $orderId
     * @param $userId
     *
     * @return OrderLog
     */
    protected function generateOrderLog($orderId, $userId)
    {
        return OrderLog::create(
            [
                'order_id'  => $orderId,
                'user_id'   => $userId,
                'source'    => OrderLog::CUSTOMER,
                'operation' => Order::NEW_ORDER,
            ]
        );
    }

    /**
     * 买家订单列表
     *
     * @param $status   integer 获取订单状态
     * @param $page     integer 获取页数
     * @param $size     integer 获取数据量
     * @param $userInfo \App\Models\User 当前会员信息
     *
     * @return array
     */
    public function getOrderList($status, $page, $size, $userInfo)
    {
        $ordersCollection =
            $this->orderRepo->getOrderList($status, $size, $userInfo->id);
        $reDataArr = [
            'total'  => $ordersCollection->total(),
            'orders' => [],
        ];
        if ($ordersCollection->count()) {
            foreach ($ordersCollection as $key => $item) {
                $reDataArr['orders'][$key] = $item->toArray();
                // 处理收货人信息
                $reDataArr['orders'][$key]['consignee_info'] =
                    json_decode($item->consignee_info, true);
                // 商品信息
                $goodsCollection = $this->getOrderGoods($item->id);
                foreach ($goodsCollection as $goodsKey => $goods) {
                    $goodsInfoArr = json_decode($goods->goods_info, true);
                    $goodsPicCollect = collect($goodsInfoArr['goods_picture']);
                    $goodsPicArr =
                        $goodsPicCollect->pluck('ypic_path', 'ordernum')->all();
                    $reDataArr['orders'][$key]['goods'][] = [
                        'id'          => $goodsInfoArr['id'],
                        'goods_title' => $goodsInfoArr['goods_title'],
                        'goods_price' => $goodsInfoArr['goods_price'],
                        'buy_num'     => $goodsInfoArr['buy_num'],
                        'goods_pic'   => empty($goodsPicArr[0])
                            ? $goodsPicArr[1] : $goodsPicArr[0],
                    ];
                }
                if ($item->select === Order::CANCELED) {
                    // 获取订单取消来源
                    $reDataArr['orders'][$key]['operation_source'] =
                        $this->getOrderNewLog($item->id)->source;
                }
            }
        }

        return $reDataArr;
    }

    /**
     * 获取订单商品信息
     *
     * @param $orderId integer 订单ID
     *
     * @return Collection
     */
    private function getOrderGoods($orderId)
    {
        return OrderGoods::where('order_id', $orderId)
                         ->select(['id', 'goods_info'])
                         ->get();
    }

    /**
     * 获得订单最新一条日志
     *
     * @param $orderId integer 订单ID
     *
     * @return OrderLog
     */
    private function getOrderNewLog($orderId)
    {
        return OrderLog::where('order_id', $orderId)
                       ->select(['source'])
                       ->orderBy('id', 'desc')
                       ->first();
    }

    /**
     * 订单状态修改
     *
     * @param $orderId integer 订单ID
     * @param $status  integer 操作状态
     * @param $source  integer 来源
     * @param $userInfo
     *
     * @throws \App\Exceptions\OrderException
     */
    public function updateOrderStatus($orderId, $status, $source, $userInfo)
    {
        // 取得订单信息
        $orderInfo = Order::query()->findOrFail($orderId);
        /*if (is_null($orderInfo)) {
            throw new OrderException(OrderException::NOT_ORDER);
        }*/
        // 检查是否当前来源的订单
        $sourceId = 0;  // 表示系统
        if ($source == OrderLog::CUSTOMER) {
            // 买家（会员）
            if ($orderInfo->user_id != $userInfo->id) {
                throw new OrderException(OrderException::PERMISSION_ERR);
            }
            $sourceId = $userInfo->id;
        } elseif ($source == OrderLog::SHOP) {
            // 卖家（服务商）
            if ($orderInfo->sp_id != $userInfo->zdp_user_id) {
                throw new OrderException(OrderException::PERMISSION_ERR);
            }
            $sourceId = $userInfo->zdp_user_id;
        }
        // 操作订单状态并写日志
        $updateNum =
            $this->orderRepo->updateOrderStatus($orderId, $status, $source,
                $sourceId);

        if ($updateNum) {
            // 未支付的订单取消, 不发送消息
            if ($orderInfo->payment == Order::WECHAT_PAY &&
                in_array(
                    $orderInfo->status,
                    [
                        Order::NEW_ORDER,
                        Order::PAYING,
                        Order::PAY_ERROR,
                    ]
                ) && $status == Order::CANCELED
            ) {
                $this->orderEvent($orderId, $status, $source, false);
            } else {
                $this->orderEvent($orderId, $status, $source);
            }
        }
    }

    /**
     * 订单状态改变的事件处理(如：发送微信模板消息)
     *
     * @param $orderId  integer 订单ID
     * @param $status   integer 操作状态
     * @param $source   integer 来源
     */
    private function orderEvent($orderId, $status, $source, $notify = true)
    {
        event(new OrderEvent($orderId, $status, $source, $notify));
    }

    /**
     * 订单支付
     *
     * @param $orderId  integer 订单ID
     * @param $userInfo \App\Models\User 会员信息
     *
     * @return array
     * @throws OrderException
     */
    public function payment($orderId, $userInfo)
    {
        // 取得订单及购买者信息
        $orderInfoModel =
            $this->orderRepo->getOrderInfo($orderId, $userInfo->id);
        $canPayStatusArr = [
            Order::NEW_ORDER,
            Order::PAY_ERROR,
        ];
        if (!in_array($orderInfoModel->status, $canPayStatusArr)
            || $orderInfoModel->payment == Order::CASH_ON_DELIVERY
        ) {
            throw new OrderException(OrderException::NOT_PAY);
        }
        $shopInfo = getServiceProvider();
        $optionsArr = config('wechat');
        $subWechatInfo = getWeChatAccount();
        $paymentConfArr = [
            'app_id'          => $optionsArr['main']['app_id'],
            'merchant_id'     => $optionsArr['main']['merchant_id'],
            'sub_app_id'      => $subWechatInfo->appid,//'wx129e040711acb928'
            'sub_merchant_id' => $subWechatInfo->merchant_id,//1454959502
        ];
        $optionsArr['payment'] =
            array_merge($optionsArr['payment'], $paymentConfArr);
        $paymentApp = new Payment($optionsArr);
        $carbon = Carbon::now();
        $body = "{$shopInfo->shop_name}-会员订单支付";
        $orderGoodsInfo = $orderInfoModel->orderGoods;
        $detail = '商品信息：';
        foreach ($orderGoodsInfo as $value) {
            $detail .= "，{$value->gname}";
        }
        $attributes = [
            'trade_type'   => 'JSAPI',
            'body'         => $body,
            'detail'       => $detail,
            'out_trade_no' => $orderInfoModel->order_no,
            // 单位：分
            'total_fee'    => MoneyUnitConvertUtil::yuanToFen($orderInfoModel->order_amount),
            //'otVzBwUTHiN0JtP9iCNcbGwuPinM'
            'sub_openid'   => $orderInfoModel->buyer->wechat_openid,
            // 原样返回的附加数据
            'attach'       => $orderInfoModel->order_no,
            // 交易起始时间 YmdHis
            'time_start'   => $carbon->format('YmdHis'),
            // 交易结束时间 YmdHis
            'time_expire'  => $carbon->addMinute(10)->format('YmdHis'),
            'notify_url'   => getUrl($subWechatInfo->source,
                '/api/wechat/payment/notify'),
        ];
        $paymentConfigArr = [];
        $self = $this;
        DB::transaction(function () use (
            $self,
            $paymentApp,
            $attributes,
            $orderInfoModel,
            &$paymentConfigArr,
            $orderId
        ) {
            $paymentApp->createPayOrder($attributes);
            $paymentConfigArr = $paymentApp->getPreparePay();
            // 更改订单状
            $orderInfoModel->status = Order::PAYING;
            $orderInfoModel->save();
            $self->orderEvent($orderId, Order::PAYING, OrderLog::CUSTOMER);
        });

        return $paymentConfigArr;
    }
}
