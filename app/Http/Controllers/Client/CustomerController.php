<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquents\CustomerRepository;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use App\Models\Customer;
use App\Jobs\SyncCumtomer;
use App\Events\SynchronizedCustomer;

class CustomerController extends Controller
{
    protected $customerRepository;
    protected $customer;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

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
        // return $customers;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::simplePaginate(15);
        return response([
            'data' => $customers,
            'status' => true,
        ], 201);
    }

    public function searchFilterCustomer(Request $request)
    {
        $params = $request->except('_token');

        $result = Customer::filter($params)->get();

        return response([
            'data' => $result,
            'status' => true,
        ], 200);
    }
}
