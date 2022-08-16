<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Shopify;

use App\Models\Types;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Carbon\Carbon;
use Throwable;
use DB;
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
            // $redirect_uri = 'http://localhost:8000/api/auth/authen';
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
        // dd($request->all());
        //Lấy Access_token gọi về từ WebhookService
        $getAccess_token = $this->getAccessToken($code, $shopName);
        $access_token = $getAccess_token->access_token;

        //Lưu thông tin shop ở Shopify vào DB
        $store_id = $this->getDataLogin($shopName, $access_token);


        //Lưu thông tin khách hàng ở Shopify vào DB
        $this->createDataCustomer($shopName, $access_token, $store_id);
        info("save store");

        //Đăng kí CustomerWebhooks thêm, xóa, sửa
        $this->registerCustomerWebhookService($shopName, $access_token);
        info("registerCustomerWebhookService");
        return 'lalalalalallalalal';
    }

    public function getAccessToken(string $code, string $domain)
    {
        info("ShopifyRepository getAccessToken: get token");
        $client = new Client();
        $response = $client->post(
            "https://" . $domain . "/admin/oauth/access_token",
            [
                'form_params' => [
                    'client_id' => env('SHOPIFY_API_KEY'),
                    'client_secret' => env('SHOPIFY_SECRET_KEY'),
                    'code' => $code,
                ]
            ]
        );

        return json_decode($response->getBody()->getContents());
    }

    public static function registerCustomerWebhookService($shop, $access_token)
    {
        info("ShopifyRepository registerCustomerWebhookService: access persmission");
        $topic_access = [
            'customers/create',
            'customers/update',
            'customers/delete',
            'app/uninstalled',
        ];
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/webhooks.json';
        foreach ($topic_access as $topic) {
            $resShop = $client->request('post', $url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $access_token,
                ],
                'form_params' => [
                    'webhook' => [
                        'topic' => $topic,
                        'format' => 'json',
                        'address' => config('shopify.ngrok') . '/api/shopify/webhook',
                    ],
                ]
            ]);
        }
    }

    public function getDataLogin($shop, $access_token)
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

        $password = $store['shop']['myshopify_domain'];

        if ($password == "") {
            return false;
        }

        $created_at = str_replace(array('T', '+07:00'), array(' ', ''), $store['shop']['created_at']);
        $updated_at = str_replace(array('T', '+07:00'), array(' ', ''), $store['shop']['updated_at']);

        $storeData = [
            "password" => bcrypt($password),
        ];

        data_set($store, '*.password', $storeData);
        $getData = $store['shop'];
        info("ShopifyRepository save store");
        $data = [
            'id' => $getData['id'],
            'name_merchant' => $getData['name'],
            'email' => $getData['email'],
            'password' => $getData['password']['password'],
            'phone' => $getData['phone'],
            'myshopify_domain' => $getData['myshopify_domain'],
            'domain' => $getData['domain'],
            'access_token' => $access_token,
            'address' => $getData['address1'],
            'province' => $getData['province'],
            'city' => $getData['city'],
            'zip' => $getData['zip'],
            'country_name' => $getData['country_name'],
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];

        $findStore = $this->store->where('id',$data['id'])->first();
        if (empty($findStore)) {
            $this->store->create($data);
        }else{
            $findStore->access_token = $data['access_token'];
            $findStore->update($data);
        }
