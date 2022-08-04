<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Jobs\SendEmail;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;


class ExportCSVController extends Controller
{
    public function export(){
        $customers = Customer::get();
        return view('export', compact('customers'));
    }

    public function exportFileDownload(Request $request){
        $store = Store::where('id', '59603255435')->first();

        $locationExport = 'backup/customers/';
        $dateExport = date('d-m-Y_H-i-s');
        $fileName = $locationExport.'customer'.$dateExport.'.csv';

//        $fileExport = Excel::download(new CustomerExport(), $fileName);
        $fileExport = Excel::store(new CustomerExport(), $fileName);

        $this->dispatch(new SendEmail($store ,$fileName));
        return back();
//        return response([
//            'data' => $fileExport,
//            'status' => 201,
//        ], 201);
    }

}
