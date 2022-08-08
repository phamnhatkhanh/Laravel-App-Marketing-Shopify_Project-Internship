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
        return $this->customerRepository->syncCutomerFromShopify();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       return $this->customerRepository->index();

    }

    public function searchFilterCustomer(Request $request)
    {
        return $this->customerRepository->searchFilterCustomer($request);
        
    }
}
