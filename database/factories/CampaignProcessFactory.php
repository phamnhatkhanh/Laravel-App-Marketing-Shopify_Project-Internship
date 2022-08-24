<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Campaign;
use App\Models\Store;

class CampaignProcessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    private static $id = 1;
    public function definition()
    {

        return [
            'id' => self::$id++,
            'store_id'=>getRandomModelId(Store::class),
            // 'campaign_id'=>getRandomModelId(Campaign::class),

            'name'=>$this->faker->userName,
            'status'=>"running",
            'process'=>$this->faker->numberBetween(0,100),
            'send_email_done'=>$this->faker->numberBetween(0,20),
            'send_email_fail'=>$this->faker->numberBetween(0,20),
            'total_customers'=>$this->faker->numberBetween(20,50)
        ];

    }
}
