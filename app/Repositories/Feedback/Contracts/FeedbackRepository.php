<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/29 0029
 * Time: 下午 3:54
 */
namespace App\Repositories\Feedback\Contracts;
use Illuminate\Support\Collection;

interface FeedbackRepository
{
    /**
     * 添加用户反馈信息
     * @param integer $user_id 用户ID
     * @param string $ip ip地址
     * @param int $msg_type 用户反馈来源
     * @param string $content 反馈内容
     *
     * @return int
     */
    public function insertUserFeedback($user_id, $ip, $content);

    /**
     * 添加用户反馈的图片信息
     * @param int $feed_id 用户反馈ID
     * @param string $url 用户反馈的图片地址
     *
     * @return Collection
     */
    public function addPic($feed_id, $url);

    /**
     * @param $feed_id int 用户反馈ID
     *
     * @return Collection
     */
    public function getPic($feed_id);

    /**
     * @param $feedback_id integer 用户反馈ID
     *
     * @return Collection
     */
    public function getUserFeedback($feedback_id);

    /**
     * @param $id int 地区ID
     *
     * @return string
     */
    public function getArea($id);
}