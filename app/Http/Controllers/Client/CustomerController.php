<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquents\CustomerRepository;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;

use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use App\Models\Customer;
<<<<<<< HEAD
use App\Jobs\SyncCumtomer;
use App\Events\SynchronizedCustomer;
=======
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Claims\Custom;
>>>>>>> 63dd166df9a3d4298aa3036daa2dc9661568b46b

class CustomerController extends Controller
{
    protected $customerRepository;
    protected $customer;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function syncCutomerFromShopify(){
        //get data from shopify -> chunk add job.
        $customers = Customer::all();

        $batch = Bus::batch([])
        ->then(function (Batch $batch) {
            event(new SynchronizedCustomer($batch->id));
        })->dispatch();
        $batch_id = $batch->id;

        $chunksCustomer = $customers->chunk(5);
        foreach ($chunksCustomer as  $chunkCumtomer) {
            $batch->add(new SyncCumtomer($batch_id,$chunkCumtomer));
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

    public function searchCustomer(Request $request)
    {
        $search = Customer::query()
            ->firstName($request)
            ->lastName($request)
            ->email($request)
            ->phone($request)
            ->get();

        return response([
            'data' => $search,
            'status' => true,
        ], 200);
    }

    public function createDate(Request $request)
    {
        $createdDate = Customer::query()
            ->createAt($request)
            ->get();

        return response([
            'data' => $createdDate,
            'status' => true,
        ], 201);
    }

    public function totalSpent(Request $request)
    {
        $totalSpent = Customer::query()
            ->totalspent($request)
            ->get();
        return response([
            'data' => $totalSpent,
            'status' => true,
        ], 201);
    }

    public function totalOrder(Request $request)
    {
        $totalOrder = Customer::query()
            ->totalOrder($request)
            ->get();

        return response([
            'data' => $totalOrder,
            'status' => true,
        ], 201);
    }

    public function sortCustomer(Request $request)
    {
        $sortCreated_at = Customer::query()
            ->Sort($request)
            ->get();

        return response([
            'data' => $sortCreated_at,
            'status' => true,
        ], 201);
    }
}
