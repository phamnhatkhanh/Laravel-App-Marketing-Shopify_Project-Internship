<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Jobs\CreateCustomer;
use App\Jobs\DeleteCustomer;
use App\Jobs\UpdateCustomer;
use App\Models\Customer;
use App\Models\Shopify;
use App\Models\Store;
use App\Repositories\Eloquents\CustomerWebhookRepository;
use App\Repositories\Eloquents\WebhookRepository;
use App\Repositories\Webhooks\RegisterCustomerWebhookService;
use App\Repositories\Webhooks\WebhookService;
use Illuminate\Http\Request;

class ShopifyController extends Controller
{

    // Truyền ra ngoài view để nhập tên Shopify
    public function index(Request $request)
    {
        $name = $request->get('name');
        if(!empty($name)){
            return response([
                'data'=> $name,
                'status' => 201,
            ],201);
        }
        else{
            return response();
        }

    }

    // Lấy link Shopify
    public function login(Request $request)
    {
        $apiKey = config('shopify.shopify_api_key');
        $scope = 'read_customers,write_customers';
        // $shop = $request->shop;
        $shop = env('SHOPIFY_DOMAIN');
        $redirect_uri =  'http://localhost:8000/api/authen';
        // $redirect_uri = config('shopify.ngrok') . '/api/authen';
        $url = 'https://' . $shop . '/admin/oauth/authorize?client_id=' . $apiKey . '&scope=' . $scope . '&redirect_uri=' . $redirect_uri;
        // dd($url);
        return redirect($url);
    }

    //Get access_token and Login Shop
    public function authen(Request $request)
    {
        $code = $request->code;
        $shopName = $request->shop;

        //Lấy Access_token gọi về từ WebhookService
        $getAccess_token = WebhookService::getAccessToken($code, $shopName);
        $access_token = $getAccess_token->access_token;

        //Lấy thông tin đăng nhập
        $getDataLogin = WebhookService::getDataLogin($shopName, $access_token);

        //Lưu thông tin Shopify vào DB
        if (!Store::find($getDataLogin['shop']->id)) {
            WebhookRepository::saveDataLogin($getDataLogin, $access_token);
        }
        //Lưu thông tin khách hàng ở Shopify lấy về từ SaveDataWebhookService vào DB
        $createCustomer = WebhookService::createDataCustomer($shopName, $access_token);

        foreach ($createCustomer['customers'] as $item) {
            if (!Customer::find($item->id)) {
                CustomerWebhookRepository::saveDataCustomer($createCustomer);
            }
        }

        //Đăng kí CustomerWebhooks thêm, xóa, sửa
        $this->registerProductWebhook($shopName, $access_token);
        // return redirect()->route('login');
        return redirect('http://127.0.0.1:8000/api/dashboard');
    }
    public static function getAccessToken(string $code, string $domain)
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
    public static function getDataLogin($shop, $access_token){
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
    public static function createDataCustomer($shop, $access_token)
    {
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/customers.json';
        $resProduct = $client->request('get', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
            ]
        ]);

        return (array)json_decode($resProduct->getBody()->getContents());
    }
    //Lưu thông tin Shopify
    public static function saveDataLogin($res, $access_token)
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
            'domain' => $saveData->domain,
            'myshopify_domain' => $saveData->domain,
            'email' => $saveData->email,
            'phone' => $saveData->phone,
            'password' => $password['password'],
            'access_token' => $access_token,
            'plan_name' => $saveData->plan_name,
            'address' => $saveData->address1,
            'zip' => $saveData->zip,
            'city' => $saveData->city,
            'country_name' => $saveData->country_name,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];
        Store::create($dataPost);

        return $dataPost;
    }
    //Lưu khách hàng vào DB
    public static function saveDataCustomer($getCustomer)
    {
        $saveCustomers = $getCustomer['customers'];
        info($saveCustomers);
        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');

        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');
        // $store_id = Session::get('store_id');
        foreach ($saveCustomers as $customer) {
            $created_at = str_replace($findCreateAT, $replaceCreateAT, $customer->created_at);
            $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $customer->updated_at);

            Customer::create([
                'id' => $customer->id,
                'store_id'  => 65147142383,
                'email' => $customer->email,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'orders_count' => $customer->orders_count,
                'total_spent' => $customer->total_spent,
                'phone' => '574-805-7422',
                // 'phone' => $customer->phone,
                // 'created_at' => $created_at,
                // 'updated_at' => $updated_at,
            ]);
        }
    }

}
