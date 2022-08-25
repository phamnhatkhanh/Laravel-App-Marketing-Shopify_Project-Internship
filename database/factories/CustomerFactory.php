<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Store;
use App\Models\Customer;

class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    private static $id = 6;

    public function definition()
    {
        // $id =6;
        // info("-- indes functino csutoem factoey ".$id);
        return [
            'id' => self::$id++,
//             'store_id'=>getRandomModelId(Store::class),
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


