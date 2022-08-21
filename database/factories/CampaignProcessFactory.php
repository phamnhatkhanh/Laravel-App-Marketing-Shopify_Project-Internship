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
    public function definition()
    {
        return [
            'store_id'=>function(){
                return Store::all()->random()->id;
            },

            'campaign_id'=>function(){
                return Campaign::all()->random()->id;
            },
            'name'=>$this->faker->userName,
            'status'=>"running",
            'process'=>$this->faker->numberBetween(0,100),
            'send_email_done'=>$this->faker->numberBetween(0,20),
            'send_email_fail'=>$this->faker->numberBetween(0,20),
            'total_customers'=>$this->faker->numberBetween(20,50)
        ];

    }
}
