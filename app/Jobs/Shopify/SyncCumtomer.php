<?php

namespace App\Jobs\Shopify;

use App\Events\Database\UpdatedModel;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Events\Database\CreatedModel;
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
    public $storeID;
    public $batchID;
    public function __construct($batchID,$storeID,$customers)
    {
        $this->customers = $customers;
        $this->storeID = $storeID;
        $this->batchID = $batchID;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        info("---verify connect");

        $customerModelBuilder = getConnectDatabaseActived(new Customer());
        $customerModel = $customerModelBuilder->getModel();

        $storeID = $this->storeID;
        $customers = $this->customers;

        // $customers = $this->customers;
        info("---get connect active ");
        data_set($customers, '*.store_id', $storeID);

        foreach ($customers as $customer) {
            $created_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['created_at']);
            $updated_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['updated_at']);
            info("--Customer Shopiyfy: ".json_encode($customer,true));

            foreach ($customer['addresses'] as $item) {
                $country = $item['country'];
                info("--Customer Addresre: ".$customer['email']);
                $data = [
                    'id' => $customer['id'],
                    'store_id' => $storeID,
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

                $findCustomer =  $customerModel->where('id', $data['id'])->first();

                info("--name: ".json_encode($findCustomer,true));
                if (empty($findCustomer)) {

                  try {
                    $customerModel->create($data);
                    $customer = $customerModel->where("id",$data['id'])->first();
                    info("Create Customer: ...  ". json_encode($customer, true));
                    $connect = ($customerModel->getConnection()->getName());
                    SyncDatabaseAfterCreatedModel($connect,$customer);
                  } catch (\Throwable $th) {
                    info("Sync customer form shopiuf: ". $th);
                  }

                } else {
                    info('Update Customer: ...'.  json_encode($findCustomer, true));
                    $findCustomer->update($data);
                    $connect = ($customerModel->getConnection()->getName());
                    SyncDatabaseAfterUpdatedModel($connect,$findCustomer);

                }
            }

              info("CreatedModel: show log in function sycn custoemr: ");


        }
        event(new SyncingCustomer($this->batchID));
    }
}
