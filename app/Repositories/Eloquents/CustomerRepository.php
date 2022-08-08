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
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use App\Models\Customer;
use App\Jobs\SyncCumtomer;
use App\Events\SynchronizedCustomer;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function syncCutomerFromShopify()
    {
        //get data from shopify -> chunk add job.
        $customers = Customer::all();

        $batch = Bus::batch([])
            ->then(function (Batch $batch) {
                event(new SynchronizedCustomer($batch->id));
            })->dispatch();
        $batch_id = $batch->id;

        $chunksCustomer = $customers->chunk(5);
        foreach ($chunksCustomer as  $chunkCumtomer) {
            $batch->add(new SyncCumtomer($batch_id, $chunkCumtomer));
        }

        return Customer::simplePaginate(15);
    }
    public function exportCustomerCSV(){
        $locationExport = 'backup/customers/';
        $dateExport = date('d-m-Y_H-i-s');
        $fileName = $locationExport.'customer'.$dateExport.'.csv';
        $store = Store::latest()->first();
        $fileExport = Excel::store(new CustomerExport(), $fileName);

        $sendEmailExport = $this->dispatch(new SendEmail($fileName, $store));

        return response([
            'data' => "Export successfully",
            'status' => 204,
        ], 204);
    }

    public function index()
    {
        return response()->json([
            'total_customers' => Customer::count(),
            'data' => Customer::simplePaginate(15),
            'status' => true
        ]);
    }

    public function searchFilterCustomer(Request $request)
    {
        $params = $request->except('_token');

        $result = Customer::searchcustomer($params)
        ->order($params)
        ->totalspant($params)
        ->sort($params)
        ->date($params)
        ->get();

        return response([
            'data' => $result,
            'status' => true,
        ], 200);
    }
}
