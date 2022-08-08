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
    public function run()
    {
        $customers = Customer::factory()->times(200)->create();

        foreach ($customers as  $customer) {
            // Customer::on('mysql_customers_backup')->create(($customer->toArray()));
            // Customer::on('mysql_customers_backup_1')->create(($customer->toArray()));
        }

    }
}
