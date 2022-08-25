<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Store;
use App\Models\Customer;
use App\Helpers\Database\Factory as Khanh;

class CustomerFactory extends Factory
{
    private static $id=1;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'id' => self::$id++,
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





