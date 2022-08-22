<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CampaignProcess;
class CampaignProcessSeeder extends Seeder
{
    private static $id = 1;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $campaignProcesses = CampaignProcess::factory()->times(50)->create();
        foreach ($campaignProcesses as  $campaignProcess) {
            $campaignProcess->id = self::$id++;
            CampaignProcess::on('mysql_campaigns_processes_backup')->create(($campaignProcess->toArray()));

        }
    }
}
// Database\Factories\
