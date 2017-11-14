<?php

namespace App\Listeners;

use App\Events\NewWeChatMessage;
use Illuminate\Support\Facades\Log;

class SendWeChatMessage
{

    /**
     * Handle the event.
     *
     * @param  NewWeChatMessage $event
     *
     * @return void
     */
    public function handle(NewWeChatMessage $event)
    {
        $weChatOpenIds = $event->weChatOpenIds;

        $template = $event->weChatTemplate;
        $this->setTemplateUrl($template, resolveSource());

        $wechatMsgSender = $event->wechatMsgSender;
        Log::info('发送微信消息给：');
        foreach ($weChatOpenIds as $key => $openId) {
            Log::info($openId);
            $this->setTemplateOpenId($template, $openId);
            try {
                $wechatMsgSender->sendTemplateNotice($template);
            } catch (\Exception $e) {
                ;
            }
        }
    }

    /**
     * 设置每个模板的openid
     *
     * @param $template
     * @param $openId
     */
    protected function setTemplateOpenId(&$template, $openId)
    {
        $template['template_data']['touser'] = $openId;
    }

    /**
     * 设置每个模板的url
     *
     * @param $template
     * @param $subDomain string 主域名 如 hello.dongpin.me => hello
     */
    protected function setTemplateUrl(&$template, $subDomain)
    {
        $urlPath = $template['template_data']['url'];

        if ($urlPath != '#') {
            $template['template_data']['url'] =
                sprintf(env('SERVICE_PROVIDER_DOMAIN'), $subDomain) . $urlPath;
        }
    }
}
