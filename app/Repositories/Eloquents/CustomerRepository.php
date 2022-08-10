<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Eloquents;

use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;

use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Exports\CustomerExport;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;

use App\Models\Customer;
use App\Models\Store;

use App\Jobs\SyncCumtomer;
use App\Jobs\SendEmail;

use App\Events\SynchronizedCustomer;

class CustomerRepository implements CustomerRepositoryInterface
{

    protected $customer;
    public function __construct(){
        $this->customer = new Customer();

    }
    public function syncCutomerFromShopify()
    {
        //get data from shopify -> chunk add job.
        $customers = Customer::all();

        $batch = Bus::batch([])
            ->then(function (Batch $batch) {

            })->finally(function (Batch $batch) {
                event(new SynchronizedCustomer($batch->id));
            })->onQueue('jobs')->dispatch();
        $batch_id = $batch->id;

        $chunksCustomer = $customers->chunk(5);
        foreach ($chunksCustomer as  $chunkCumtomer) {
            $batch->add(new SyncCumtomer($batch_id, $chunkCumtomer));
        }

        return Customer::simplePaginate(15);
    }

    public function index(Request $request)
    {
        if ($request->has('list_customer')) {
            $arr = explode(',', $request['list_customer']);
            if(count($arr) > 0){
                $users = Customer::whereIn('id', $arr)   
                ->simplePaginate(15);
            }

        } elseif($request->has('except_customer')){
            $arr = explode(',', $request['except_customer']);
            if(count($arr) > 0){
                $users = Customer::whereNotIn('id', $arr)
                // ->get();
                ->simplePaginate(3);
            }
           
        }else{
            $users = Customer::simplePaginate(15);
        }
     
        return response([
            "total_customers" => Customer::count(),
            "data" => $users,
            "status" => "success"
        ],200);
    }

    public function searchFilterCustomer(Request $request)
    {
        $params = $request->except('_token');

        $result = $this->customer->searchcustomer($params)
        ->order($params)
        ->totalspant($params)
        ->sort($params)
        ->date($params)
        ->simplePaginate(15);

        return response([
            'data' => $result,
            'status' => true,
        ], 200);
    }

    public function exportCustomerCSV()
    {
        $locationExport = 'backup/customers/';
        $dateExport = date('d-m-Y_H-i-s');
        $fileName = $locationExport . 'customer' . $dateExport . '.csv';
        $store = Store::latest()->first();
        $fileExport = Excel::store(new CustomerExport(), $fileName);

        $sendEmailExport = $this->dispatch(new SendEmail($fileName, $store));

        return response([
            'message' => 'Export CSV Done',
            'status' => 204,
        ], 204);
    }
}
