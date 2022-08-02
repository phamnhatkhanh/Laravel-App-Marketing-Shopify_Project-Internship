<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    function webhook(Request $request)
    {
        $topic = $request->header('X-Shopify-Topic');
        $payload = $request->all();

        switch ($topic) {
            case 'customers/update':
                //Update data Product
                ShopifyController::updateFromShopify($payload);
                break;

            case 'customers/create':
                //Create data Product
                ShopifyController::createFromShopify($payload);
                break;

            case 'customers/delete':
                //Delete data Product
                ShopifyController::deleteFromShopify($payload);
        }
    }

    //Đăng kí ProductWebhooks thêm, xóa, sửa
    public function registerProductWebhook($shop, $access_token)
    {
        RegisterCustomerWebhookService::registerProductWebhookService($shop, $access_token);
    }
 public function accessPermission(){
        //  GetProducts::dispatch();
        $access_permission =array(
            "app/uninstalled",
            "products/create",
            "products/update",
            "products/delete" ,
        );
        //   dd(env("DOMAIN_NGROK"));
        $store_id  = Session::get('store_id');
        $store_token = Session::get('store_token');
        $store = Store::where('id',$store_id)->first();
        $url = "https://". $store->myshopify_domain ."/admin/api/2022-07/webhooks.json";
        foreach ($access_permission as $access) {

            $topic_webhook = array(
                "webhook"=> array(
                    "topic"=> $access,
                    "address"=>env("DOMAIN_NGROK")."/api/webhook/".$access,
                    "format"=>"json",
                )
            );

            $getDataProduct =  Http::withHeaders([
                'X-Shopify-Access-Token' =>  $store->app_token,
            ])->withBody(json_encode($topic_webhook), 'application/json')->post($url);
        }

        // After install app: Sync all product have store merchant.
        return redirect("/admin/list-product");


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

