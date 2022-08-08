<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JwtAuthController;
use App\Models\Customer;
use App\Models\Store;
use App\Repositories\Shopify\ShopifyRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use phpDocumentor\Reflection\Types\This;

class ShopifyController extends Controller
{

    protected $productRepository;

//     protected $product;

    public function __construct(ShopifyRepository $shopifyRepository)
    {
        $this->shopifyRepository = $shopifyRepository;
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

        Session::put('id', $getDataLogin['shop']->id);

        //Lưu thông tin khách hàng ở Shopify lấy về từ SaveDataWebhookService vào DB
        $createCustomer = $this->createDataCustomer($shopName, $access_token);
//
//        foreach ($createCustomer['customers'] as $item) {
//
//            if (!Customer::find($item->id)) {
//                $this->saveDataCustomer($createCustomer);
//            }
//        }

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
       return $this->shopifyRepository->getAccessToken($code, $domain);
    }

    //Register Webhook Add, Edit, Delete, Uninstall
    public function registerCustomerWebhookService($shop, $access_token){
        $this->shopifyRepository->registerCustomerWebhookService($shop, $access_token);
    }

    //Lấy thông tin đăng nhập
    public function getDataLogin($shop, $access_token)
    {
        return $this->shopifyRepository->getDataLogin($shop, $access_token);
    }

    //Đếm số khách hàng lấy về
    public function countDataCustomer($shop, $access_token)
    {
        return $this->shopifyRepository->countDataCustomer($shop, $access_token);
    }

    //Lấy thông tin khách hàng từ Shopify về
    public function createDataCustomer($shop, $access_token)
    {
        return $this->shopifyRepository->createDataCustomer($shop, $access_token);
    }

    public function setParam(array $headers, $params)
    {
        return $this->shopifyRepository->setParam($headers, $params);
    }

    //Lưu thông tin Shopify
    public function saveDataLogin($res, $access_token)
    {
        return $this->shopifyRepository->saveDataLogin($res, $access_token);
    }
}
