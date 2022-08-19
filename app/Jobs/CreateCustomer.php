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
        $this->myshopify_domain = $myshopify_domain;
        // $this->customer = getConnectDatabaseActived(new Customer());
        // $this->store = getConnectDatabaseActived(new Store());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customer_model = new Customer();
        $store_model = new Store();
        $data_customer = $this->data_customer;
        $myshopify_domain = $this->myshopify_domain;

        $created_at = str_replace(array('T', '+07:00'), array(' ', ''), $data_customer['created_at']);
        $updated_at = str_replace(array('T', '+07:00'), array(' ', ''), $data_customer['updated_at']);

        $store = $store_model->where('myshopify_domain', $myshopify_domain)->first();

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
        
        $customer_model->create($data);
        $customer_eloquent = $customer_model->where("id",$data_customer['id'])->first();
        info("Create Customer: ...  ". json_encode($customer_eloquent, true));
        $connect = ($customer_model->getConnection()->getName());
        SyncDatabaseAfterCreatedModel($connect,$customer_eloquent);

    }
}
