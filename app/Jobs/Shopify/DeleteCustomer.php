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
    private $dataCustomer;
    private $customer;
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $customerModelBuilder = getConnectDatabaseActived(new Customer());
        $customerModel = $customerModelBuilder->getModel();


        $dataCustomer = $this->dataCustomer;
        $id = $dataCustomer['id'];
        $customer = $customerModel->where('id', $id)->first();

        if (!empty($customer)) {
            $connect = ($customerModel->getConnection()->getName());
            event(new DeletedModel($connect, $customer));

        }
    }
}
