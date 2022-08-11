<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerExport implements FromCollection, WithHeadings
{
    protected $customer;

    public function __construct(){
        $this->customer = getConnectDatabaseActived(new Customer());

    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->customer->get();
    }

    public function headings() :array{
        return ['ID', 'Store_ID', 'First_Name', 'Last_Name', 'Email', 'Phone', 'Country', 'Orders_count', 'Total_Spent', 'Created_At', 'Updated_At'];
    }
}
