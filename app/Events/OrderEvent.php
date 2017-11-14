<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

/**
 * 订单事件
 * Class OrderEvent
 *
 * @package App\Events
 */
class OrderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderId;
    public $status;
    public $spSource;
    public $orderAction;
    public $notify = true;

    /**
     * OrderEvent constructor.
     * Create a new event instance.
     *
     * @param $orderId      integer 订单ID
     * @param $status       integer 操作状态
     * @param $orderAction  integer 操作者 如：买家或卖家 OrderLog::SHOP
     */
    public function __construct($orderId, $status, $orderAction, $notify = true)
    {
        $this->status = $status;
        $this->orderId = $orderId;
        $this->spSource = resolveSource();
        $this->orderAction = $orderAction;
        $this->notify = $notify;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
