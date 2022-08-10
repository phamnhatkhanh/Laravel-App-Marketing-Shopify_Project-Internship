<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Customer;

class DeleteCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $data_customer;
    private $customer;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data_customer)
    {
        $this->data_customer = $data_customer;
        $this->customer = getConnectDatabaseActived(new Customer());

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data_customer = $this->data_customer;

        $id = $data_customer['id'];
        $this->customer->where('id', $id)->delete();
    }
}
