<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/29 0029
 * Time: 下午 3:39
 */
namespace App\Http\Controllers\Other\Common;

use App\Http\Controllers\Controller;
use App\Services\FeedbackService;
use Illuminate\Http\Request;

class FeedbackController extends Controller{

    /**
     * @var FeedbackService
     */
    private $service;

    public function __construct(FeedbackService $service)
    {
        $this->service = $service;
    }


    /**
     * 插入反馈信息
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function insertUserFeedback(Request $request)
    {
        $this->validate(
            $request,
            [
                'content'     =>'required|string|between:5,150',
                'imgs'        =>'array|between:0,2',
            ],
            [
                'content.required'=>'反馈内容不能为空',
                'content.string'=>'反馈内容必须是字符串',
                'content.between'=>'反馈内容必须大于min,小于max',

                'imgs.array'=>'反馈图片必须是个数组',
                'imgs.between'=>'反馈图片必须大于0，小于2',
            ]
        );

        $reData = $this->service->insertUserFeedback(
            $request->getClientIp(),
            $request->input('content'),
            $request->input('imgs')
        );

        return response()->json([
            'code'    => $reData['code'],
            'message' => $reData['message'],
            'data'    => $reData['data'],
        ]);
    }

    /**
     * 根据用户反馈ID获取详情
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserFeedback(Request $request)
    {
        $this->validate(
            $request,
            [
                'feedback_id' => 'required|integer|min:1'
            ],
            [
                'feedback_id.required' => '用户反馈ID不能为空',
                'feedback_id.integer'  => '用户反馈ID必须是整数',
                'feedback_id.min'      => '用户反馈ID必须大于1',
            ]
        );
        $reData = $this->service->getUserFeedback(
            $request->input('feedback_id')
        );

        return response()->json([
            'code'    => $reData['code'],
            'message' => $reData['message'],
            'data'    => $reData['data'],
        ]);
    }
}