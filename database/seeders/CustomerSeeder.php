<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    private static $id = 1;
    public function run()
    {
        $customers = Customer::factory()->times(5)->create();

        foreach ($customers as  $customer) {
            $customer->id = self::$id++;
            Customer::on('mysql_customers_backup')->create(($customer->toArray()));
            Customer::on('mysql_customers_backup_1')->create(($customer->toArray()));
        }

    }
}
