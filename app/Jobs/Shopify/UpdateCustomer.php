<?php

namespace App\Jobs\Shopify;

use App\Models\Customer;
use App\Events\Database\UpdatedModel;
use App\Events\Database\DeletedModel;

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

    /**
     * Data customer get from shopify.
     *
     * @var mixed
     */
    private $dataCustomer;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dataCustomer)
    {
        $this->dataCustomer = $dataCustomer;
    }

    /**
     * Update customer when get data customer from shopify and sync data in the database model cluster.
     *
     * @return void
     */
    public function handle()
    {
        info("UpdateCustomer: inside function ");
        $customerModelBuilder = setConnectDatabaseActived(new Customer());
        $customerModel = $customerModelBuilder->getModel();
        $dataCustomer = $this->dataCustomer;
        $dataCustomerID = $dataCustomer['id'];

        $createdAt = str_replace(array('T', '+07:00'), array(' ', ''), $dataCustomer['created_at']);
        $updatedAt = str_replace(array('T', '+07:00'), array(' ', ''), $dataCustomer['updated_at']);
        info("UpdateCustomer: id ".$dataCustomerID);
        info("UpdateCustomer: ".$dataCustomer['last_name']);

        $customer = $customerModel->where('id', $dataCustomerID)->first();
        $customer->update([
            'email' => $dataCustomer['email'],
            'first_name' => $dataCustomer['first_name'],
            'last_name' => $dataCustomer['last_name'],
            'orders_count' => $dataCustomer['orders_count'],
            'total_spent' => $dataCustomer['total_spent'],
            'phone' => $dataCustomer['phone'],
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);

        $connect = ($customer->getConnection()->getName());
        SyncDatabaseAfterUpdatedModel($connect, $customer);
    }
}
