<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign_Process;
class CampaignProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Campaign_Process::factory()->times(5)->create();
    }
}
// Database\Factories\
