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

    /**
     * Get Store Information
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function syncCutomerFromShopify(Request $request)
    {
        return $this->customerRepository->syncCutomerFromShopify($request);
    }

    /**
     * Search Customer by Store
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       return $this->customerRepository->index($request);
    }

    /**
     * Receive request from FontEnd send all Customer or select Customer and Create File to send Mail
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportCustomerCSV(Request $request){
        return $this->customerRepository->exportCustomerCSV($request);

    }

    /**
     * Get All Customer display the interface
     *
     * @return resource
     */
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

