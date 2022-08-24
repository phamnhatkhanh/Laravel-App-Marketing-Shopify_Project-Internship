<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\CampaignProcess;
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

        Campaign::factory()->times(5)->create()->each(function($campaign){
            $connect = ($campaign->getConnection()->getName());
            SyncDatabaseAfterCreatedModel($connect,$campaign);
            CampaignProcess::factory(1)->create([
                'store_id' => $campaign->store_id,
                'campaign_id' => $campaign->id
            ])->each(function($campaignProcess){
                $campaignProcess->id = self::$id++;
                SyncDatabaseAfterCreatedModel($campaignProcess->getConnection()->getName(),$campaignProcess);
            });

        });



    }


}
