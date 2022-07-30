<?php

namespace App\Repositories\Eloquents;

use App\Models\Customer;
use App\Models\Shopify;

class CustomerWebhookRepository
{
    
    //Lưu khách hàng vào DB
    public static function saveDataCustomer($getCustomer)
    {
        $saveCustomers = $getCustomer['customers'];

        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');

        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');

        foreach ($saveCustomers as $customer) {
            $created_at = str_replace($findCreateAT, $replaceCreateAT, $customer->created_at);
            $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $customer->updated_at);

            Customer::create([
                'id' => $customer->id,
                'email' => $customer->email,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'orders_count' => $customer->orders_count,
                'total_spent' => $customer->total_spent,
                'phone' => $customer->phone,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ]);
        }
    }
}
