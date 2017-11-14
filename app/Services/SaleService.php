<?php

namespace App\Services;

use App\Events\NewWeChatMessage;
use App\Exceptions\AppException;
use Illuminate\Database\Eloquent\Collection;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Models\OrderGoods;
use Zdp\ServiceProvider\Data\Models\OrderLog;
use Zdp\ServiceProvider\Data\Models\User;

class SaleService
{
    /**
     * 获取所有销售单列表
     *
     * @param      $uid
     * @param null $status 不传默认为所有
     * @param      $page
     * @param      $size
     *
     * @return array
     */
    public function index($uid, $status = null, $page, $size)
    {
        $query = Order::query();

        $query->with('orderGoods');

        if (empty($status)) {
            // 列表中排除线上支付且未支付的订单
            $query->where(function($a){
                $a->whereIn('status', [
                    Order::UNDELIVERED,
                    Order::DELIVERING,
                    Order::RECEIVED,
                    Order::CANCELED,
                ])
                    ->orWhere(function ($query) {
                        $query->where(function ($query) {
                            $query->where('payment', Order::CASH_ON_DELIVERY)
                                  ->where('status', Order::NEW_ORDER);
                        })
                              ->orWhere(function ($query) {
                                  $query->where('payment', Order::WECHAT_PAY)
                                        ->where('status', Order::PAY_SUCCESS);
                              });
                    });
            });
        } else {
            // 如果状态为1的话，排除微信新订单且未付款的
            if ($status == 1) {
                $query->where(function ($a){
                    $a->where(function ($query) {
                        $query->where('payment', Order::CASH_ON_DELIVERY)
                              ->where('status', Order::NEW_ORDER);
                    })
                          ->orWhere(function ($query) {
                              $query->where('payment', Order::WECHAT_PAY)
                                    ->where('status', Order::PAY_SUCCESS);
                          });
                });
            } else {
                $query->where('status', $status);
            }
        }
        $data = $query->where('sp_id', $uid)
            ->orderBy('id', 'desc')
            ->paginate($size, ['*'], null, $page);

        return [
            'total'     => $data->total(),
            'current'   => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'sales'     => array_map(function ($sales) {
                return self::formatForShop($sales);
            }, $data->items()),
        ];
    }

    protected function formatForShop($sales)
    {
        return [
            'order_id'     => $sales->id,
            'sp_id'        => $sales->sp_id,
            'buyer_id'     => $sales->user_id,
            'goods_sort'   => $sales->goods_num,
            'goods_count'  => $sales->buy_count,
            'order_amount' => $sales->order_amount,
            'order_status' => $sales->status,
            'created_at'   => $sales->created_at->toDateTimeString(),
            'buyer_info'   => self::formatBuyer($sales->consignee_info),
            'goods_info'   => self::formatGoods($sales->orderGoods),
        ];
    }

    protected function formatBuyer($buyer)
    {
        $buyer = json_decode($buyer);

        return [
            'buyer_name'    => $buyer->receiver,
            'buyer_mobile'  => $buyer->mobile,
            'buyer_address' => $buyer->address,
        ];
    }

    protected function formatGoods($goods)
    {

        $info = [];

        foreach ($goods as $good) {

            $goodsInfo = json_decode($good['goods_info']);

            $shopInfo = self::getGoodsMarket($good->goods_id);

            $info[] = [
                'goods_id'        => $good->goods_id,
                'goods_status'    => $good->status,
                'goods_title'     => $goodsInfo->goods_title,
                'goods_brand'     => $goodsInfo->brand,
                'goods_num'       => $goodsInfo->buy_num,
                'goods_picture'   => $goodsInfo->goods_picture[0]->ypic_path,
                'goods_price_add' => $goodsInfo->goods_price,
                'goods_price_pre' => $goodsInfo->goods_attribute->goods_price,
                'goods_shop'      => $shopInfo['shop'],
                'goods_market'    => $shopInfo['market'],
            ];
        }

        return $info;
    }

    protected function getGoodsMarket($goodsId)
    {
        $data = DpGoodsInfo::with('shop', 'shop.market')
            ->where('id', $goodsId)
            ->first();

        return [
            'shop'   => $data->shop->dianPuName,
            'market' => $data->shop->market->pianqu,
        ];
    }

