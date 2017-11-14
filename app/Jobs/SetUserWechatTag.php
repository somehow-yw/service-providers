<?php

namespace App\Jobs;

use App\Services\Wechat\WechatService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * 给用户在微信会员打上TAG
 * Class SetUserWechatTag
 *
 * @package App\Jobs
 */
class SetUserWechatTag implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $openId;

    /**
     * Create a new job instance.
     *
     * @param $openId string 会员的微信OPENID
     *
     * @return void
     */
    public function __construct($openId)
    {
        $this->openId = $openId;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Wechat\WechatService $service
     */
    public function handle(WechatService $service)
    {
        $service->setUserWechatTag($this->openId);
    }
}
