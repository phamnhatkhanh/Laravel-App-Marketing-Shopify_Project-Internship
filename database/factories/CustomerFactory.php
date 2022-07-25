<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
            'title'=>$this->faker->text(25),
            'desc'=>$this->faker->text(50),
            'price' => $this->faker->numberBetween(100,1000),
            'stock'=> $this->faker->randomDigit,
            'discount' => $this->faker->numberBetween(2,30),
            'user_id' => function(){
        	        return User::all()->random();
            },
        ];
    }
}