    /**
     * 确认订单/确认发货
     *
     * @param array $orderIds
     * @param       $uid
     * @param       $handle 0:取消订单
     *                      1:确认订单
     *                      2:确认发货
     *                      3:确认收货
     *
     * @throws AppException
     */
    public function handle(array $orderIds, $uid, $handle)
    {
        $status = $this->getStatus($orderIds);
        // 查询当前订单状态是否全部一致
        if ($status->count() != 1) {
            throw new AppException('包含不一致状态');
        }

        $status = $status->get(0)['status'];

        if ($status == Order::CANCELED) {
            throw new AppException('订单已取消！');
        }

        switch ($handle) {
            case 0: // 取消订单
                if ($status != Order::NEW_ORDER &&
                    $status != Order::UNDELIVERED
                ) {
                    throw new AppException('要修改的订单状态错误');
                }
                $handleStatus = Order::CANCELED;
                break;
            case 1: // 确认订单
                $handleStatus = self::judgeStatus($status, $orderIds);
                break;
            case 2: // 确认发货
                if ($status != Order::UNDELIVERED) {
                    throw new AppException('要修改的订单状态错误');
                }
                $handleStatus = Order::DELIVERING;
                break;
            case 3: // 确认收货
                if ($status != Order::DELIVERING) {
                    throw new AppException('要修改的订单状态错误');
                }
                $handleStatus = Order::RECEIVED;
                break;
            default:
                throw new AppException(sprintf('输入的 %s 判断有误', $handle));
        }

        return \DB::transaction(function () use (
            $orderIds,
            $uid,
            $handleStatus
        ) {
            Order::whereIn('id', $orderIds)
                ->update(['status' => $handleStatus]);
            array_map(function ($id) use ($handleStatus, $uid) {
                OrderLog::create([
                    'order_id'  => $id,
                    'operation' => $handleStatus,
                    'source'    => OrderLog::SHOP,
                    'user_id'   => $uid,
                ]);
            }, $orderIds);
            switch ($handleStatus) {
                case Order::CANCELED:   // 取消订单通知买家
                    self::shopCancelTemplate($orderIds);
                    break;
                case Order::DELIVERING: // 订单发货通知买家
                    self::shopSendTemplate($orderIds);
                    break;
            }
        });
    }

    /**
     * 查询所请求的订单状态
     *
     * @param array $orderIds array 所查询的订单 格式：[1,2,3]
     *
     * @return collection
     */
    protected function getStatus($orderIds)
    {
        $status = Order::whereIn('id', $orderIds)
            ->select('status')
            ->distinct()
            ->get();

        return $status;
    }

    // 取消订单提醒
    protected function shopCancelTemplate($orderIds)
    {
        foreach ($orderIds as $orderId) {
            $messageInfo = self::getMessageInfo($orderId);
            $data = [
                'url'  => config('wechat_template.urls.sp_cancel_order'),
                'data' => [
                    'first'             => ['卖家取消了您的订单。', '#173177'],
                    'orderProductPrice' => ["￥{$messageInfo['amount']}元", '#173177',],
                    'orderProductName'  => [$messageInfo['goodsName'], '#173177'],
                    'orderAddress'      => [$messageInfo['userInfo'], '#173177'],
                    'orderName'         => [$messageInfo['order_no'], '#173177'],
                    'remark'            => ['查看详情', '#173177'],
                ],
            ];
            self::sendMessageToUser('cancel_order', $data, [$messageInfo['openId']]);
        }
    }

    // 发货提醒
    protected function shopSendTemplate($orderIds)
    {
        foreach ($orderIds as $orderId) {
            $messageInfo = self::getMessageInfo($orderId);
            $data = [
                'url'  => config('wechat_template.urls.shipments_send_url'),
                'data' => [
                    'first'    => ['您的商品已发货，请注意收货。', '#173177'],
                    'keyword1' => [$messageInfo['order_no'], '#173177'],
                    'keyword2' => [$messageInfo['goodsName'], '#173177'],
                    'keyword3' => ["{$messageInfo['goodsNum']}种商品", '#173177'],
                    'keyword4' => ["￥{$messageInfo['amount']}元", '#173177'],
                    'remark'   => ['查看详情', '#173177'],
                ],
            ];
            self::sendMessageToUser('order_shipments', $data, [$messageInfo['openId']]);
        }
    }

    protected function getMessageInfo($orderId)
    {
        $goods = OrderGoods::where('order_id', $orderId)->get();
        $goodsName = [];
        foreach ($goods as $good) {
            // 商品名相加
            $goodsInfo = json_decode($good->goods_info);
            $goodsName[] = $goodsInfo->gname;
        }
        $goodsNames = implode(',', $goodsName);
        $order = Order::where('id', $orderId)->first();
        // 取得用户OPENID
        $openId = User::where('id', $order->user_id)->value('wechat_openid');
        $userInfo = json_decode($order->consignee_info);

        return [
            'amount'    => $order->order_amount,
            'goodsNum'    => $order->goods_num,
            'goodsName' => $goodsNames,
            'userInfo'  => $userInfo->receiver . ',' . $userInfo->mobile . ',' . $userInfo->address,
            'order_no'  => $order->order_no,
            'openId'    => $openId,
        ];
    }

    protected function sendMessageToUser($type, $data, $openId)
    {
        $succeedTemplate = generate_wechat_template($type, $data);
        \Event::fire('new_wechat_msg', new NewWeChatMessage($openId, $succeedTemplate));
    }

    // 根据不同订单判断状态
    protected function judgeStatus($status, $orderIds)
    {
        foreach ($orderIds as $orderId){
            $payment = Order::where('id', $orderId)
                ->value('payment');
            switch ($payment){
                case Order::CASH_ON_DELIVERY:
                    if ($status != Order::NEW_ORDER){
                        throw new AppException('订单状态错误');
                    }
                    break;
                case Order::WECHAT_PAY:
                    if ($status != Order::PAY_SUCCESS){
                        throw new AppException('订单状态错误');
                    }
                    break;
            }
        }

        return Order::UNDELIVERED;

    }
}
