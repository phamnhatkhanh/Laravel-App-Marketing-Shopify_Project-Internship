<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Shopify;

use App\Repositories\Contracts\ShopifyRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\Http\Controllers\JwtAuthController;
use App\Models\Customer;
use App\Models\Store;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class ShopifyRepository implements ShopifyRepositoryInterface
{
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

        //Lấy Access_token gọi về từ WebhookService
        $getAccess_token = $this->getAccessToken($code, $shopName);
        $access_token = $getAccess_token->access_token;

        //Lấy thông tin đăng nhập
        $getDataLogin = $this->getDataLogin($shopName, $access_token);

        $password = $getDataLogin['shop']->myshopify_domain;

        if ($password == "") {
            return false;
        }

        $storeData = array(
            "password" => bcrypt($password),
        );
        Session::put('password', $storeData);

        // Lưu thông tin Shopify vào DB
        if (!Store::find($getDataLogin['shop']->id)) {
            $this->saveDataLogin($getDataLogin, $access_token);
        }

        Session::put('id', $getDataLogin['shop']->id);

        //Lưu thông tin khách hàng ở Shopify lấy về từ SaveDataWebhookService vào DB
        $this->createDataCustomer($shopName, $access_token);

        //Đăng kí CustomerWebhooks thêm, xóa, sửa
        $this->registerCustomerWebhookService($shopName, $access_token);

        $request['myshopify_domain'] = $shopName;
        $JwtAuthController = new JwtAuthController;
        $result = $JwtAuthController->login($request);

        return  response([
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
        $url = 'https://' . $shop . '/admin/api/2022-07/shop.json?';
        $client = new Client();
        $dataAuthen = $client->request('GET', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
            ]
        ]);
        $getDataStore = (array)json_decode($dataAuthen->getBody());

        return $getDataStore;
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
            'fields' => 'id,first_name, last_name, email, phone, country, orders_count, total_spent',
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
            $storeID = Session::get('id');
            data_set($customers, '*.store_id', $storeID);

            Customer::insert($customers);
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

    public function saveDataLogin($res, $access_token)
    {
        $saveData = $res['shop'];

        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');
        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');

        $created_at = str_replace($findCreateAT, $replaceCreateAT, $saveData->created_at);
        $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $saveData->updated_at);

        $password = Session::get('password');

        $dataPost = [
            'id' => $saveData->id,
            'name_merchant' => $saveData->name,
            'email' => $saveData->email,
            'password' => $password["password"],
            'phone' => $saveData->phone,
            'myshopify_domain' => $saveData->domain,
            'domain' => $saveData->domain,
            'access_token' => $access_token,
            'address' => $saveData->address1,
            'province' => $saveData->province,
            'city' => $saveData->city,
            'zip' => $saveData->zip,
            'country_name' => $saveData->country_name,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];
        Store::create($dataPost);

        return $dataPost;
    }

    public function saveDataCustomer($getCustomer)
    {
        $saveCustomers = $getCustomer['customers'];

        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');

        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');

        $store_id = Session::get('id');

        foreach ($saveCustomers as $customer) {
            $created_at = str_replace($findCreateAT, $replaceCreateAT, $customer->created_at);
            $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $customer->updated_at);

            Customer::create([
                'id' => $customer->id,
                'store_id' => $store_id,
                'email' => $customer->email,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'orders_count' => $customer->orders_count,
                'total_spent' => $customer->total_spent,
                'phone' => $customer->phone,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ]);
        }
    }
}
