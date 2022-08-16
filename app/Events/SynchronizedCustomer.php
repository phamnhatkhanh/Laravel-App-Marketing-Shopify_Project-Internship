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


    public $batch_id;


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
        info('comleted sync customer: '. $this->batch_id);
        $batches =  JobBatch::find($this->batch_id);
        $customer = new Customer();
        return [
            "status" => true,
            "message" => "Success sync customer",
            'processing'=> $batches->progress(),
            // "data" => json_encode(Customer::get(),true)
            "data" => $customer->simplePaginate(15)
        ];



        // return 'Finish: '.$batches->finished_at.
        //     ' - Processing: '.$batches->progress().'%'.
        //     ' - Send: '. $batches->processedJobs().
        //     ' - Fail: '.$batches->failed_jobs;
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
    //     return ['customers_synchronized'];
    // }
    // public function broadcastAs(){
    //     return 'synchronized_customer';
    // }
    // public function broadcastOn()
    // {
    //     return ['MailSent'];
    // }
    // public function broadcastAs(){
    //     return 'send-done';
    // }
}
