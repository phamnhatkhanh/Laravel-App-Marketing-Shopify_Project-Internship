<?php

namespace App\Jobs\Shopify;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\Request;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;

use App\Events\Database\CreatedModel;

use App\Models\Customer;
use App\Models\Store;

class CreateCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Data customer get from shopify.
     *
     * @var mixed
     */
    private $dataCustomer;

    /**
     * Original domain store shopify
     *
     * @var string
     */
    private $myShopifyDomain;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dataCustomer,$myShopifyDomain)
    {
        $this->dataCustomer = $dataCustomer;
        $this->myShopifyDomain = $myShopifyDomain;
    }

    /**

     * Create customer when get data customer from shopify and sync data in the database model cluster.

     *
     * @return void
     */
    public function handle()
    {


        $customerModelBuilder = setConnectDatabaseActived(new Customer());
        $customerModel = $customerModelBuilder->getModel();
        $storeModelBuilder = setConnectDatabaseActived(new Store());
        $storeModel = $storeModelBuilder->getModel();

        $dataCustomer = $this->dataCustomer;
        $myShopifyDomain = $this->myShopifyDomain;

        $created_at = str_replace(array('T', '+07:00'), array(' ', ''), $dataCustomer['created_at']);
        $updated_at = str_replace(array('T', '+07:00'), array(' ', ''), $dataCustomer['updated_at']);

        $store = $storeModel->where('myshopify_domain', $myShopifyDomain)->first();

        $data = [
            'id' => $dataCustomer['id'],
            'store_id' => $store->id,
            'email' => $dataCustomer['email'],
            'first_name' => $dataCustomer['first_name'],
            'last_name' => $dataCustomer['last_name'],
            'orders_count' => $dataCustomer['orders_count'],
            'total_spent' => $dataCustomer['total_spent'],
            'phone' => $dataCustomer['phone'],
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];

        Schema::connection($customerModel->getConnection()->getName())->disableForeignKeyConstraints();
            $customerModel->create($data);
        Schema::connection($customerModel->getConnection()->getName())->enableForeignKeyConstraints();

        $customer = $customerModel->where("id",$dataCustomer['id'])->first();
        info("Create Customer: ...  ". json_encode($customer, true));
        $connect = $customerModel->getConnection()->getName();
        SyncDatabaseAfterCreatedModel($connect,$customer);

    }
}
