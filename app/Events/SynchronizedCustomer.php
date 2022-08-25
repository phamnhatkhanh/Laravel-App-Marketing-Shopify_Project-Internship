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
use App\Models\Store;

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
     * * The primary key of store.
     *
     * @var string
     */
    public $storeID;

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
    public function __construct($batchID,$storeID)
    {

        $this->batchID = $batchID;
        $this->storeID = $storeID;
        $this->payload  = $this->sendProcess();
    }

    public function sendProcess(){
        info('SynchronizedCustomer: COMPOLETE SYNC CUSTOMER FROM SHOPIFY');
        $batch =  JobBatch::find($this->batchID);

        $customerModelBuilder = setConnectDatabaseActived(new Customer());
        $customers = $customerModelBuilder->where('store_id',$this->storeID)->simplePaginate(15);

        if(empty($customers->getCollection()->toArray())){
            return [
                "status" => true,
                "message" => "Success sync customer",
                'processing'=> $batch->progress(),
                "totat" => 0,
                "data" => []
            ];
        }
        return [
            "status" => true,
            "message" => "Success sync customer",
            'processing'=> $batch->progress(),
            "totat" => $customers->getCollection()->count(),
            "data" => $customers
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
