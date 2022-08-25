<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

use App\Models\Store;
use App\Models\Customer;
class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    private static $id = 1;

    public function run()
    {
       $customers = Customer::factory()->times(5)
        ->state(new Sequence(
            fn ($sequence) => ['store_id'=>getRandomModelId(Store::class)]
        ))->create();

        foreach ($customers as  $customer) {
            $customer->id = self::$id++;
            SyncDatabaseAfterCreatedModel($customer->getConnection()->getName(),$customer);
        }

    }
}
