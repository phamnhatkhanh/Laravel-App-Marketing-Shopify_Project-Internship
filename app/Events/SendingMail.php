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

class SendingMail implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $batchId;
    public $campaignProcess;
    public $payload;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($batchId,$campaignProcess)
    {
        $this->batchId = $batchId;
        $this->campaignProcess = $campaignProcess;
        // $this->message  = $this->sendProcess($this->campaignProcess);
        $this->payload  = $this->sendProcess($this->campaignProcess);
    }

    public function sendProcess($campaignProcess){
        info("sedding mail ". $this->batchId);
        $batch =  JobBatch::find($this->batchId);

        info(' mail_done_percentage: ' . ("mail_send ". $batch->processedJobs(). "totle cutstome ".$campaignProcess->total_customers));
        info(' mail_done_percentage: ' . "mail_send ".( ($batch->processedJobs()/$campaignProcess->total_customers)*100));

        $mail_done_percentage =  $campaignProcess->total_customers > 0 ?round(($batch->processedJobs()/$campaignProcess->total_customers) * 100):0;
        $mail_failed_percentage = $batch->total_jobs > 0 ? round(($batch->failed_jobs/$batch->total_jobs) * 100):0;

        return response()->json([
            'campaignId' => $campaignProcess->id,
            'status' =>'running',
            'processing' => $batch->progress(),
            'mail_send_done' => $batch->processedJobs(),
            'mail_done_percentage' => $mail_done_percentage,
            'mail_send_failed' =>$batch->failed_jobs,
            'mail_failed_percentage' => $mail_failed_percentage,
            'total_customer' => $campaignProcess->total_customers,
            'finished_at' => $batch->finished_at
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
