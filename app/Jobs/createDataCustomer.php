<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class createDataCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $customers, $store_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customers, $store_id)
    {
        $this->customers = $customers;
        $this->store_id = $store_id;

        /**
         * Execute the job.
         *
         * @return void
         */
    }

    public function handle()
    {
        $store_id = $this->store_id;
        $customers = $this->customers;
        $customerModel = new Customer();
        data_set($customers, '*.store_id', $store_id);

        info("Shopify: save customers");
        $getCustomer = $customerModel->get();

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

                if (empty($findCustomer)) {
                    // Schema::connection($customerModel->getConnection()->getName())->disableForeignKeyConstraints();

                    // $campaignProcess = $this->campaignProcess->create($request->all());

                        // info('Create Customer: ...'.  json_encode($findCustomer, true));

                    $customerModel->create($data);
                     $customer_eloquent = $customerModel->where("id",$customer['id'])->first();
                    info("Create Customer: ...  ". json_encode($customer_eloquent, true));
                    $connect = ($customerModel->getConnection()->getName());
                    SyncDatabaseAfterCreatedModel($connect,$customer_eloquent);
                    // Schema::connection($customerModel->getConnection()->getName())->enableForeignKeyConstraints();
                } else {
                        info('Update Customer: ...'.  json_encode($findCustomer, true));
                    $findCustomer->update($data);
                    $connect = ($customerModel->getConnection()->getName());
                    SyncDatabaseAfterUpdatedModel($connect, $findCustomer);
                }
            }
        }
    }
}

