<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Factories\Sequence;

use App\Models\Store;
use App\Models\Campaign;
use App\Models\CampaignProcess;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        //Remeber use command php artisan setupDB.
        Campaign::factory()->times(3)->state(new Sequence(
                fn ($sequence) => ['store_id'=>getRandomModelId(Store::class)]
            ))->create()
            ->each(function($campaign){
                info("--Create campaign: " . json_encode($campaign,true));
                $connect = ($campaign->getConnection()->getName());
                SyncDatabaseAfterCreatedModel($connect,$campaign);

                CampaignProcess::factory(1)->create([
                    'store_id' => $campaign->store_id,
                    'campaign_id' => $campaign->id
                ])->each(function($campaignProcess){
                    SyncDatabaseAfterCreatedModel($campaignProcess->getConnection()->getName(),$campaignProcess);
                });
        });
    }
}

