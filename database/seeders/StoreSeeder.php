<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Store;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    private static $id = 1;
    public function run()
    {

        $stores = Store::factory()->times(5)->create();
        foreach ($stores as  $store) {
            $store->id = self::$id++;
            SyncDatabaseAfterCreatedModel($store->getConnection()->getName(),$store);
        }
    }
}
