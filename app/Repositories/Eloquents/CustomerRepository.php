<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Eloquents;

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
    public function __construct(){
        $this->customer = getConnectDatabaseActived(new Customer());

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


    public function index()
    {
        return response()->json([
            'total_customers' => $this->customer->count(),
            'data' => $this->customer->simplePaginate(15),
            'status' => true
        ]);
    }

    public function searchFilterCustomer(Request $request)
    {
        $params = $request->except('_token');

        $result = $this->customer->searchcustomer($params)
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

    public function getCustomer(){
        // return "sfkjsnfks";
        return $this->customer->get();
    }
    public function store($request){

        $request['id'] = $this->customer->max('id')+1;
        $request['created_at'] = Carbon::now()->format('Y-m-d H:i:s');;
        $request['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');;

        $this->customer->create($request->all());

        $customer = $this->customer->where('id', $request['id'])->first();
        //  dd( $customer->id );
        $connect = ($this->customer->getConnection()->getName());
        event(new CreatedModel($connect,$customer));
        return $customer;
    }

     public function update( $request, $customer_id){
        // dd($this->customer->getConnection()->getName());
        // dd("update function ".$customer_id);

        // info("Repostty: inside update");

        $this->customer->where('id',$customer_id)->update($request->all());
        $customer  = ($this->customer->where('id',$customer_id)->first());
        $connect = ($this->customer->getConnection()->getName());
        // dd($connect);
        event(new UpdatedModel($connect,$customer));
        // info("pass connect");

        // $this->customer;
        return $customer;
    }
    public function destroy( $customer_id){
        // dd("dleete function ".$customer_id);
        $customer = $this->customer->where('id',$customer_id)->first();
        if(!empty($customer)){
            $customer->delete();
            $connect = ($this->customer->getConnection()->getName());
            event(new DeletedModel($connect,$customer));
            return $customer;
        }
    }
}
