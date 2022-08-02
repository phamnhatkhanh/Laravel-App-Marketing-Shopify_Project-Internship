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
        $shop = $request->shop;
        $redirect_uri = config('shopify.ngrok') . '/api/authen';
        $url = 'https://'.$shop.'.myshopify.com/admin/oauth/authorize?client_id=' . $apiKey . '&scope=' . $scope . '&redirect_uri=' . $redirect_uri;

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

    //Đăng kí ProductWebhooks thêm, xóa, sửa
    public function registerProductWebhook($shop, $access_token)
    {
        RegisterCustomerWebhookService::registerProductWebhookService($shop, $access_token);
    }

    //Đưa vào Queue để lưu những khách hàng đã được tạo trên Shopify vào DB
    public static function createFromShopify($payload)
    {
        $data =  dispatch(new CreateCustomer($payload));

        return response([
            'data' => $data,
            'status' => 201
        ], 201);
    }

    //Đưa vào Queue để tự động lưu những khách hàng đã được sửa trên Shopify vào DB
    public static function updateFromShopify($payload)
    {
       $data =  dispatch(new UpdateCustomer($payload));

       return response([
        'data' => $data,
        'status' => 201
    ], 201);
    }

    //Đưa vào Queue để tự động xóa khách hàng đã xóa trên Shopify trong DB
    public static function deleteFromShopify($payload)
    {
        $data = dispatch(new DeleteCustomer($payload));

        return response([
            'data' => $data,
            'status' => 201
        ], 201);
    }

}
