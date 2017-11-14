<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/10
 * Time: 17:23
 */

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Jobs\SetUserWechatTag;
use App\Services\Wechat\WechatService;
use Carbon\Carbon;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Support\Collection;
use EasyWeChat\Message\Raw;
use Log;
use Zdp\ServiceProvider\Data\Models\WebAdminLoginToken;

/**
 * 微信信息处理
 * Class WechatController
 *
 * @package App\Http\Controllers\Wechat
 */
class WechatController extends Controller
{
    /**
     * 处理微信的请求消息
     *
     * @param \EasyWeChat\Foundation\Application $wechat
     *
     * @return \Symfony\Component\HttpFoundation\Response|string
     */
    public function noticeManage(Application $wechat)
    {
        $wechat->server->setMessageHandler([$this, 'msgHandling']);

        return $wechat->server->serve();
    }

    /**
     * 消息处理
     *
     * @param $message Collection 微信消息内容
     *
     * @return \EasyWeChat\Message\Raw|string
     */
    public function msgHandling($message)
    {
        switch ($message->MsgType) {
            case 'event':
                return $this->eventHandling($message);
                break;
            case 'text':
                return $this->textMsgHandling($message);
                break;
            case 'image':
                return '收到图片消息';
                break;
            case 'voice':
                return '收到语音消息';
                break;
            case 'video':
                return '收到视频消息';
                break;
            case 'location':
                return '收到坐标消息';
                break;
            case 'link':
                return '收到链接消息';
                break;
            default:
                return '收到其它消息';
                break;
        }
    }

    // 事件消息的处理
    public function eventHandling($message)
    {
        $serviceInfo = getServiceProvider();

        \Log::info($message);

        switch ($message->Event) {
            case 'subscribe':
                // 关注(订阅)
                // 重设会员标签
                /** @var WechatService $service */
                //$service = app()->make(WechatService::class);
                //$service->setUserWechatTag($message->FromUserName);
                // 使用Job
                dispatch(new SetUserWechatTag($message->FromUserName));

                return $this->weMsgReply("欢迎关注 {$serviceInfo->shop_name}！");
                break;

            case 'CLICK':
                if ($message->EventKey == 'V1_WEB_ADMIN_TOKEN') {
                    $openId = $message->FromUserName;

                    $string = WebAdminLoginToken::generate($openId);
                    $domain = config('admin.domain', 'dongpin.me');

                    return $this->weMsgReply("登陆码:{$string}; 登陆码1分钟失效, 失效后请重新获取。后台登录网址：{$domain}");
                } else {
                    return '';
                }

                break;

            default:
                //return $this->weMsgReply('欢迎您');
                return '';
        }
    }

    /**
     * 微信文字消息的处理
     *
     * @param $message
     *
     * @return \EasyWeChat\Message\Raw|string
     */
    public function textMsgHandling($message)
    {
        $serviceInfo = getServiceProvider();

        return $this->weMsgReply("请拨打电话：{$serviceInfo->mobile} 进行咨询，谢谢！");
    }

    /**
     * 消息回复
     *
     * @param $reMsgContent string 需要返回的消息内容
     * @param $msgType      string 返回的消息体格式 默认会自动封装 如果格式为 raw_xml
     *                      则返回一个自行封装的原生XML
     *
     * @return \EasyWeChat\Message\Raw|string
     */
    public function weMsgReply($reMsgContent = '', $msgType = 'text')
    {
        if ($msgType == 'raw_xml') {
            // 自己的原生XML
            return new Raw($reMsgContent);
        } else {
            return $reMsgContent;
        }
    }
}
