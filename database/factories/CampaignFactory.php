<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Store;
class CampaignFactory extends Factory
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
            'name'=>$this->faker->userName,
            'subject'=>$this->faker->sentence(3,true),
            'content'=> $this->faker->sentence(3,true),
            'footer'=>$this->faker->sentence(3,true),
            'background_banner'=> $this->faker->hexcolor,
            'background_color'=> $this->faker->hexcolor,
            'background_radius'=> $this->faker->hexcolor,
            'button_label'=> $this->faker->hexcolor,
            'button_radius'=> $this->faker->hexcolor,
            'button_background_color'=> $this->faker->hexcolor,
            'button_text_color'=> $this->faker->hexcolor,
        ];
    }
}

