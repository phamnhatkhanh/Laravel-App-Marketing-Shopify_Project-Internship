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


    /**
     * * The primary key for job batch of group job create customer when sync customer.
     *
     * @var string
     */
    public $batchID;

    /**
     * * The data after excute this job.
     *
     * @var array
     */
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
        info('SynchronizedCustomer: COMPOLETE SYNC CUSTOMER FROM SHOPIFY');
        $batch =  JobBatch::find($this->batchID);
        $customer_model_builder = setConnectDatabaseActived(new Customer());
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
