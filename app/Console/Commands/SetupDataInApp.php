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

        $storeName = $this->ask('Please choose store: ');

        $storeModelBuilder = setConnectDatabaseActived(new Store());
        $storeModel = $storeModelBuilder->getModel();
        $store = $storeModel->where('name_merchant', 'LIKE', $storeName.'%')->first();
        info("1");
        if(!empty($store)){

            $action = $this->ask('What do you refresh the data for?: ');

            $customerModelBuilder = setConnectDatabaseActived(new Customer());
            $customerModel = $customerModelBuilder->getModel();

            if(strtolower($action) == "find"){
                $store->customers()->each(function($customer){
                    SyncDatabaseAfterDeletedModel($customer->getConnection()->getName(),$customer);
                });

                self::$id = $customerModel->max('id');
                Schema::connection($customerModel->getConnection()->getName())->disableForeignKeyConstraints();
                    $customerModel->factory()->times(200)->create([
                        'store_id'=>$store->id
                    ])->each(function($customer){
                        $customer->id = self::$id++;
                        SyncDatabaseAfterCreatedModel($customer->getConnection()->getName(),$customer);
                    });
                Schema::connection($customerModel->getConnection()->getName())->enableForeignKeyConstraints();

                $this->info("Delete data customer from shopify in database and seed new data customer.");

            }elseif(strtolower($action) == "sync"){

                $store->customers()->each(function($customer){
                    SyncDatabaseAfterDeletedModel($customer->getConnection()->getName(),$customer);
                });
                $this->info("Delete all customer of store.");

            }elseif(strtolower($action) == "test"){

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

