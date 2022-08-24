<?php

namespace App\Jobs\Shopify;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

use App\Models\Customer;

class CreateDataCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * List data customer get from shopify.
     *
     * @var mixed
     */
    private $customers;

    /**
     * The primary key of the store.
     *
     * @var string
     */
    public $storeId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customers, $storeId)
    {
        $this->customers = $customers;
        $this->storeId = $storeId;
    }

    /**
     * Create customer when get data customer from shopify and sync data in the database model cluster.

     *
     * @return void
     */
    public function handle()
    {
        info("---verify connect");


        $customerModelBuilder = setConnectDatabaseActived(new Customer());
        $customerModel = $customerModelBuilder->getModel();

        $storeId = $this->storeId;
        $customers = $this->customers;
        info("---get connect active ");
        data_set($customers, '*.store_id', $storeId);


        foreach ($customers as $customer) {
            $created_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['created_at']);
            $updated_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['updated_at']);
            info("--Customer Shopiyfy: " . json_encode($customer, true));

            $country = $customer['addresses'][0]['country'];
            info("--Customer Addresre: " . $customer['email']);
            $data = [
                'id' => $customer['id'],
                'store_id' => $storeId,
                'email' => $customer['email'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'orders_count' => $customer['orders_count'],
                'total_spent' => $customer['total_spent'],
                'phone' => $customer['phone'],
                'country' => $country,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ];

            $findCustomer = $customerModel->where('id', $data['id'])->first();
            // info('Id cua Customer:'.json_encode($findCustomer, true));
            info("--name: " . json_encode($findCustomer, true));
            if (empty($findCustomer)) {

                try {
                    $customerModel->create($data);
                    $customer = $customerModel->where("id", $data['id'])->first();
                    info("Create Customer: ...  " . json_encode($customer, true));
                    $connect = ($customerModel->getConnection()->getName());
                    SyncDatabaseAfterCreatedModel($connect, $customer);
                } catch (\Throwable $th) {
                    info("Sync customer form shopiuf: " . $th);
                }

            } else {
                try {
                    //code...
                    info('Update Customer: ...' . json_encode($findCustomer, true));
                    $findCustomer->update($data);
                    $connect = $customerModel->getConnection()->getName();
                    SyncDatabaseAfterUpdatedModel($connect, $findCustomer);
                } catch (\Throwable $th) {
                    throw $th;
                }

            }
        }

    }
}

