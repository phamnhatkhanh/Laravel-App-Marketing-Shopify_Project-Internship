<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Store;
class DeleteCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dcs';

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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $store = Store::where('id',65147142383)->first();
        $store->customers()->each(function($customer){
            // info(json_encode($customer,true));
            SyncDatabaseAfterDeletedModel($customer->getConnection()->getName(),$customer);
        });

    }
}

