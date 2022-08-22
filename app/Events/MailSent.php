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


    public $batchID;
    public $campaignProcess;
    public $payload;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($batchID,$campaignProcess)
    {
        $this->batchID = $batchID;
        $this->campaignProcess = $campaignProcess;
        $this->payload  =  $this->sendProcess($this->campaignProcess);
    }
    public function sendProcess($campaignProcess){
        $batch =  JobBatch::find($this->batchID);

        $mailDonePercentage =  $campaignProcess->total_customers> 0?round(($batch->processedJobs()/$campaignProcess->total_customers) * 100):0;
        $mailFailedPercentage = $batch->total_jobs>0? round(($batch->failed_jobs/$batch->total_jobs) * 100):0;

         return response()->json([
            'campaignId' => $campaignProcess->id,
            'status' =>'completed',
            'processing' => 100,
            'mail_send_done' => $batch->processedJobs(),
            'mail_done_percentage' => $mailDonePercentage,
            'mail_send_failed' =>$batch->failed_jobs,
            'mail_failed_percentage' => $mailFailedPercentage,
            'total_customer' => $campaignProcess->total_customers,
            'finished_at' =>$batch->finished_at
         ]);

    }


    public function broadcastOn()
    {
        return ['campaigns'];
    }
    public function broadcastAs(){
        return 'send_mail';
    }

}
