<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\JobBatch;

class MailSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $batchId;
    public $campaignProcessId;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($batchId,$campaignProcessId)
    {
        $this->batchId = $batchId;
        $this->campaignProcessId = $campaignProcessId;
        $this->message  = $this->sendProcess($this->campaignProcessId);
    }
    public function sendProcess($campaignProcessId){
        info('comleted send mail');
         $batches =  JobBatch::find($this->batchId);


         return response()->json([
            'campaignId' => $campaignProcessId,
            'processing'=>$batches->progress(),
            'mail_send_done'=> $batches->processedJobs(),
            'mail_send_failed'=>$batches->failed_jobs,
            'finished_at' =>$batches->finished_at
         ]);

        //  'Finish: '.$batches->finished_at.
        //     ' - Processing: '.$batches->progress().'%'.
        //     ' - Send: '. $batches->processedJobs().
        //     ' - Fail: '.$batches->failed_jobs.
        //     ' OF: '.$campaignProcessId;

    }


    public function broadcastOn()
    {
        return ['campaigns'];
    }
    public function broadcastAs(){
        return 'send_mail';
    }

    // public function broadcastOn()
    // {
    //     return ['MailSent'];
    // }
    // public function broadcastAs(){
    //     return 'send-done';
    // }
}
