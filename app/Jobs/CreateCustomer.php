<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Session;

class CreateCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $customer, $myshopify_domain;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customer,$myshopify_domain)
    {
        $this->customer = $customer;
        $this->myshopify_domain = $myshopify_domain;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customer = $this->customer;
        $myshopify_domain = $this->myshopify_domain;

        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');

        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');

        $created_at = str_replace($findCreateAT, $replaceCreateAT, $customer['created_at']);
        $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $customer['updated_at']);

        $store = Store::where('myshopify_domain', $myshopify_domain)->first();

        Customer::create([
            'id' => $customer['id'],
            'store_id' => $store->id,
            'email' => $customer['email'],
            'first_name' => $customer['first_name'],
            'last_name' => $customer['last_name'],
            'orders_count' => $customer['orders_count'],
            'total_spent' => $customer['total_spent'],
            'phone' => $customer['phone'],
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);
    }
}
