<?php

namespace App\Repositories\Eloquents;

use App\Models\Customer;
use App\Models\Shopify;
use Session;
use Illuminate\Support\Str;

class CustomerWebhookRepository
{

    //Lưu khách hàng vào DB
    public static function saveDataCustomer($getCustomer)
    {
        $saveCustomers = $getCustomer['customers'];
        info($saveCustomers);
        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');

        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');
        // $store_id = Session::get('store_id');
        foreach ($saveCustomers as $customer) {
            $created_at = str_replace($findCreateAT, $replaceCreateAT, $customer->created_at);
            $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $customer->updated_at);

            Customer::create([
                'id' => $customer->id,
                'store_id'  => 65147142383,
                'email' => $customer->email,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'orders_count' => $customer->orders_count,
                'total_spent' => $customer->total_spent,
                'phone' => '574-805-7422',
                // 'phone' => $customer->phone,
                // 'created_at' => $created_at,
                // 'updated_at' => $updated_at,
            ]);
        }
    }
}
