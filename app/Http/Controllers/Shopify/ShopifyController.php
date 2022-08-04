<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JwtAuthController;
use App\Models\Customer;
use App\Models\Shopify;
use App\Models\Store;
use App\Repositories\Eloquents\CustomerWebhookRepository;
use App\Repositories\Webhooks\RegisterCustomerWebhookService;
use App\Repositories\Webhooks\WebhookService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ShopifyController extends Controller
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

            $redirect_uri = 'http://localhost:8080/login';

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
        if($hmac != $calculatedHmac) {
            return response([
                "status" => false
            ], 401);
        }
        return true;
    }

    //Get access_token and Login Shop
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
            // "id" => $getDataLogin['shop']->id,
            "password" => bcrypt($password),
        );
        Session::put('password', $storeData);

        // Lưu thông tin Shopify vào DB
        if (!Store::find($getDataLogin['shop']->id)) {
            $this->saveDataLogin($getDataLogin, $access_token);
        }

        //Lưu thông tin khách hàng ở Shopify lấy về từ SaveDataWebhookService vào DB
        $createCustomer = $this->createDataCustomer($shopName, $access_token);

        foreach ($createCustomer['customers'] as $item) {

            if (!Customer::find($item->id)) {
                $this->saveDataCustomer($createCustomer);
            }
        }

        //Đăng kí CustomerWebhooks thêm, xóa, sửa
        WebhookController::registerCustomerWebhookService($shopName, $access_token);

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

    //Lấy thông tin đăng nhập
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

    //Lấy thông tin khách hàng từ Shopify về
    public function createDataCustomer($shop, $access_token)
    {
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/customers.json';
        $resProduct = $client->request('get', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
                'Content-Type' => 'application/json',
            ]
        ]);
        $getDataCustomer = (array)json_decode($resProduct->getBody());

        return $getDataCustomer;
    }

    //Lưu thông tin Shopify
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
            'province' => 'New York',
            'city' => $saveData->city,
            'zip' => $saveData->zip,
            'country_name' => $saveData->country_name,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];
        Store::create($dataPost);

        return $dataPost;
    }

    //Lưu khách hàng vào DB
    public function saveDataCustomer($getCustomer)
    {
        $saveCustomers = $getCustomer['customers'];
        info($saveCustomers);
        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');

        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');

        // $store_id = Session::get('id');

        // $store = Store::where('myshopify_domain', $myshopify_domain)->first();

        foreach ($saveCustomers as $customer) {
            $created_at = str_replace($findCreateAT, $replaceCreateAT, $customer->created_at);
            $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $customer->updated_at);

            Customer::create([
                'id' => $customer->id,
                'store_id' => "65147142383",
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


    // private function generatePasswordFromEmail($email)
    // {
    //     $parsedEmail = explode("@", $email);
    //     if (count($parsedEmail) > 1) {
    //         return $parsedEmail[0];
    //     }
    //     retu
}
