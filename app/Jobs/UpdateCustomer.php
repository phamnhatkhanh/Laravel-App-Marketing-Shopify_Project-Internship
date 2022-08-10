<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Session;

class UpdateCustomer implements ShouldQueue
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
        $data_customer = $this->customer;
        $data_customer_id = $data_customer['id'];

        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');

        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');

        $created_at = str_replace($findCreateAT, $replaceCreateAT, $data_customer['created_at']);
        $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $data_customer['updated_at']);


        $this->customer->where('id', $data_customer_id)->update([
            'email' => $data_customer['email'],
            'first_name' => $data_customer['first_name'],
            'last_name' => $data_customer['last_name'],
            'orders_count' => $data_customer['orders_count'],
            'total_spent' => $data_customer['total_spent'],
            'phone' => $data_customer['phone'],
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);
    }
}
