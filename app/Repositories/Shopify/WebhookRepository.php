<?php

namespace App\Repositories\Shopify;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

use Session;

use App\Jobs\CreateCustomer;
use App\Jobs\DeleteCustomer;
use App\Jobs\UpdateCustomer;


use App\Models\Store;

class WebhookRepository
{

    protected $store;
     public function __construct(){
        
        $this->store = new Store();

    }
    function webhook(Request $request){
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

//            case 'app/uninstalled':
//                //Unistall App
        }
    }

//    public function registerCustomerWebhookService($shop, $access_token){
//        $topic_access = [
//            'customers/create',
//            'customers/update',
//            'customers/delete',
//            'app/uninstalled',
//        ];
//        $client = new Client();
//        $url = 'https://' . $shop . '/admin/api/2022-07/webhooks.json';
//        foreach ($topic_access as $topic){
//            $resShop = $client->request('post', $url, [
//                'headers' => [
//                    'X-Shopify-Access-Token' => $access_token,
//                ],
//                'form_params' => [
//                    'webhook' => [
//                        'topic' => $topic,
//                        'format' => 'json',
//                        'address' => config('shopify.ngrok').'/api/shopify/webhook',
//                    ],
//                ]
//            ]);
//        }
//
//    }

    public function createFromShopify($payload, $myshopify_domain){
        $data =  dispatch(new CreateCustomer($payload, $myshopify_domain));
    }

    public function updateFromShopify($payload){
        $data =  dispatch(new UpdateCustomer($payload));
    }

    public function deleteFromShopify($payload){
        $data = dispatch(new DeleteCustomer($payload));
    }
}
