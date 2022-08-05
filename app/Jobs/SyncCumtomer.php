<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Events\SyncingCustomer;
use App\Models\Customer;
class SyncCumtomer implements ShouldQueue
{

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $customers;
    public $batch_id;
    public function __construct($batch_id,$chunkCumtomer)
    {
        $this->customers = $chunkCumtomer;
        $this->batch_id = $batch_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        foreach ($this->customers as $customer) {

            $customer->first_name="phamj";
            // info($customer->id);
            // $customer->update([
            //     $customer->first_name,
            //     $customer->last_name,
            //     $customer->email,
            //     $customer->phone,
            // ]);
        }
        event(new SyncingCustomer($this->batch_id))->onQueue('event');
    }
}
