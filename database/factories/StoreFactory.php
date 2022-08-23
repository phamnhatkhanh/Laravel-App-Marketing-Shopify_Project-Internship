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
            'id' => 65147142383,
            // 'id' => self::$id++,
            'name_merchant'=>$this->faker->username,
            'email'=> $this->faker->email,
            'phone'=>$this->faker->phoneNumber,
            'password'=>$this->faker->password,
            'myshopify_domain'=>$this->faker->username.'.myshopify.com',
            'domain'=>$this->faker->domainName,
            'access_token'=>Str::random(10),
            'address' => $this->faker->address,
            'province'=>$this->faker->state,
            'city'=>$this->faker->city,
            'zip'=>$this->faker->numberBetween(1000, 7000),
            'country_name'=>$this->faker->country,
            'created_at'=>$this->faker->dateTimeThisYear(),
            'updated_at'=>$this->faker->dateTimeThisMonth(),
        ];
    }
}
