<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Eloquents;

use App\Exports\CustomerExport;
use App\Jobs\SendEmail;
use App\Models\Store;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function exportCustomerCSV(){
        $locationExport = 'backup/customers/';
        $dateExport = date('d-m-Y_H-i-s');
        $fileName = $locationExport.'customer'.$dateExport.'.csv';
        $store = Store::latest()->first();
        $fileExport = Excel::store(new CustomerExport(), $fileName);

        $sendEmailExport = $this->dispatch(new SendEmail($fileName, $store));

        return response([
            'message' => 'Export CSV Done',
            'status' => 204,
        ], 204);
    }
}
