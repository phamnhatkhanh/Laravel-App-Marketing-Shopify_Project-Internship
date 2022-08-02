<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Factory\FactoryHelper;
use App\Models\Store;
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->numberBetween(1,900),
            'store_id'=>FactoryHelper::getRandomModelId(Store::class),
            'first_name'=>$this->faker->firstNameMale,
            'last_name'=>$this->faker->lastName,
            'email'=> $this->faker->email,
            'phone'=>$this->faker->phoneNumber,
            'country'=>$this->faker->country,
            'orders_count'=>'https://khanhpham530112313.myshopify.com',
            'total_spent'=>$this->faker->numberBetween(1000, 7000),
            'created_at'=>$this->faker->dateTime(),
            'updated_at'=>$this->faker->dateTime(),
        ];
    }
}


