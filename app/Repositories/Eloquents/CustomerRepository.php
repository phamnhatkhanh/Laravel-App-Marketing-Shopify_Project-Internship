<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Eloquents;

use App\Jobs\SendEmailSelectedCustomer;

use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Carbon\Carbon;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Exports\CustomerExport;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;

use App\Models\Customer;
use App\Models\Store;

use App\Jobs\SyncCumtomer;
use App\Jobs\SendEmail;

use App\Events\Database\CreatedModel;
use App\Events\Database\UpdatedModel;
use App\Events\Database\DeletedModel;
use App\Events\SyncDatabase;
use App\Events\SynchronizedCustomer;

class CustomerRepository implements CustomerRepositoryInterface
{

    protected $customer;
    protected $store;

    public function __construct()
    {
        $this->customer = getConnectDatabaseActived(new Customer());
        $this->store = getConnectDatabaseActived(new Store());
    }

    public function syncCutomerFromShopify()
    {
        //get data from shopify -> chunk add job.
        $customers = $this->customer->all();

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

        return $this->customer->simplePaginate(15);
    }

    public function index(Request $request)
    {
        if ($request->has('list_customer')) {
            $arr = explode(',', $request['list_customer']);

            if (count($arr) > 0) {
                $users = Customer::whereIn('id', $arr)
                    ->simplePaginate(3);
            }
        } elseif ($request->has('except_customer')) {
            $arr = explode(',', $request['except_customer']);
            if (count($arr) > 0) {
                $users = Customer::whereNotIn('id', $arr)
                    // ->get();
                    ->simplePaginate(3);
            }
        } else {
            $params = $request->except('_token');

            $users = $this->customer->searchcustomer($params)
                ->order($params)
                ->totalspent($params)
                ->sort($params)
                ->date($params)
                ->simplePaginate(15);

            $total =  $this->customer->searchcustomer($params)->count();
            $totalpage = (int)round($total / 15);
        }
        $total = Customer::count();
        $totalpage = (int)round($total / 15);

        return response([
            "total_customers" => $total,
            "totalPage" => $totalpage,
            "data" => $users,
            "status" => "success"
        ], 200);
    }

    public function searchFilterCustomer(Request $request)
    {
        $params = $request->except('_token');

        $result = $this->customer->searchcustomer($params)
            ->order($params)
            ->totalspent($params)
            ->sort($params)
            ->date($params)
            ->simplePaginate(15);

        $total =  $this->customer->searchcustomer($params)->count();
        $totalpage = (int)round($total / 15);
        return response([
            'data' => $result,
            "totalPage" => $totalpage,
            'status' => true,
        ], 200);
    }

    public function exportCustomerCSV()
    {
        $locationExport = 'backup/customers/';
        $dateExport = date('d-m-Y_H-i-s');

        $fileName = $locationExport . 'customer_' . $dateExport . '.csv';

        $store = $this->store->latest()->first();
        Excel::store(new CustomerExport(), $fileName);

        dispatch(new SendEmail($fileName, $store));

        return response([
            'message' => 'Export CSV Done',
            'status' => 204,
        ], 204);
    }

    public function exportSelectCustomerCSV(Request $request)
    {
        $list_customers = $request->list_customer;
        $except_customer = $request->except_customer;
        $limit = $request->limit;

        if ($request->has('list_customer')) {
            $users = $this->customer->whereIn('id', $list_customers)->get();
        } elseif ($request->has('except_customer')) {
            $users = $this->customer->whereNotIn('id',  $except_customer)
                ->take($limit)
                ->get();
        } else {
            $users = $this->customer->simplePaginate(15);
        }

        $locationExport = storage_path('app/backup/customers/');
        $dateExport = date('d-m-Y_H-i-s');
        $fileName = $locationExport . 'customer_' . $dateExport . '.csv';

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

        $store = $this->store->latest()->first();
        dispatch(new SendEmailSelectedCustomer($fileName, $store));

        return response([
            'message' => 'Export Selected Customers CSV Done',
            'status' => 204,
        ], 204);
    }

    public function getCustomer()
    {

        return $this->customer->get();
    }


    public function store($request)
    {
        $request['id'] = $this->customer->max('id') + 1;
        $request['created_at'] = Carbon::now()->format('Y-m-d H:i:s');;
        $request['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');;

        $this->customer->create($request->all());
        $customer = $this->customer->where('id', $request['id'])->first();
        $connect = ($this->customer->getConnection()->getName());
        event(new CreatedModel($connect, $customer));
        return $customer;
    }


    public function update($request, $customer_id)
    {

        // dd($this->customer->getConnection()->getName());
        // dd("update function ".$customer_id);

        // info("Repostty: inside update");

        $this->customer->where('id', $customer_id)->update($request->all());
        $customer  = ($this->customer->where('id', $customer_id)->first());
        $connect = ($this->customer->getConnection()->getName());
        // dd($connect);
        event(new UpdatedModel($connect, $customer));
        // info("pass connect");

        // $this->customer;
        return $customer;
    }


    public function destroy($customer_id)
    {

        // dd("dleete function ".$customer_id);
        $customer = $this->customer->where('id', $customer_id)->first();
        if (!empty($customer)) {
            $customer->delete();
            $connect = ($this->customer->getConnection()->getName());
            event(new DeletedModel($connect, $customer));
            return $customer;
        }
    }
}
