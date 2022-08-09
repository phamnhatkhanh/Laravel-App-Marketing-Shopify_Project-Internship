<?php

namespace App\Http\Controllers\Client;

use App\Exports\SelectedCustomerExport;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmail;
use App\Jobs\SendEmailSelectedCustomer;
use App\Repositories\Eloquents\CustomerRepository;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use App\Models\Customer;
use App\Jobs\SyncCumtomer;
use App\Events\SynchronizedCustomer;
use App\Exports\CustomerExport;
use Maatwebsite\Excel\Facades\Excel;

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

         return response()->json([
            'total_customers' => Customer::count(),
            'data' => Customer::simplePaginate(15),
            'status' => true
        ]);


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
    public function exportIDCustomerCSV(Request $request){
        $locationExport = 'backup/customers/';
        $dateExport = date('d-m-Y_H-i-s');
        $fileName = $locationExport.'customer'.$dateExport.'.csv';
        Excel::store(new SelectedCustomerExport(), $fileName);

        $this->dispatch(new SendEmail($fileName));

        return response([
            'message' => 'Export CSV Done',
            'status' => 204,
        ], 204);
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
