<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Factory\FactoryHelper;
use App\Models\Campaign;
class Campaign_ProcessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'campaign_id'=>FactoryHelper::getRandomModelId(Campaign::class),
            'name'=>$this->faker->username,
            'status' => $this->faker->randomElement(['running', 'completed','pending']),
            'process'=> $this->faker->numberBetween(0,100),
            'send_email_done'=> $this->faker->numberBetween(0,100),
            'send_email_fail'=> $this->faker->numberBetween(0,100),
            'total_customers'=> $this->faker->numberBetween(100,1000),
            'created_at'=>$this->faker->dateTime(),
            'updated_at'=>$this->faker->dateTime(),

        ];
    }
}
