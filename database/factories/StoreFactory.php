<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class StoreFactory extends Factory
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
            // 'id' => $this->faker->numberBetween(1000, 7000),
            'name_merchant'=>$this->faker->username,
            'email'=> $this->faker->email,
            'phone'=>$this->faker->phoneNumber,
            'password'=>$this->faker->password,
            'myshopify_domain'=>'https://khanhpham530112313.myshopify.com',
            'domain'=>$this->faker->domainName,
            'access_token'=>Str::random(10),
            'address' => $this->faker->address,
            'province'=>$this->faker->state,
            'city'=>$this->faker->city,
            'zip'=>$this->faker->numberBetween(1000, 7000),
            'country_name'=>$this->faker->country,
            'created_at'=>$this->faker->dateTime(),
            'updated_at'=>$this->faker->dateTime(),
        ];
    }
}
