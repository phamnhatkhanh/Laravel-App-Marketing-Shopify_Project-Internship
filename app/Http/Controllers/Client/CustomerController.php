<?php

namespace App\Http\Controllers\Client;


use App\Http\Controllers\Controller;
use App\Repositories\Eloquents\CustomerRepository;
use Illuminate\Http\Request;

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

    /**
     *
     *
     */
    public function exportCustomerCSV(){
        return $this->customerRepository->exportCustomerCSV();
    }

    /**
     *
     *
     */
    public function exportSelectCustomerCSV(Request $request){
        return $this->customerRepository->exportSelectCustomerCSV($request);
    }

    public function searchFilterCustomer(Request $request)
    {
        return $this->customerRepository->searchFilterCustomer($request);
    }
}
