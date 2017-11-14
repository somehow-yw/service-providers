<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/29 0029
 * Time: 下午 3:42
 */

namespace App\Services;

use Carbon\Carbon;
use Zdp\ServiceProvider\Data\Models\SpMessagesImg;
use App\Repositories\Feedback\Contracts\FeedbackRepository;
use App\Exceptions\Feedback\FeedbackException;
use DB;
use App\Jobs\FeedbackOss;
use App\Utils\WechatPicToOss;
use App\Events\NewWeChatMessage;
use Event;
use Zdp\ServiceProvider\Data\Models\User;

class FeedbackService
{
    private $repository;

    public function __construct(
        FeedbackRepository $repository
    ) {
        $this->repository = $repository;
    }


    /**
     * 写入用户反馈消息
     *
     * @param $ip      string 用户的ip
     * @param $content string 用户反馈的地址
     * @param $imgs    array 用户反馈的图片
     *
     * @return array
     * @throws FeedbackException
     */
    public function insertUserFeedback($ip, $content, $imgs)
    {
        $user = getUser();
        $serviceProvider = getServiceProvider();
        if (empty($user)) {
            throw new FeedbackException('用户不存在', FeedbackException::USER_NOT_EXIST);
        }

        DB::transaction(
            function () use (
                $user,
                $ip,
                $content,
                $imgs,
                $serviceProvider
            ) {
                // 用户反馈数据写入
                $feed_id = $this->repository->insertUserFeedback($user->id, $ip, $content);

                // 用户反馈图片写入
                $imgAddObj = $this->handPic($feed_id, $imgs);

                // 将图片添加的信息放入队列
                if ($imgAddObj) {
                    dispatch(new FeedbackOss($feed_id));
                }

                $newOrderTemplate = generate_wechat_template('feedback_remind', [
                    'url'  => config('wechat_template.urls.feedback_remind_url') . $feed_id,
                    'data' => [
                        'first'    => ['您好,有用户向您反馈问题。'],
                        'keyword1' => [$user->shop_name . ' ' . $user->mobile_phone],
                        'keyword2' => [Carbon::now()->format('Y-m-d H:i:s')],
                        'keyword3' => [$content],
                        'remark'   => ['商品和订单问题，请及时联系买家处理。系统和功能问题，已提交平台处理。'],
                    ],
                ]);

                Event::fire(
                    'feedback_remind',
                    new NewWeChatMessage($serviceProvider->subscribers->toArray(), $newOrderTemplate)
                );
            }
        );

        return [
            'code'=>0,
            'message'=>'ok',
            'data'=>[]
        ];
    }

    /**
     * 用户反馈的图片处理
     * @param int $feed_id 用户反馈ID
     * @param array $imgs 用户反馈的图片
     *
     * @return bool
     */
    public function handPic($feed_id, $imgs)
    {
        if (!empty($imgs)) {
            foreach ($imgs as $k => $v) {
                $this->repository->addPic($feed_id, $v['img_url']);
            }
        }

        return true;
    }

    /**
     * 将用户反馈图片从微信上移动到OSS
     * @param $feed_id
     *
     */
    public function feedPicToOss($feed_id)
    {
        // 获取需要上传的图片
        $picObj = $this->repository->getPic($feed_id);
        foreach ($picObj as $k => $v) {
            $filePath = "/Uploads/FeedbackImgs";
            // 数据库及OSS保存的路径
            $fileSavePath = $filePath . '/' . $v->img_url . 'jpg';
            /** @var WechatPicToOss $wechatApi */
            $wechatApi = app()->make(WechatPicToOss::class);
            $logPath = storage_path('logs/feedPic_'.date('Ymd') . '.log');
            $downloadFilePath = $wechatApi->wechatFileDownload($v->img_url, $logPath, $filePath);
            $uploadName = $wechatApi->wechatFileUpload($fileSavePath, $downloadFilePath, $logPath);
            if ($uploadName == 'ok') {
                SpMessagesImg::query()->where('id', $v->id)
                            ->update(['img_url' => $fileSavePath]);
            }
        }
    }


    /**
     * 根据用户反馈ID获取详情
     *
     * @param $feed_id integer 用户反馈ID
     *
     * @return array
     */
    public function getUserFeedback($feed_id)
    {
        $resObj = $this->repository->getUserFeedback($feed_id);
        $reData = [];
        if (!empty($resObj)) {
            $reData['content'] = $resObj->message;
            $reData['time'] = $resObj->mesgtime;
            foreach ($resObj->feedbackPic as $k => $value) {
                $reData['img'][$k]['img_url'] = $value->img_url;
            }
            $userObj = User::query()
                           ->where('id', $resObj->shid)
                           ->first();
            $reData['province'] = $this->repository->getArea($userObj->province_id);
            $reData['city'] = $this->repository->getArea($userObj->city_id);
            $reData['county'] = $this->repository->getArea($userObj->county_id);
            $reData['name'] = $userObj->user_name;
            $reData['tel'] = $userObj->mobile_phone;
            $reData['shop_name'] = $userObj->shop_name;
            $reData['shop_address'] = $userObj->address;
        }

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => $reData,
        ];
    }
}