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
use App\Models\Customer;

class SynchronizedCustomer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $batchID;


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
        info('comleted sync customer: '. $this->batchID);
        $batch =  JobBatch::find($this->batchID);
        $customer_model_builder = getConnectDatabaseActived(new Customer());
        $customer = $customer_model_builder->getModel();
        return [
            "status" => true,
            "message" => "Success sync customer",
            'processing'=> $batch->progress(),
            "totat" => $customer->count(),
            "data" => $customer->simplePaginate(15)
        ];
    }


    public function broadcastOn()
    {
        return ['customers_syncing'];
    }
    public function broadcastAs(){
        return 'syncing_customer';
    }
 
}
