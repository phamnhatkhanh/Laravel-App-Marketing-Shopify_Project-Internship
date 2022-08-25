<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

use App\Models\Store;
use App\Models\Customer;

class SetupDataInApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    // private  $id = 1;
    private static $id;
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $storeName = $this->ask('Please enter name store');
        $storeModelBuilder = setConnectDatabaseActived(new Store());
        $storeModel = $storeModelBuilder->getModel();
        $store = $storeModel->where('name_merchant', 'LIKE', $storeName.'%')->first();

        if(!empty($store)){

            $action = $this->ask('What do you refresh the data for ?');


            $customerModelBuilder = setConnectDatabaseActived(new Customer());
            $customerModel = $customerModelBuilder->getModel();

            if(strtolower($action) == "find"){
                $customers = $customerModel->where('store_id',$store->id)->get();
                $customers->each(function($customer){

                    SyncDatabaseAfterDeletedModel($customer->getConnection()->getName(),$customer);
                });

                $idMaxCustomer = getUniqueId(Customer::class);

                $number = $this->ask('How many customers do you need?');
                Schema::connection($customerModel->getConnection()->getName())->disableForeignKeyConstraints();

                    $customerModel->factory()->times($number)->make(['store_id' => $store->id])
                    ->each(function($customer) use (&$idMaxCustomer,&$customerModel ){

                        $idMaxCustomer++;
                        $customer->id = $idMaxCustomer;
                        $customerModel->create($customer->toArray());

                        SyncDatabaseAfterCreatedModel($customer->getConnection()->getName(),$customer);
                    });

                Schema::connection($customerModel->getConnection()->getName())->enableForeignKeyConstraints();

                $this->info("Delete data customer from shopify in database and seed new data customer.");

            }elseif(strtolower($action) == "sync"){

                $customers = $customerModel->where('store_id',$store->id)->get();
                $customers ->each(function($customer){
                    SyncDatabaseAfterDeletedModel($customer->getConnection()->getName(),$customer);
                });

                $this->info("Delete all customer of store.");

            }elseif(strtolower($action) == "uninstall"){

                SyncDatabaseAfterDeletedModel($store->getConnection()->getName(),$store);

                $this->info("Delete store and table relative store in database.");

            }else{

                $this->info("Action: ". strtoupper($action) . " not exist in list action on Command ");

            }
        }else{

            $this->info("Not found store!!!");

        }

    }
}

