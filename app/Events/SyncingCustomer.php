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

class SyncingCustomer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $batch_id;
    public $processing;
    public $payload;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($batch_id)
    {
        $this->batch_id = $batch_id;
        $this->payload  = $this->sendProcess();
    }

    public function sendProcess(){

        $batches =  JobBatch::find($this->batch_id);
        // $this->processing =$batches->progress();
        // $this->status = false;

        info("customer syncing ................");
        return ([
        'processing'=> $batches->progress(),
        'status' =>false
        ]);
        // return $batches->progress();
        // status:false

        // return 'Finish: '.$batches->finished_at.
        //     ' - Processing: '.$batches->progress().'%'.
        //     ' - Send: '. $batches->processedJobs().
        //     ' - Fail: '.$batches->failed_jobs;

        //
    }


    public function broadcastOn()
    {
        return ['customers_syncing'];
    }
    public function broadcastAs(){
        return 'syncing_customer';
    }
    // public function broadcastOn()
    // {
    //     return ['SendingMail'];
    // }
    // public function broadcastAs(){
    //     return 'send-processing';
    // }
}
