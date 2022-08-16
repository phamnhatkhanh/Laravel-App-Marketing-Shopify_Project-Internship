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
        $stores = Store::factory()->times(1)->create();
        // info("data_fake_store" . $stores);
        foreach ($stores as  $store) {
            $store->id = self::$id++;
            // info("data_fake_store_id: ".$store->id);
            Store::on('mysql_stores_backup')->create(($store->toArray()));
        }
    }
}
