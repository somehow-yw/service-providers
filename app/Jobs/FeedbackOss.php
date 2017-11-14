<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\FeedbackService;

class FeedbackOss implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $feedId;
    
    /**
     * Create a new job instance.
     * @param $feedId    integer 用户反馈ID
     * 
     */
    public function __construct($feedId)
    {
        $this->feedId = $feedId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FeedbackService $feedbackService)
    {
        $feedbackService->feedPicToOss($this->feedId);
    }
}
