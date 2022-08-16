<?php

namespace App\Jobs;

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
        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');
        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');

        // $store = $this->store->latest()->first();
        data_set($customers, '*.store_id', $this->store_id);

        info("Sho pify: save customers");
        foreach ($this->customers as $customer){
            $created_at = str_replace($findCreateAT, $replaceCreateAT, $customer['created_at']);
            $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $customer['updated_at']);

            foreach ($customer['addresses'] as $item){
                $country = $item['country'];
                $data = [
                    'id' => $customer['id'],
                    'store_id' => $this->store_id,
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

                $connect = ($customer_model->getConnection()->getName());
                event(new CreatedModel($connect,$data,$customer_model->getTable()));
                // $this->customer->insert($data);
            }

            // if (!$this->customer->find($data['id'])){
            // }
        }
        event(new SyncingCustomer($this->batch_id));
    }
}
