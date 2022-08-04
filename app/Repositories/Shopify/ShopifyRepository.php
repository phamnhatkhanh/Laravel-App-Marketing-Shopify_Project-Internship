<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Shopify;

use App\Repositories\Contracts\ShopifyRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\Models\Customer;
use App\Models\Store;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class ShopifyRepository implements ShopifyRepositoryInterface
{
    public function index(Request $request){
        $name = $request->get('name');
        if (!empty($name)) {

            return response([
                'data' => $name,
                'status' => 201,
            ], 201);
        } else {

            return response();
        }
    }

    public function login(Request $request){
        $apiKey = config('shopify.shopify_api_key');
        $scope = 'read_customers,write_customers';
        $shop = $request->shop;

        $redirect_uri = config('shopify.ngrok') . '/api/authen';
        $url = 'https://' . $shop . '/admin/oauth/authorize?client_id=' . $apiKey . '&scope=' . $scope . '&redirect_uri=' . $redirect_uri;

        return $url;
    }

    public function authen(Request $request){

    }

    public function getAccessToken(string $code, string $domain){
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

    public static function registerCustomerWebhookService($shop, $access_token){
        $topic_access = [
            'customers/create',
            'customers/update',
            'customers/delete',
            'app/uninstalled',
        ];
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/webhooks.json';
        foreach ($topic_access as $topic){
            $resShop = $client->request('post', $url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $access_token,
                ],
                'form_params' => [
                    'webhook' => [
                        'topic' => $topic,
                        'format' => 'json',
                        'address' => config('shopify.ngrok').'/api/shopify/webhook',
                    ],
                ]
            ]);
        }
    }

    public function getDataLogin($shop, $access_token){
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

    public function countDataCustomer($shop, $access_token){
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

    public function createDataCustomer($shop, $access_token){

    }

//    public function setParam(array $headers, $params);

    public function saveDataLogin($res, $access_token){
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

    public function saveDataCustomer($getCustomer){
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
