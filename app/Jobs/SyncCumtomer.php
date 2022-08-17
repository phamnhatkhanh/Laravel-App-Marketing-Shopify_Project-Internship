<?php

namespace App\Jobs;

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
use Illuminate\Support\Facades\DB;

class SyncCumtomer implements ShouldQueue
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

        $customer_model = new Customer();
        $store_id = $this->store_id;

        $customers = $this->customers;

        data_set($customers, '*.store_id', $store_id);
        info("Shopify: save customers");
        $getCustomer = $customer_model::get();
        info('All customer: '. json_encode($getCustomer, true));
        
        foreach ($customers as $customer) {
            $created_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['created_at']);
            $updated_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['updated_at']);

            foreach ($customer['addresses'] as $item) {
                $country = $item['country'];

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
                $findCustomer = $getCustomer->where('id', $data['id'])->first();
                info('Id cua Customer:'.json_encode($findCustomer, true));


              //  if (empty($findCustomer)) {
                    info('Create Customer');
//                        $this->customer->create($data);
                 //   $connect = ($customer_model->getConnection()->getName());
                //    event(new CreatedModel($connect, $data, $customer_model->getModel()->getTable()));
                //} else {
                  //  info('Update Customer');
                 //   $findCustomer->update($data);
                 //   $connect = ($customer_model->getConnection()->getName());
                  //  event(new UpdatedModel($connect, $findCustomer));
               // }

                $connect = ($customer_model->getConnection()->getName());
                SyncDatabaseAfterCreatedModel($connect,$data,$customer_model->getTable());
                // event(new CreatedModel($connect,$data,$customer_model->getTable()));
                 info("CreatedModel: show log in function sycn custoemr: ");
                showLog();
                // $model->create($data);

            }
        }
        event(new SyncingCustomer($this->batch_id));
    }
}
