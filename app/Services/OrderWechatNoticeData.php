<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/17
 * Time: 11:46
 */

namespace App\Services;

use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Models\OrderGoods;
use Zdp\ServiceProvider\Data\Models\OrderLog;
use Zdp\ServiceProvider\Data\Models\ServiceProvider;

/**
 * 订单改变时，发送微信消息
 * Class OrderWechatNoticeData
 *
 * @package App\Services
 */
class OrderWechatNoticeData
{
    /**
     * 发送微信模板消息
     *
     * @param $orderId
     * @param $status
     * @param $orderAction
     *
     * @return array
     */
    public function sendNotice($orderId, $status, $orderAction)
    {
        if ($orderAction == OrderLog::CUSTOMER) {
            // 买家（会员）
            return $this->sendBuyerNoticeInfo($orderId, $status);
        } elseif ($orderAction == OrderLog::SHOP) {
            // 卖家（服务商）
            return $this->sendSellerNoticeInfo($orderId, $status);
        }

        return [];
    }

    /**
     * 获得卖家(服务商)改变订单时发送微信模板消息通知
     *
     * @param $orderId
     * @param $status
     *
     * @return array
     */
    private function sendSellerNoticeInfo($orderId, $status)
    {
        $openIds = [];
        $template = [];
        switch ($status) {
            case Order::NEW_ORDER:
                // 新订单
                break;
            case Order::UNDELIVERED:
                // 已确认 待发货
                //
                break;
            case Order::DELIVERING:
                // 已发货 待收货
                //
                break;
            case Order::RECEIVED:
                // 已收货
                //
                break;
            case Order::CANCELED:
                // 已取消
                //
                break;
            default:
                ;
        }

        return [
            'openIds'  => $openIds,
            'template' => $template,
        ];
    }

    /**
     * 买家改变订单时发送微信模板消息通知
     *
     * @param $orderId
     * @param $status
     *
     * @return array
     */
    private function sendBuyerNoticeInfo($orderId, $status)
    {
        $openIds = [];
        $template = [];
        switch ($status) {
            case Order::NEW_ORDER:
                // 新订单
                break;
            case Order::UNDELIVERED:
                // 已确认 待发货
                //
                break;
            case Order::DELIVERING:
                // 已发货 待收货
                //
                break;
            case Order::RECEIVED:
                // 已收货
                //
                break;
            case Order::CANCELED:
                // 已取消
                // 取得订单信息
                $orderStatusArr = [Order::CANCELED];
                $orderInfo = $this->getOrderInfo($orderId, $orderStatusArr);

                // 取得商品信息
                $goodsInfoCollection = $this->getOrderGoodsInfo($orderId);
                $goodsNames = '';
                foreach ($goodsInfoCollection as $value) {
                    $goodsInfoArr = json_decode($value->goods_info, true);
                    $goodsNames .= $goodsInfoArr['gname'] . ',';
                }

                // 取得所有服务商OPENID
                $serviceProvider = ServiceProvider
                    ::query()
                    ->where('zdp_user_id', $orderInfo->sp_id)
                    ->first();

                $openIds = $serviceProvider->subscribers->toArray();
                $orderAddressArr = json_decode($orderInfo->consignee_info);
                $address = "联系人:{$orderAddressArr->receiver} " .
                           "联系电话:{$orderAddressArr->mobile} " .
                           "收货地址:{$orderAddressArr->address}";

                $templateDataArr = [
                    'url'  => config('wechat_template.urls.cancel_order'),
                    'data' => [
                        'first'             => ['买家取消了您的订单。', '#173177'],
                        'orderProductPrice' => [
                            "￥{$orderInfo->order_amount}元",
                            '#173177',
                        ],
                        'orderProductName'  => [$goodsNames, '#173177'],
                        'orderAddress'      => [$address, '#173177'],
                        'orderName'         => [
                            $orderInfo->order_no,
                            '#173177',
                        ],
                        'remark'            => ['查看详情', '#173177'],
                    ],
                ];
                $template =
                    generate_wechat_template('cancel_order', $templateDataArr);

                break;

            case Order::PAY_SUCCESS:
                // 支付成功
                // 订单信息
                $orderStatusArr = [Order::PAY_SUCCESS];
                $orderInfo = $this->getOrderInfo($orderId, $orderStatusArr);
                // 取得商品信息
                $goodsInfoCollection = $this->getOrderGoodsInfo($orderId);
                $goodsNames = '';
                foreach ($goodsInfoCollection as $value) {
                    $goodsInfoArr = json_decode($value->goods_info, true);
                    $goodsNames .= $goodsInfoArr['gname'] . ',';
                }

                // 取得所有服务商OPENID
                $serviceProvider = ServiceProvider
                    ::query()
                    ->where('zdp_user_id', $orderInfo->sp_id)
                    ->first();

                $openIds = $serviceProvider->subscribers->toArray();
                $orderAddressArr = json_decode($orderInfo->consignee_info);
                $address = "联系人:{$orderAddressArr->receiver} " .
                           "联系电话:{$orderAddressArr->mobile} " .
                           "收货地址:{$orderAddressArr->address}";

                $templateDataArr = [
                    'url'  => config('wechat_template.urls.new_order_url'),
                    'data' => [
                        'first'    => [config('wechat_template.titles.new_order_title')],
                        'keyword1' => ["￥{$orderInfo->order_amount} 元(支付成功)"],
                        'keyword2' => [$goodsNames],
                        'keyword3' => [$address],
                        'remark'   => ['查看订单详情'],
                    ],
                ];

                $template =
                    generate_wechat_template('new_order', $templateDataArr);

                break;

            default:
                break;
        }

        return [
            'openIds'  => $openIds,
            'template' => $template,
        ];
    }

    /**
     * 取得订单信息
     *
     * @param       $orderId        integer 订单ID
     * @param array $orderStatusArr array 订单状态 格式：[1,2]
     *
     * @return Order
     */
    protected function getOrderInfo($orderId, array $orderStatusArr)
    {
        $orderInfo = Order::query()
            ->select([
                'id',
                'order_no',
                'sp_id',
                'user_id',
                'order_amount',
                'consignee_info',
            ])
            ->where('id', $orderId)
            ->whereIn('status', $orderStatusArr)
            ->first();

        return $orderInfo;
    }

    /**
     * 获得订单商品的信息
     *
     * @param $orderId integer 订单ID
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getOrderGoodsInfo($orderId)
    {
        $goodsInfo = OrderGoods::query()
            ->select(['order_id', 'goods_info'])
            ->where('order_id', $orderId)
            ->get();

        return $goodsInfo;
    }
}