//        $connect = ($this->store->getConnection()->getName());
//        event(new CreatedModel($connect, $data, $this->store->getModel()->getTable()));

        return $getData['id'];
    }

    public function countDataCustomer($shop, $access_token)
    {
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/customers/count.json';
        $resCustomer = $client->request('get', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
            ]
        ]);
        $countCustomer = (array)json_decode($resCustomer->getBody());

        return $countCustomer;
    }

    public function createDataCustomer($shop, $access_token, $store_id)
    {
        $limit = 250;
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

            // $store = $this->store->latest()->first();
            data_set($customers, '*.store_id', $store_id);
            info("Shopify: save customers");
            $getCustomer = $this->customer->get();
            foreach ($customers as $customer) {
                $created_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['created_at']);
                $updated_at = str_replace(array('T', '+07:00'), array(' ', ''), $customer['updated_at']);


                foreach ($customer['addresses'] as $item) {
                    $country = $item['country'];

                    $data = [
                        'id' => $customer['id'],
                        'store_id' => $store_id,
                        'email' => $customer['email'],
                        'first_name' => $customer['first_name'],
                        'last_name' => $customer['last_name'],
                        'orders_count' => $customer['orders_count'],
                        'total_spent' => $customer['total_spent'],
                        'phone' => $customer['phone'],
                        'country' => $country,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                    ];
                    $findCustomer = $getCustomer->where('id',$data['id'])->first();

                    if (empty($findCustomer)) {
                        $this->customer->create($data);
                    } else {
                        $findCustomer->update($data);
                    }

//                    $connect = ($this->customer->getConnection()->getName());
//                    event(new CreatedModel($connect, $data, $this->customer->getModel()->getTable()));
                }
            }
        }

        return $log;
    }
    public  function syncCustomer($shop, $access_token,$store)
    {


        // get store.
        DB::beginTransaction();
        try {
            $store_id = $store->id;
            $store->customers()->delete();

            // $store->query()->delete();
            // dd(["sfjnskfs",$shop, $access_token,$store_id]);

            $batch = Bus::batch([])
                ->then(function (Batch $batch) {

                })->finally(function (Batch $batch)  {

                    event(new SynchronizedCustomer($batch->id));

                })->onQueue('jobs')->dispatch();

            $batch_id = $batch->id;

            $limit = 250;
            $countCustomer = $this->countDataCustomer($shop, $access_token);
            $ceilRequest = (int)ceil($countCustomer['count'] / $limit);
            $numberRequest = $countCustomer > $limit ? $ceilRequest : 1;
            $log = [];
            $params = [
                'fields' => 'id, first_name, last_name, email, phone, addresses, orders_count, total_spent, created_at, updated_at',
                'limit' => $limit,
            ];
            info("syncCustomer get customer from shopify");
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


                $batch->add(new SyncCumtomer($batch_id,$store_id, $customers));



            }

            info("syncCustomer done sycn customer");
            DB::commit();
        } catch (Throwable $e) {
            info($e);
            DB::rollback();
            // report($e);
        }






        return "successfully sycn customers";
    }

    public function setParam(array $headers, $params)
    {
        $links = explode(',', @$headers['Link'][0]);
        $nextPage = $prevPage = null;
        foreach ($links as $link) {
            if (strpos($link, 'rel="next"')) {
                $nextPage = $link;
            }
            if (strpos($link, 'rel="previous"')) {
                $prevPage = $link;
            }
        }

        $params = [];

        if ($nextPage) {
            preg_match('~<(.*?)>~', $nextPage, $next);
            $url_components = parse_url($next[1]);
            parse_str($url_components['query'], $parseStr);
            $params = $parseStr;
            $params['next_cursor'] = $parseStr['page_info'];
        }

        if ($prevPage) {
            preg_match('~<(.*?)>~', $prevPage, $next);
            $url_components = parse_url($next[1]);
            parse_str($url_components['query'], $parseStr);
            $params = !empty($params) ? $params : $parseStr;
            $params['prev_cursor'] = $parseStr['page_info'];
        }

        return $params;
    }

    public function getStore()
    {
        return $this->store->get();
    }

    public function store($request)
    {
        // dd();
        // dd($this->store->getConnection()->getName());
        $request['id'] = $this->store->max('id') + 1;
        $request['created_at'] = Carbon::now()->format('Y-m-d H:i:s');;
        $request['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');;

        // $store = $this->store->create($request->all())->id;

        // $this->store->create($request->all());
        // $store =$this->store->where('id', $request['id'])->first();
        $connect = ($this->store->getConnection()->getName());
        event(new CreatedModel($connect, $request->all(), $this->store->getModel()->getTable()));
        return "add successfully store";
    }

    public function update($request, $store_id)
    {
        // dd("repo: update");

        $store = $this->store->where('id', $store_id)->first();
        if (!empty($store)) {

            $store->update($request->all());
            $connect = ($this->store->getConnection()->getName());

            event(new UpdatedModel($connect, $store));
        }
        // info("pass connect");

        // $this->store;
        return $store;
    }

    public function destroy($store_id)
    {

        $store = $this->store->where('id', $store_id)->first();
        if (!empty($store)) {
            // dd("dleete function ".$store_id);
            // $store->delete();
            $connect = ($this->store->getConnection()->getName());
            event(new DeletedModel($connect, $store));
            return $store;
        }
    }

}
