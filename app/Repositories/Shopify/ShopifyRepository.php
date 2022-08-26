<?php


namespace App\Repositories\Shopify;


use Throwable;
use Carbon\Carbon;
use GuzzleHttp\Client;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Http;

use App\Models\Customer;
use App\Models\Store;
use App\Events\Database\CreatedModel;
use App\Events\Database\UpdatedModel;
use App\Events\Database\DeletedModel;
use App\Events\SynchronizedCustomer;
use App\Jobs\Shopify\SyncCumtomer;
use App\Jobs\Shopify\CreateDataStore;
use App\Jobs\Shopify\CreateDataCustomer;
use App\Services\Shopify\ShopifyService;
use App\Http\Controllers\LoginController;
use App\Repositories\Contracts\ShopifyRepositoryInterface;

class ShopifyRepository implements ShopifyRepositoryInterface
{
    protected $customer;
    protected $store;

    public function __construct()
    {
        $this->customer = setConnectDatabaseActived(new Customer());
        $this->store = setConnectDatabaseActived(new Store());
    }

    /**
     * If hmac already exists, then login into Store. If don't have hmac download the app from Shopify
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string|void
     */
    public function login(Request $request)
    {
        if (isset($request["hmac"])) {
            if ($this->verifyHmacAppInstall($request)) {

                $shop = $this->store->where("myshopify_domain", $request->shop)->first();

                if (empty($shop)) {
                    $this->authen($request);
                    $request['first_install_app'] = true;
                }

                info("1...Shopify login");
                $LoginController = new LoginController;
                return $LoginController->login($request);
            }
        } else {
            info("NO hmac Login");
            $apiKey = config('shopify.shopify_api_key');

            $scope = 'read_customers,write_customers';
            $shop = $request->myshopify_domain;

            $hostLink = $request->header("origin");

            $url = 'https://'.$shop.'/admin/api/2022-07/shop.json';

            $request = Http::get($url);

            $statusCode = $request->getStatusCode();

            if ($statusCode == 401){
                $redirect_uri = $hostLink . "/login";

                info($redirect_uri);
                $url = 'https://' . $shop . '/admin/oauth/authorize?client_id=' . $apiKey . '&scope=' . $scope . '&redirect_uri=' . $redirect_uri;
                info($url);
                return response()->json([
                    "status" => true,
                    "url" => $url
                ]);
            } else {
                return response()->json([
                    'message' => 'Có lỗi: ' . $statusCode,
                    'status' => false,
                ], $statusCode);
            }
        }
    }

    /**
     * Define since from Shopify to server after install app
     *
     * @param Request $request
     * @return true
     */
    private function verifyHmacAppInstall(Request $request)
    {
        $params = array();
        foreach ($request->toArray() as $param => $value) {
            if ($param != 'signature' && $param != 'hmac') {
                $params[$param] = "{$param}={$value}";
            }
        }
        asort($params);

        $params = implode('&', $params);
        $hmac = $request->header("HTTP_X_SHOPIFY_HMAC_SHA256");

        $calculatedHmac = hash_hmac('sha256', $params, \env('SHOPIFY_SECRET_KEY'));

        if ($hmac != $calculatedHmac) {

            return response([
                "status" => false
            ], 401);
        }

        return true;
    }

    /**
     * Receive information from login to do other things
     *
     * @param Request $request
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    public function authen(Request $request)
    {
        $code = $request->code;
        info("in functino authen " . $code);
        $shop = $request->shop;

        //Lấy accessToken gọi về từ WebhookService
        $getAccessToken = $this->getAccessToken($code, $shop);
        $accessToken = $getAccessToken->access_token;

        $storeID = $this->createDataStore($shop, $accessToken);


        // $store->customers

        // =======



        //Lưu thông tin khách hàng ở Shopify vào DB
        info("save customer");
        // $this->createDataCustomer($shop, $accessToken, $storeID);

        $getWebhook = $this->getTopicWebhook($shop, $accessToken);

        //Đăng kí CustomerWebhooks thêm, xóa, sửa
        $this->registerCustomerWebhookService($shop, $accessToken, $getWebhook);
        info("registerCustomerWebhookService");
        // return "setup store sucess";
    }

    public function checkLogin($shop, $access_token)
    {
        return ShopifyService::checkLogin($shop, $access_token);
    }
    /**
     * Get accessToken from the Shopify
     *
     * @param string $code
     * @param string $domain
     * @return resource
     */
    public function getAccessToken(string $code, string $domain)
    {
        return ShopifyService::getAccessToken($code, $domain);
    }

    /**
     * Retrieves a count of existing webhook subscriptions
     *
     * @param string $shop
     * @param string $accessToken
     * @return array
     */
    public function getTopicWebhook($shop, $accessToken)
    {
        return ShopifyService::getTopicWebhook($shop, $accessToken);
    }

    /**
     * Create a new webhook subscription
     *
     * @param string $shop
     * @param string $accessToken
     * @param $getWebhook
     * @return string
     */
    public static function registerCustomerWebhookService($shop, $accessToken, $getWebhook)
    {
        return ShopifyService::registerCustomerWebhookService($shop, $accessToken, $getWebhook);
    }

