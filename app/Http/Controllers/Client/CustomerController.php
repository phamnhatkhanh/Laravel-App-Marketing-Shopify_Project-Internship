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
    public function index(Request $request)
    {
       return $this->customerRepository->index($request);
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

     public function getCustomer()
    {
        return $this->customerRepository->getCustomer();
    }

    public function update(Request $request, $customer_id)
    {

        $customer = $this->customerRepository->update($request, $customer_id);

        return response([
            'data' => $customer
        ],201);
    }

    public function store(Request $request)
    {
        // dd("store prodcut");
        $customer = $this->customerRepository->store($request);
        return response([
            'data' => $customer
            // 'data' => new customerResource($customer)
        ],201);
    }

    public function destroy($customer_id)
    {

        $customer = $this->customerRepository->destroy( $customer_id);
        return response([
            'data' => $customer,
            'mess' => "dleete customer done"
        ],201);

    }

    public function show($id)
    {

    }

}

// /api/customer

// ?list_customer=[3,6,8]
// ?except_customer=[2,6,3]&&get_quantify_customer = 3
