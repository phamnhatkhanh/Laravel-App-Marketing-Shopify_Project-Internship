<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Store;
class CustomerFactory extends Factory
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
            'store_id'=> 65147142383,
            // 'store_id'=>getRandomModelId(Store::class),
            // 'campaign_id'=>getRandomModelId(Campaign::class),
            'first_name'=>$this->faker->firstNameMale,
            'last_name'=>$this->faker->lastName,
            'email'=> $this->faker->email,
            'phone'=>$this->faker->phoneNumber,
            'country'=>$this->faker->country,
            'orders_count'=>$this->faker->numberBetween(0,500),
            'total_spent'=>$this->faker->numberBetween(500,4000),
            'created_at'=>$this->faker->dateTimeThisYear(),
            'updated_at'=>$this->faker->dateTimeThisMonth(),
        ];
    }
}


