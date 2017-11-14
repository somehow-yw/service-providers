<?php

namespace App\Events;

use App\Utils\WechatTemplate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class NewWeChatMessage
 *
 * @package App\Events
 *
 * @property array          $weChatOpenIds
 * @property array          $weChatTemplate
 * @property WechatTemplate $wechatMsgSender
 */
class NewWeChatMessage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $weChatOpenIds;
    public $weChatTemplate;
    public $wechatMsgSender;

    /**
     * Create a new event instance.
     *
     * @param $weChatOpenIds  array
     * @param $weChatTemplate array
     */
    public function __construct(array $weChatOpenIds, array $weChatTemplate)
    {
        $this->weChatOpenIds = $weChatOpenIds;
        $this->weChatTemplate = $weChatTemplate;
        $this->wechatMsgSender = app(WechatTemplate::class);
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
