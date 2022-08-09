<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SelectedCustomerExport implements FromCollection, WithHeadings
{
    protected $customers;

    public function __construct(array $customers)
    {
        $this->customers = $customers;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $customers = $this->customers;
        return Customer::where('id', $customers)->all();
    }

    public function headings() :array{
        return ['ID', 'Store_ID', 'First_Name', 'Last_Name', 'Email', 'Phone', 'Country', 'Orders_count', 'Total_Spent', 'Created_At', 'Updated_At'];
    }
}
