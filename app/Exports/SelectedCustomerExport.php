<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SelectedCustomerExport implements FromCollection, WithHeadings
{
//    protected $list_customer;
//
//    public function __construct(array $list_customer)
//    {
//        $this->list_customer = $list_customer;
//    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection([
            [1, 2, 3],
            [4, 5, 6]
        ]);
    }

    public function headings() :array{
        return ['ID', 'Store_ID', 'First_Name', 'Last_Name', 'Email', 'Phone', 'Country', 'Orders_count', 'Total_Spent', 'Created_At', 'Updated_At'];
    }
}
