<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Jobs\CreateCustomer;
use App\Jobs\DeleteCustomer;
use App\Jobs\UpdateCustomer;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    function webhook(Request $request)
    {
        $topic = $request->header('X-Shopify-Topic');
        $myshopify_domain = $request->header('X-Shopify-Shop-Domain');
        $payload = $request->all();

        switch ($topic) {
            case 'customers/update':
                //Update data Product
                $this->updateFromShopify($payload);
                break;

            case 'customers/create':
                //Create data Product
                $this->createFromShopify($payload, $myshopify_domain);
                break;

            case 'customers/delete':
                //Delete data Product
                $this->deleteFromShopify($payload);

            case 'app/uninstalled':
                //Unistall App
        }
    }

    //Register Webhook Add, Edit, Delete, Uninstall
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

    //Đưa vào Queue để lưu những khách hàng đã được tạo trên Shopify vào DB
    public function createFromShopify($payload,$myshopify_domain)
    {
        $data =  dispatch(new CreateCustomer($payload, $myshopify_domain));

        return response([
            'data' => $data,
            'status' => 201
        ], 201);
    }

    //Đưa vào Queue để tự động lưu những khách hàng đã được sửa trên Shopify vào DB
    public function updateFromShopify($payload)
    {
        $data =  dispatch(new UpdateCustomer($payload));

        return response([
            'data' => $data,
            'status' => 201
        ], 201);
    }

    //Đưa vào Queue để tự động xóa khách hàng đã xóa trên Shopify trong DB
    public function deleteFromShopify($payload)
    {
        $data = dispatch(new DeleteCustomer($payload));

        return response([
            'data' => $data,
            'status' => 201
        ], 201);
    }

}

