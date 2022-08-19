<?php

namespace App\Services\Customers;

class CustomerService
{
    /**
     * Open File and Add attributes, value
     *
     * @param object $fileName
     * @param object $users
     * @return void
     */
    public static function exportCustomer($fileName, $users){
        $handle = fopen($fileName, 'w');

        fputcsv($handle, array(
            'ID', 'Store_ID', 'First_Name', 'Last_Name', 'Email', 'Phone',
            'Country', 'Orders_count', 'Total_Spent', 'Created_At', 'Updated_At'
        ));

        foreach ($users as $item) {
            fputcsv($handle, array(
                $item->id, $item->store_id, $item->first_name, $item->last_name, $item->email, $item->phone,
                $item->country, $item->orders_count, $item->total_spent, $item->created_at, $item->updated_at
            ));
        }

        fclose($handle);

        $headers = array(
            'Content-Type' => 'text/csv',
        );
    }
}
