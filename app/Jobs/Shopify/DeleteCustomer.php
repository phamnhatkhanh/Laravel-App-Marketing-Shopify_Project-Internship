<?php

namespace App\Jobs\Shopify;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Customer;
use App\Events\Database\UpdatedModel;
use App\Events\Database\DeletedModel;

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
        // $this->customer = getConnectDatabaseActived(new Customer());

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $customer_model_builder = getConnectDatabaseActived(new Customer());
        $customer_model = $customer_model_builder->getModel();
       

        $data_customer = $this->data_customer;
        $id = $data_customer['id'];
        $customer = $customer_model->where('id', $id)->first();

        if (!empty($customer)) {
            $connect = ($customer_model->getConnection()->getName());
            event(new DeletedModel($connect, $customer));

        }
    }
}
