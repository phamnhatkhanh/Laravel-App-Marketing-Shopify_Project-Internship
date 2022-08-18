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

    public function createFromShopify($payload, $myshopify_domain){
       dispatch(new CreateCustomer($payload, $myshopify_domain));
    }

    public function updateFromShopify($payload){
        info("WebhookRepository: update customer from shopify");
        dispatch(new UpdateCustomer($payload));
    }

    public function deleteFromShopify($payload){
        dispatch(new DeleteCustomer($payload));
    }
}
