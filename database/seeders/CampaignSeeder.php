<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
class CampaignSeeder extends Seeder
{
    private static $id = 1;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $campaigns = Campaign::factory()->times(50)->create();
        foreach ($campaigns as  $campaign) {
            $campaign->id = self::$id++;
            Campaign::on('mysql_campaigns_backup')->create(($campaign->toArray()));

        }
    }
}
