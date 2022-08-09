<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Shopify;

use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Controllers\JwtAuthController;

use Throwable;

use App\Repositories\Contracts\ShopifyRepositoryInterface;

use App\Models\Customer;
use App\Models\Store;

class ShopifyRepository implements ShopifyRepositoryInterface
{
    protected $customer;
    protected $store;

    public function __construct(){
        $this->customer = new Customer();
        $this->store = new Store();

    }

    public function login(Request $request)
    {
        if ($request->header("HTTP_X_SHOPIFY_HMAC_SHA256")) {
            if ($this->verifyHmacAppInstall($request)) {
                $JwtAuthController = new JwtAuthController;
                return $JwtAuthController->login($request);
            }
        } else {
            $apiKey = config('shopify.shopify_api_key');
            $scope = 'read_customers,write_customers';
            $shop = $request->myshopify_domain;
            $redirect_uri = 'http://192.168.101.83:8080/login';

            $url = 'https://' . $shop . '/admin/oauth/authorize?client_id=' . $apiKey . '&scope=' . $scope . '&redirect_uri=' . $redirect_uri;

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
        $shopName = $request->shop;

        //Lấy Access_token
        $getAccess_token = $this->getAccessToken($code, $shopName);
        $access_token = $getAccess_token->access_token;

        //Lưu thông tin shop
        $getDataLogin = $this->getDataLogin($shopName, $access_token);

        //Lưu thông tin khách hàng ở Shopify vào DB
        $this->createDataCustomer($shopName, $access_token);

        //Đăng kí CustomerWebhooks thêm, xóa, sửa
        $this->registerCustomerWebhookService($shopName, $access_token);

        $request['myshopify_domain'] = $shopName;
        $JwtAuthController = new JwtAuthController;
        $result = $JwtAuthController->login($request);

        return response([
            "access_token" => $result,
            "message" => true,
        ], 200);
    }

    public function getAccessToken(string $code, string $domain)
    {
        $client2 = new Client();
        $response = $client2->post(
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

        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');
        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');

        $created_at = str_replace($findCreateAT, $replaceCreateAT, $store['shop']['created_at']);
        $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $store['shop']['updated_at']);

        $storeData = [
            "password" => bcrypt($password),
        ];

        data_set($store, '*.password', $storeData);
        $getData = $store['shop'];
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

        if (!Store::find($data['id'])) {
            Store::insert($data);
        }

        return $log;
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

    public function createDataCustomer($shop, $access_token)
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

            $findCreateAT = array('T', '+07:00');
            $replaceCreateAT = array(' ', '');
            $findUpdateAT = array('T', '+07:00');
            $replaceUpdateAT = array(' ', '');

            $store = Store::latest()->first();
            data_set($customers, '*.store_id', $store->id);

            foreach ($customers as $customer){
                $created_at = str_replace($findCreateAT, $replaceCreateAT, $customer['created_at']);
                $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $customer['updated_at']);

                foreach ($customer['addresses'] as $item){
                    $country = $item['country'];

                    $data = [
                        'id' => $customer['id'],
                        'store_id' => $customer['store_id'],
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
                }


                if (!Customer::find($data['id'])){
                    Customer::insert($data);
                }
            }
        }

        return $log;
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

}
