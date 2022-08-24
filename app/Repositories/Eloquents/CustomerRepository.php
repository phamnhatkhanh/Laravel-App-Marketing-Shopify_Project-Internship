<?php


namespace App\Repositories\Eloquents;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Bus\Batch;

use App\Jobs\SendEmail;
use App\Models\Customer;
use App\Models\Store;
use App\Events\Database\CreatedModel;
use App\Events\Database\UpdatedModel;
use App\Events\Database\DeletedModel;
use App\Events\SynchronizedCustomer;
use App\Services\Customers\CustomerService;
use App\Repositories\Shopify\ShopifyRepository;
use App\Repositories\Contracts\CustomerRepositoryInterface;
// use Illuminate\Support\Facades\Auth;
// use Tymon\JWTAuth\Exceptions\JWTException;
// use Tymon\JWTAuth\Exceptions\TokenExpiredException;
// use Tymon\JWTAuth\Exceptions\TokenInvalidException;
// use Tymon\JWTAuth\Facades\JWTAuth;

class CustomerRepository implements CustomerRepositoryInterface
{
    protected $customer;
    protected $store;

    public function __construct()
    {
        $this->store = setConnectDatabaseActived(new Store());
        $this->customer = setConnectDatabaseActived(new Customer());
    }

    /**
     * Get Store Information
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function syncCutomerFromShopify(Request $request)
    {

        // $storeID = "65147142383";
        $storeID = getStoreID();

        $store = $this->store->where('id',  $storeID)->first();


        $shopifyRepository = new ShopifyRepository();


        $shopifyRepository->syncCustomer($store->myshopify_domain, $store->access_token, $store);

        return response([
            "status" => true,
            "message" => "Start sync customer"
        ], 200);
    }

    /**
     * Search Customer by Store
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $storeID = getStoreID();

        $store = $this->store->where('id', $storeID)->first();

        if (isset($store)) {
            $totalPage = 0;
            if ($request->has('list_customer')) {
                $arr = explode(',', $request['list_customer']);

                if (count($arr) > 0) {
                    $users = $this->customer
                        ->where("store_id", $store->id)
                        ->whereIn('id', $arr)
                        ->simplePaginate(3);
                }
            } elseif ($request->has('except_customer')) {
                $arr = explode(',', $request['except_customer']);

                if (count($arr) > 0) {
                    $users = $this->customer
                        ->where("store_id", $store->id)
                        ->whereNotIn('id', $arr)
                        ->simplePaginate(3);
                }
            } else {
                $params = $request->except('_token');

                $users = $this->customer
                    ->where("store_id", $store->id)
                    ->searchcustomer($params)
                    ->order($params)
                    ->totalspent($params)
                    ->sort($params)
                    ->date($params)
                    ->simplePaginate(15);


                $totalSearchCustomer = $this->customer
                    ->where("store_id", $store->id)
                    ->searchcustomer($params)->count();
                    info("---total_customers store in condition ".$totalSearchCustomer);
                $totalPage = (int)ceil($totalSearchCustomer / 15);

            }

            $total = $this->customer->where("store_id", $store->id)->count();
            info("---total_customers store ".$total);
            return response([
                "total_customers" => $total,
                "totalPage" => $totalPage ? $totalPage : 0,
                "data" => $users,
                "status" => true
            ], 200);
        }

        return response([
            "status" => "Not found",
        ], 404);
    }

    /**
     * Open File and Add attributes, value
     *
     * @return void
     */
    public function exportCustomer($fileName, $users)
    {
        CustomerService::exportCustomer($fileName, $users);
    }

    /**
     * Receive request from FontEnd send all Customer or select Customer and Create File to send Mail
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportCustomerCSV(Request $request)
    {
        $storeID = getStoreID();

        info("Customer hash token: " . $storeID);

        $locationExport = storage_path('app/backup/customers/');
        $dateExport = date('d-m-Y_H-i-s');

        $fileName = $locationExport . 'customer_'.$storeID.'_' . $dateExport . '.csv';
        if (!empty($request->list_customer || !empty($request->except_customer))) {
            if ($request->has('list_customer')) {
                $listCustomers = $request->list_customer;
                $users = $this->customer->whereIn('id', $listCustomers)->get();
            } elseif ($request->has('except_customer')) {
                $except_customer = $request->except_customer;
                $limit = $request->limit;
                $users = $this->customer->whereNotIn('id', $except_customer)
                    ->take($limit)
                    ->get();
            } else {
                $users = $this->customer->simplePaginate(15);
            }

            $this->exportCustomer($fileName, $users);

            $store = $this->store->where('id', $storeID)->first();
            dispatch(new SendEmail($fileName, $store));
        } else {
            $store = $this->store->where('id', $storeID)->first();
            $users = $store->customers;

            $this->exportCustomer($fileName, $users);
            dispatch(new SendEmail($fileName, $store));
        }

        return response()->json([
            'message' => 'Export CSV Done',
            'status' => true,
        ], 200);
    }


    public function store($request)
    {
        $request['id'] = $this->customer->max('id') + 1;
        // dd($request['id'] );
        $request['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $request['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');

        Schema::connection($this->customer->getConnection()->getName())->disableForeignKeyConstraints();
            $customer = $this->customer->create($request->all());
        Schema::connection($this->customer->getConnection()->getName())->enableForeignKeyConstraints();

        $customer = $this->customer->where('id', $request['id'])->first();
        $connect = ($this->customer->getConnection()->getName());
        event(new CreatedModel($connect, $customer));

        return  $customer;
    }

    /**
     *
     * @return resource
     */
    public function update($request, $customerID)
    {
        $customer = $this->customer->where('id', $customerID)->first();
        if (!empty($customer)) {
            $customer->update($request->all());
            $connect = ($this->customer->getConnection()->getName());
            event(new UpdatedModel($connect, $customer));
        }

        return $customer;
    }


    public function destroy($customerID)
    {
        // dd("dleete function ".$customerID);
        $customer = $this->customer->where('id', $customerID)->first();
        if (!empty($customer)) {
            // $customer->delete();
            $connect = ($this->customer->getConnection()->getName());
            event(new DeletedModel($connect, $customer));
            return $customer;
        }
    }
}
