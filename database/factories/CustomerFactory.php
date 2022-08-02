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
    private static $id = 1;
    public function definition()
    {
         static $number;
        return [
            // 'id' => $this->faker->numberBetween(1,2000),
            'id' => self::$id++,
            'store_id'=>FactoryHelper::getRandomModelId(Store::class),
            'first_name'=>$this->faker->firstNameMale,
            'last_name'=>$this->faker->lastName,
            'email'=> $this->faker->email,
            'phone'=>$this->faker->phoneNumber,
            'country'=>$this->faker->country,
<<<<<<< HEAD
            'orders_count'=>$this->faker->numberBetween(0,500),
            'total_spent'=>$this->faker->numberBetween(500,4000),
=======
            'orders_count'=>'https://khanhpham530112313.myshopify.com',
            'total_spent'=>$this->faker->numberBetween(1000, 7000),
>>>>>>> 63dd166df9a3d4298aa3036daa2dc9661568b46b
            'created_at'=>$this->faker->dateTime(),
            'updated_at'=>$this->faker->dateTime(),
        ];
    }
}