    /**
     * Get Shop Information and save it into the Database
     *
     * @param string $shop
     * @param string $accessToken
     * @return resource
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createDataStore($shop, $accessToken)
    {
        $params = [
            'fields' => 'id, name, email, password, phone, myshopify_domain, domain, access_token,
                        address1, province, city, zip, country_name, created_at, updated_at',
        ];
        $log = [];

        $url = 'https://' . $shop . '/admin/api/2022-07/shop.json?';
        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $accessToken,
            ],
            'query' => $params
        ]);

        $responseStore = (array)json_decode($request->getBody(), true);
        $store = !empty($responseStore) ? $responseStore : [];

        info("createDataStore...");
        dispatch(new CreateDataStore($store, $accessToken));

        $getData = $store['shop'];
        return $getData['id'];
    }

    /**
     * Retrieve a count of Customers
     *
     * @param string $shop
     * @param string $accessToken
     * @return array
     */
    public function countDataCustomer($shop, $accessToken)
    {
        return ShopifyService::countDataCustomer($shop, $accessToken);
    }

    /**
     * Get list Customer from Shopify and Save Customer into the Database
     *
     * @param string $shop
     * @param string $accessToken
     * @param string $storeID
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createDataCustomer($shop, $accessToken, $storeID)
    {
        $limit = 250;

        //Count number Customers
        $countCustomer = $this->countDataCustomer($shop, $accessToken);
        $ceilRequest = (int)ceil($countCustomer['count'] / $limit);

        //Calculate the number of iterations to be able to save all customers to the DB
        $numberRequest = $countCustomer > $limit ? $ceilRequest : 1;
        $log = [];
        $params = [
            'fields' => 'id, first_name, last_name, email, phone, addresses, orders_count, total_spent, created_at, updated_at',
            'limit' => $limit,
        ];
        $client = new Client();
        for ($i = 0; $i < $numberRequest; $i++) {
            $url = 'https://' . $shop . '/admin/api/2022-07/customers.json';
            $request = $client->request('get', $url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'query' => $params
            ]);

            $headers = $request->getHeaders();
            $params = $this->setParam($headers, $params);

            $responseCustomer = json_decode($request->getBody(), true);
            $customers = !empty($responseCustomer['customers']) ? $responseCustomer['customers'] : [];

            data_set($customers, '*.store_id', $storeID);

            info("Shopify: save customers");
            dispatch(new CreateDataCustomer($customers, $storeID));
        }

        return $log;
    }

    /**
     * SyncCustomer from Shopify to Database. If the Customer already exits then edit and Customer doesn't exist then save Customer in the Database
     *
     * @param string $shop
     * @param string $accessToken
     * @param string $store
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncCustomer($shop, $accessToken, $store)
    {

        info("--function: syncCustomer in shopify repository ".$store->id);
        try {
            $storeID = $store->id;

            $batch = Bus::batch([])
                ->then(function (Batch $batch) {
                })->finally(function (Batch $batch) use($storeID){
                    info ("SUCESS sync customers");
                    info("-call event: SynchronizedCustomer");
                    event(new SynchronizedCustomer($batch->id,$storeID));
                })->onQueue('jobs')->dispatch();

            $batchID = $batch->id;

            info("--2 call job batch....");
            $limit = 150;

            //Count number Customers
            $countCustomer = $this->countDataCustomer($shop, $accessToken);
            $ceilRequest = (int)ceil($countCustomer['count'] / $limit);

            //Calculate the number of iterations to be able to save all customers to the DB
            $numberRequest = $countCustomer > $limit ? $ceilRequest : 1;
            $log = [];
            $params = [
                'fields' => 'id, first_name, last_name, email, phone, orders_count, total_spent, addresses, created_at, updated_at',
                'limit' => $limit,
            ];

            $arrCustomers = [];

            for ($i = 0; $i < $numberRequest; $i++) {
                $client = new Client();
                $url = 'https://' . $shop . '/admin/api/2022-07/customers.json';
                $request = $client->request('get', $url, [
                    'headers' => [
                        'X-Shopify-Access-Token' => $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'query' => $params
                ]);

                $headers = $request->getHeaders();
                $params = $this->setParam($headers, $params);

                $responseCustomer = (array)json_decode($request->getBody(), true);
                $customers = !empty($responseCustomer['customers']) ? $responseCustomer['customers'] : [];
                $arrCustomers[] =  $customers;

            }
            if(is_null($arrCustomers)){

                $batch->add(new SyncCumtomer($batchID, $storeID, $arrCustomers));
            }else{

                foreach ($arrCustomers as $customers) {

                    $batch->add(new SyncCumtomer($batchID, $storeID, $customers));
                }

            }

            info(".......syncCustomer: done sycn customer");
        } catch (Throwable $e) {
            info($e);
        }

    }

    /**
     * If quantity Customer exceed one save will automatically press rel="next" to go through the page and continue save
     *
     * @param array $headers
     * @param $params
     * @return mixed
     */
    public function setParam(array $headers, $params)
    {
        return ShopifyService::setParam($headers, $params);
    }

    /**
     * Get Store display the interface
     *
     * @return resource
     */
    public function getStore()
    {
        return $this->store->get();
    }

    public function store($request)
    {
        $request['id'] = $this->store->max('id') + 1;
        $request['created_at'] = Carbon::now()->format('Y-m-d H:i:s');;
        $request['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');;
        $store = $this->store->create($request->all());
        // dd($store);
        $store = $this->store->where('id', $request['id'])->first();
        $connect = ($this->store->getConnection()->getName());
        event(new CreatedModel($connect, $store));
        return $store;
        // return "add successfully store";


    }

    public function update($request, $storeId)
    {
        $store = $this->store->where('id', $storeId)->first();
        if (!empty($store)) {

            $store->update($request->all());
            $connect = ($this->store->getConnection()->getName());

            event(new UpdatedModel($connect, $store));
        }

        return $store;
    }


    public function destroy($storeId)
    {

        $store = $this->store->where('id', $storeId)->first();
        if (!empty($store)) {
            $connect = ($this->store->getConnection()->getName());
            event(new DeletedModel($connect, $store));
            return $store;
        }
    }
}
