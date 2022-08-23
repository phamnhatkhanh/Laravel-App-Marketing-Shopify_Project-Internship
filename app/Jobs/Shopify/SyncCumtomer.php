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
    public $storeID;

    /**
     * The primary key of the job batch.
     *
     * @var string
     */
    public $batchID;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($batchID,$storeID,$customers)
    {
        $this->customers = $customers;
        $this->storeID = $storeID;
        $this->batchID = $batchID;
    }

    /**
     * Get customer from shopify and update in database.
     *
     * @return void
     */
    public function handle()
    {
        info("1...Sycncustoemr: get connect actived");
        $customerModelBuilder = setConnectDatabaseActived(new Customer());
        $customerModel = $customerModelBuilder->getModel();

        $storeID = $this->storeID;
        $customers = $this->customers;

        data_set($customers, '*.store_id', $storeID);

        foreach ($customers as $customer) {
            $created_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['created_at']);
            $updated_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['updated_at']);


            foreach ($customer['addresses'] as $item) {
                $country = $item['country'];

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

                if (empty($findCustomer)) {
                  try {
                    $customerModel->create($data);
                    $customer = $customerModel->where("id",$data['id'])->first();
                    info("-SyncCumtomer Create Customer: ...  ". json_encode($customer, true));
                    $connect = ($customerModel->getConnection()->getName());
                    SyncDatabaseAfterCreatedModel($connect,$customer);
                  } catch (\Throwable $th) {
                    info("Sync customer form shopify: ". $th);
                  }
                } else {
                    info('-SyncCumtomer Update Customer: ...'.  json_encode($findCustomer, true));
                    $findCustomer->update($data);
                    $connect = ($customerModel->getConnection()->getName());
                    SyncDatabaseAfterUpdatedModel($connect,$findCustomer);

                }
            }
        }
        info("2...Sycncustoemr: get success connect actived");
        event(new SyncingCustomer($this->batchID));
    }
}
