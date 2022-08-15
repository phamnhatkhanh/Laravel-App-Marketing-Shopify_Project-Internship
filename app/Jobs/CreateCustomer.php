<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\Request;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Session;
use App\Events\Database\CreatedModel;
use App\Models\Customer;
use App\Models\Store;

class CreateCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data_customer,$customer, $myshopify_domain;
    protected $store;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data_customer,$myshopify_domain)
    {
        $this->data_customer = $data_customer;
        // $this->customer = getConnectDatabaseActived(new Customer());
        // $this->store = getConnectDatabaseActived(new Store());
        $this->myshopify_domain = $myshopify_domain;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data_customer = $this->data_customer;
        $myshopify_domain = $this->myshopify_domain;

        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');

        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');

        $created_at = str_replace($findCreateAT, $replaceCreateAT, $data_customer['created_at']);
        $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $data_customer['updated_at']);

        $store = $this->store->where('myshopify_domain', $myshopify_domain)->first();

        info("Job CreatedModel: ".$store->id);
        $data = [
            'id' => $data_customer['id'],
            'store_id' => $store->id,
            'email' => $data_customer['email'],
            'first_name' => $data_customer['first_name'],
            'last_name' => $data_customer['last_name'],
            'orders_count' => $data_customer['orders_count'],
            'total_spent' => $data_customer['total_spent'],
            'phone' => $data_customer['phone'],
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];

        info("Job CreatedModel: first_name ".$data_customer['first_name']);
        $connect = ($this->customer->getConnection()->getName());
        event(new CreatedModel($connect,$data,$this->customer->getModel()->getTable()));

        // $this->customer->create([
        //     'id' => $data_customer['id'],
        //     'store_id' => $store->id,
        //     'email' => $data_customer['email'],
        //     'first_name' => $data_customer['first_name'],
        //     'last_name' => $data_customer['last_name'],
        //     'orders_count' => $data_customer['orders_count'],
        //     'total_spent' => $data_customer['total_spent'],
        //     'phone' => $data_customer['phone'],
        //     'created_at' => $created_at,
        //     'updated_at' => $updated_at,
        // ]);
    }
}
