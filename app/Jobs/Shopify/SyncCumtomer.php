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


class SyncCumtomer
 implements ShouldQueue
{

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $customers;
    public $store_id;
    public $batch_id;
    public function __construct($batch_id,$store_id,$customers)
    {
        $this->customers = $customers;
        $this->store_id = $store_id;
        $this->batch_id = $batch_id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

      info("---verify connect");
      // info(json_encode($this->customers,true));
        // $customer_model = (new Customer());
        $customer_model_builder = getConnectDatabaseActived(new Customer());
        $customer_model = $customer_model_builder->getModel();

        $store_id = $this->store_id;
        $customers = $this->customers;

        // $customers = $this->customers;
      info("---get connect active ");
        data_set($customers, '*.store_id', $store_id);
      // info("Shopify: save customers: ".$customer_model->getConnection()->getName());
      // info("Shopify: get ta customers: ".$customer_model->getModels()->getTable());
        // $getCustomer = $customer_model->all();
        // info('All customer: '. json_encode($getCustomer, true));

        foreach ($customers as $customer) {
            $created_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['created_at']);
            $updated_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['updated_at']);
            info("--Customer Shopiyfy: ".json_encode($customer,true));

            foreach ($customer['addresses'] as $item) {
                $country = $item['country'];
                info("--Customer Addresre: ".$customer['email']);
                $data = [
                    'id' => $customer['id'],
                    'store_id' => $store_id,
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


                $findCustomer =  $customer_model->where('id', $data['id'])->first();
                // info('Id cua Customer:'.json_encode($findCustomer, true));
                info("--name: ".json_encode($findCustomer,true));
                if (empty($findCustomer)) {

                  try {
                    $customer_model->create($data);
                    $customer_eloquent = $customer_model->where("id",$data['id'])->first();
                    info("Create Customer: ...  ". json_encode($customer_eloquent, true));
                    $connect = ($customer_model->getConnection()->getName());
                    SyncDatabaseAfterCreatedModel($connect,$customer_eloquent);
                  } catch (\Throwable $th) {
                    info("Sync customer form shopiuf: ". $th);
                  }

                } else {
                    info('Update Customer: ...'.  json_encode($findCustomer, true));
                    $findCustomer->update($data);
                    $connect = ($customer_model->getConnection()->getName());
                    SyncDatabaseAfterUpdatedModel($connect,$findCustomer);

                }
            }


            // event(new CreatedModel($connect,$data,$customer_model->getTable()));
              info("CreatedModel: show log in function sycn custoemr: ");

            // $model->create($data);

        }
        event(new SyncingCustomer($this->batch_id));
    }
}
