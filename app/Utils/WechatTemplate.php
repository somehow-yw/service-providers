<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/30
 * Time: 14:51
 */

namespace App\Utils;

use App\Exceptions\AppException;
use EasyWeChat\Core\Exceptions\HttpException;
use EasyWeChat\Foundation\Application;
use Zdp\ServiceProvider\Data\Models\WechatAccount;
use App\Models\WechatTemplate as TemplateModel;

/**
 * 微信模板消息的处理
 * Class WechatTemplate
 *
 * @package App\Utils
 *
 * @property WechatAccount $spInfo;
 */
class WechatTemplate
{
    const MAX_RETRY = 3;

    /**
     * @var Application
     */
    public    $wechatApp;
    public    $spInfo;
    protected $spSource;

    /**
     * Inject the wechat service.
     */
    public function __construct()
    {
        $this->spSource = $spSource = resolveSource();
        //$this->intoAppConfig();
    }

    /**
     * 微信操作信息配置
     *
     * @param       $spSource    string 服务商域名(标识)
     *
     * @return void
     * @throws AppException
     */
    public function intoAppConfig($spSource = '')
    {
        if (empty($spSource)) {
            $spSource = $this->spSource;
        }
        $this->spInfo = WechatAccount::where('source', $spSource)
                                     ->first();
        if (is_null($this->spInfo)) {
            throw new AppException('服务商不存在');
        }
        $weChatConfigArr = config('wechat');
        $weChatAccounts = [
            'app_id'  => $this->spInfo->appid,
            // AppID
            'secret'  => $this->spInfo->secret,
            // AppSecret
            'token'   => $this->spInfo->token,
            // Token
            'aes_key' => $this->spInfo->aes_key,
            // EncodingAESKey
        ];
        $appConfigArr = array_merge($weChatConfigArr, $weChatAccounts);
        $this->wechatApp = new Application($appConfigArr);
    }

    /**
     * 根据微信模板shortId获取对应的模板ID
     *
     * @param $shortId string 微信模板的短ID（编号）
     *
     * @return string
     */
    public function getTemplateId($shortId)
    {
        $templateArr = TemplateModel::query()->where('source', $this->spSource)
                                    ->where('short_id', $shortId)
                                    ->select(['id', 'template_id'])
                                    ->first();
        if (is_null($templateArr)) {
            $wechatReArr =
                $this->wechatApp->notice->addTemplate($shortId)->toArray();
            $templateId = $wechatReArr['template_id'];
            $createArr = [
                'source'      => $this->spSource,
                'short_id'    => $shortId,
                'template_id' => $templateId,
            ];
            TemplateModel::query()->create($createArr);
        } else {
            $templateId = $templateArr->template_id;
        }

        return $templateId;
    }

    /**
     * 重设微信模板ID信息
     *
     * @param string $templateId 微信ID
     */
    public function clearTemplateId($templateId)
    {
        $num = TemplateModel::query()
                            ->where('source', $this->spSource)
                            ->where('template_id', $templateId)
                            ->delete();

        \Log::info("清理服务商 {$this->spSource} 的模板ID: $templateId. 删除数量: {$num}");
    }

    /**
     * 发送微信模板消息
     *
     * @param array $templateArr array 模板消息内容，具体请参阅wechat_template配置文件
     * @param       $spSource    string 服务商域名(标识)
     *
     * @return array
     */
    public function sendTemplateNotice(array $templateArr, $spSource = '')
    {
        $this->intoAppConfig($spSource);

        $tryTimes = 0;

        while ($tryTimes < self::MAX_RETRY) {
            $tryTimes++;

            $templateId = $this->getTemplateId($templateArr['short_id']);

            try {
                $weReturn = $this->send(array_merge(
                    ['template_id' => $templateId],
                    $templateArr['template_data']
                ));
            } catch (HttpException $e) {
                // 处理模板ID错误
                if ($e->getCode() == 40037) {
                    \Log::info("找不到服务商 {$this->spSource} 的模板ID: {$templateArr['short_id']} => {$templateId}. 清理本地缓存模板ID后重新申请.");
                    $this->clearTemplateId($templateId);
                    continue;
                }

                return [];
            }

            return $weReturn->toArray();
        }
    }

    /**
     * @param $data
     *
     * @return \EasyWeChat\Support\Collection
     */
    protected function send($data)
    {
        return $this->wechatApp->notice->send($data);
    }
}
