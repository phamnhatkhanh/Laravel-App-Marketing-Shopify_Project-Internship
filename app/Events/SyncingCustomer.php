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


    public $batchID;
    public $processing;
    public $payload;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($batchID)
    {
        $this->batchID = $batchID;
        $this->payload  = $this->sendProcess();
    }

    public function sendProcess(){

        $batch =  JobBatch::find($this->batchID);

        return ([
        'processing'=> $batch->progress(),
        'status' =>false
        ]);
    }


    public function broadcastOn()
    {
        return ['customers_syncing'];
    }
    public function broadcastAs(){
        return 'syncing_customer';
    }

}
