<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Factory\FactoryHelper;
use App\Models\Store;
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'store_id'=>FactoryHelper::getRandomModelId(Store::class),
            'name'=>$this->faker->userName,
            'subject'=>$this->faker->sentence(6,true),
            'content'=> $this->faker->paragraph(3,true),
            'footer'=>$this->faker->sentence(6,true),
        ];
    }
}

