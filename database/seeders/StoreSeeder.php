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
    public function run()
    {
        $stores = Store::factory()->times(5)->create();
        // info("data_fake_store" . $stores);
        foreach ($stores as  $store) {
            info("data_fake_store_id: ".$store->id);
            // Store::on('mysql_stores_backup')->create(($store->toArray()));
        }
    }
}
