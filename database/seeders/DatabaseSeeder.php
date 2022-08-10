<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(UserSeeder::class);
        $this->call(StoreSeeder::class);
        $this->call(CustomerSeeder::class);
        // $this->call(CampaignSeeder::class);
        // $this->call(CampaignProcessSeeder::class);
    }
}
