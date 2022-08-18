<?php


namespace App\Repositories\Shopify;


use App\Jobs\createDataCustomer;
use App\Jobs\createDataStore;
use App\Services\Shopify\ShopifyService;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\This;
use Throwable;

use GuzzleHttp\Client;
use App\Http\Controllers\LoginController;
use App\Repositories\Contracts\ShopifyRepositoryInterface;
use App\Models\Customer;
use App\Models\Store;
use App\Events\Database\CreatedModel;
use App\Events\Database\UpdatedModel;
use App\Events\Database\DeletedModel;
use App\Events\SyncDatabase;
use App\Events\SynchronizedCustomer;
use App\Jobs\SyncCumtomer;

class ShopifyRepository implements ShopifyRepositoryInterface
{
    protected $customer;
    protected $store;

    public function __construct()
    {
        $this->customer = getConnectDatabaseActived(new Customer());
        $this->store = getConnectDatabaseActived(new Store());

    }

    public function login(Request $request)
    {

        if (isset($request["hmac"])) {
            info("have hash mac ");
            if ($this->verifyHmacAppInstall($request)) {

                $shop = $this->store->where("myshopify_domain", $request->shop)->first();

                if (empty($shop)) {
                    info("get acces token ");
                    $this->authen($request);
                }

                $LoginController = new LoginController;
                return $LoginController->login($request);
            }
        } else {
            info("NO hmac Login");
            $apiKey = config('shopify.shopify_api_key');
            // $apiKey = config('shopify.shopify_api_key');
            $scope = 'read_customers,write_customers';
            $shop = $request->myshopify_domain;

//            $redirect_uri = 'http://localhost:8000/api/auth/authen';
            $redirect_uri = 'http://192.168.101.83:8080/login';

            $url = 'https://' . $shop . '/admin/oauth/authorize?client_id=' . $apiKey . '&scope=' . $scope . '&redirect_uri=' . $redirect_uri;
            info($url);
            return $url;
        }
    }

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

    public function authen(Request $request)
    {
        $code = $request->code;
        info("in functino authen " . $code);
        $shopName = $request->shop;

        //Lấy Access_token gọi về từ WebhookService
        $getAccess_token = $this->getAccessToken($code, $shopName);
        $access_token = $getAccess_token->access_token;

        $store_id = $this->createDataStore($shopName, $access_token);

        //Lưu thông tin khách hàng ở Shopify vào DB
        info("save customer");
        $this->createDataCustomer($shopName, $access_token, $store_id);


        $getWebhook = $this->getTopicWebhook($shopName, $access_token);

        //Đăng kí CustomerWebhooks thêm, xóa, sửa
        $this->registerCustomerWebhookService($shopName, $access_token, $getWebhook);
        info("registerCustomerWebhookService");
    }

    public function getAccessToken(string $code, string $domain)
    {
        return ShopifyService::getAccessToken($code, $domain);
    }

    public function getTopicWebhook($shop, $access_token)
    {
        return ShopifyService::getTopicWebhook($shop, $access_token);
    }

    public static function registerCustomerWebhookService($shop, $access_token, $getWebhook)
    {
        return ShopifyService::registerCustomerWebhookService($shop, $access_token, $getWebhook);
    }

    public function createDataStore($shop, $access_token)
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
                'X-Shopify-Access-Token' => $access_token,
            ],
            'query' => $params
        ]);
        $responseStore = (array)json_decode($request->getBody(), true);

        $store = !empty($responseStore) ? $responseStore : [];

        dispatch(new createDataStore($store, $access_token));

        $getData = $store['shop'];

        return $getData['id'];
    }

    public function countDataCustomer($shop, $access_token)
    {
        return ShopifyService::countDataCustomer($shop, $access_token);
    }

    public function createDataCustomer($shop, $access_token, $store_id)
    {
        $limit = 250;
        $countCustomer = $this->countDataCustomer($shop, $access_token);
        $ceilRequest = (int)ceil($countCustomer['count'] / $limit);
        $numberRequest = $countCustomer > $limit ? $ceilRequest : 1;
        $log = [];
        $params = [
            'fields' => 'id, first_name, last_name, email, phone, addresses, orders_count, total_spent, created_at, updated_at',
            'limit' => $limit,
        ];
        for ($i = 0; $i < $numberRequest; $i++) {
            $client = new Client();
            $url = 'https://' . $shop . '/admin/api/2022-07/customers.json';
            $request = $client->request('get', $url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $access_token,
                    'Content-Type' => 'application/json',
                ],
                'query' => $params
            ]);

            $headers = $request->getHeaders();
            $params = $this->setParam($headers, $params);

            $responseCustomer = json_decode($request->getBody(), true);
            $customers = !empty($responseCustomer['customers']) ? $responseCustomer['customers'] : [];

            data_set($customers, '*.store_id', $store_id);

            info("Shopify: save customers");
            dispatch(new createDataCustomer($customers, $store_id));
        }

        return $log;
    }

    public function syncCustomer($shop, $access_token, $store)
    {
        // get store.
        try {
            $store_id = $store->id;
            $store->customers()->delete();

            $batch = Bus::batch([])
                ->then(function (Batch $batch) {

                })->finally(function (Batch $batch) {

                    event(new SynchronizedCustomer($batch->id));
                })->onQueue('jobs')->dispatch();
            $batch_id = $batch->id;

            $limit = 10;
            $countCustomer = $this->countDataCustomer($shop, $access_token);
            $ceilRequest = (int)ceil($countCustomer['count'] / $limit);
            $numberRequest = $countCustomer > $limit ? $ceilRequest : 1;
            $log = [];
            $params = [
                'fields' => 'id, first_name, last_name, email, phone, orders_count, total_spent, addresses, created_at, updated_at',
                'limit' => $limit,
            ];
            for ($i = 0; $i < $numberRequest; $i++) {
                $client = new Client();
                $url = 'https://' . $shop . '/admin/api/2022-07/customers.json';
                $request = $client->request('get', $url, [
                    'headers' => [
                        'X-Shopify-Access-Token' => $access_token,
                        'Content-Type' => 'application/json',
                    ],
                    'query' => $params
                ]);

                $headers = $request->getHeaders();
                $params = $this->setParam($headers, $params);

                $responseCustomer = (array)json_decode($request->getBody(), true);
                $customers = !empty($responseCustomer['customers']) ? $responseCustomer['customers'] : [];

                $batch->add(new SyncCumtomer($batch_id, $store_id, $customers));
            }

            info("syncCustomer done sycn customer");
        } catch (Throwable $e) {
            info($e);
            // report($e);
        }
        return "successfully sycn customers";
    }

    public function setParam(array $headers, $params)
    {
        return ShopifyService::setParam($headers, $params);
    }

    public function getStore()
    {
        return $this->store->get();
    }

    public function store($request)
    {
        $request['id'] = $this->store->max('id') + 1;
        $request['created_at'] = Carbon::now()->format('Y-m-d H:i:s');;
        $request['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');;
        $connect = ($this->store->getConnection()->getName());
        event(new CreatedModel($connect, $request->all(), $this->store->getModel()->getTable()));
        return "add successfully store";
    }

    public function update($request, $store_id)
    {
        $store = $this->store->where('id', $store_id)->first();
        if (!empty($store)) {

            $store->update($request->all());
            $connect = ($this->store->getConnection()->getName());

            event(new UpdatedModel($connect, $store));
        }

        return $store;
    }

}
