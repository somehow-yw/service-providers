<?php

namespace App\Listeners;

use App\Events\OrderEvent;
use App\Jobs\SendOrderWechatNotice;

/**
 * 订单事件监听
 * Class OrderListener
 *
 * @package App\Listeners
 */
class OrderListener
{
    protected $wechatNotice;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(SendOrderWechatNotice $wechatNotice)
    {
        $this->wechatNotice = $wechatNotice;
    }

    /**
     * Handle the event.
     *
     * @param  OrderEvent $event
     *
     * @return void
     */
    public function handle(OrderEvent $event)
    {
        // 发消息
        if ($event->notify) {
            dispatch(new SendOrderWechatNotice(
                $event->orderId,
                $event->status,
                $event->orderAction,
                $event->spSource
            ));
        }
    }
}
