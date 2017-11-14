<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/29 0029
 * Time: 下午 3:53
 */

namespace App\Repositories\Feedback;

use App\Repositories\Feedback\Contracts\FeedbackRepository as RepositoryContract;
use Carbon\Carbon;
use Zdp\ServiceProvider\Data\Models\Area;
use Zdp\ServiceProvider\Data\Models\SpMessages;
use Zdp\ServiceProvider\Data\Models\SpMessagesImg;

class FeedbackRepository implements RepositoryContract
{
    /**
     * @see \App\Repositories\Feedback\Contracts\FeedbackRepository
     */
    public function insertUserFeedback($user_id, $ip, $content)
    {
        $time = Carbon::now();
        $createData = [
            'shid'          => $user_id,
            'message'       => $content,
            'mesgtime'      => $time,
            'formip'        => $ip,
            'msgact'        => SpMessages::NOT_HAND
        ];

        return SpMessages::create($createData)->id;
    }

    /**
     * @see \App\Repositories\Feedback\Contracts\FeedbackRepository
     */
    public function addPic($feed_id, $url)
    {
        $createData = [
            'message_id' => $feed_id,
            'img_url'    => $url,
        ];

        SpMessagesImg::create($createData);
    }

    /**
     * @see \App\Repositories\Feedback\Contracts\FeedbackRepository
     */
    public function getPic($feed_id)
    {
        return SpMessagesImg::query()
                           ->where("message_id", $feed_id)
                           ->get();
    }

    /**
     * @see \App\Repositories\Feedback\Contracts\FeedbackRepository
     */
    public function getUserFeedback($feedback_id)
    {
        return SpMessages::query()
                         ->with('feedbackPic')
                         ->where('id', $feedback_id)
                         ->first();
    }

    /**
     * @see \App\Repositories\Feedback\Contracts\FeedbackRepository
     */
    public function getArea($id)
    {
        return Area::query()
            ->where('id', $id)
            ->value('name');
    }
}