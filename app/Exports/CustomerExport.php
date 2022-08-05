<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Customer::all();
    }

    public function headings() :array{
        return ['ID', 'Store_ID', 'First_Name', 'Last_Name', 'Email', 'Phone', 'Country', 'Orders_count', 'Total_Spent', 'Created_At', 'Updated_At'];
    }
}
