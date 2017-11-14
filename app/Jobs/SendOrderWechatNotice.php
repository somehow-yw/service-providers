<?php

namespace App\Jobs;

use App\Services\OrderWechatNoticeData;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Utils\WechatTemplate;
use Illuminate\Support\Facades\Log;

/**
 * 发送微信模板消息
 * Class OrderWechatNoticeData
 *
 * @package App\Jobs
 */
class SendOrderWechatNotice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;
    protected $status;
    protected $orderAction;
    protected $spSource;
    protected $wechatMsgSender;

    /**
     * Create a new job instance.
     * SendOrderWechatNotice constructor.
     *
     * @param $orderId     integer 订单ID
     * @param $status      integer 订单操作状态
     * @param $orderAction integer 订单操作者 如：会员或服务商等 OrderLog::SHOP
     * @param $spSource    string 服务商标识(域名)
     */
    public function __construct(
        $orderId = 0,
        $status = 0,
        $orderAction = 0,
        $spSource = ''
    ) {
        $this->orderId = $orderId;
        $this->status = $status;
        $this->orderAction = $orderAction;
        $this->spSource = $spSource;
    }

    /**
     * Execute the job.
     *
     * @param \App\Utils\WechatTemplate           $wechatMsgSender
     * @param \App\Services\OrderWechatNoticeData $wechatNotice
     */
    public function handle(
        WechatTemplate $wechatMsgSender,
        OrderWechatNoticeData $wechatNotice
    ) {
        $sendDataArr = $wechatNotice->sendNotice($this->orderId, $this->status, $this->orderAction);

        if (empty($sendDataArr)) {
            return;
        }

        setTemplateUrl($sendDataArr['template'], $this->spSource);

        //        Log::info('Wechat Teplate:' . json_encode($sendDataArr));
        foreach ($sendDataArr['openIds'] as $key => $openId) {
            setTemplateOpenId($sendDataArr['template'], $openId);
            try {
                $wechatMsgSender->sendTemplateNotice($sendDataArr['template'], $this->spSource);
            } catch (\Exception $e) {
                ;
            }
        }
    }
}
